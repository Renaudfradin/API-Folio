<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\GitHubLanguagesChart;
use App\Filament\Widgets\GitHubStatsOverview;
use App\Services\GitHub\GitHubService;
use App\Traits\HasRoleBasedVisibility;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use RuntimeException;
use Throwable;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class GitHubDashboard extends Page
{
    use HasRoleBasedVisibility;

    protected static string|UnitEnum|null $navigationGroup = 'GitHub';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $slug = 'github-dashboard';

    protected static ?string $title = 'GitHub';

    protected ?string $heading = 'Tableau de bord GitHub';

    protected ?string $subheading = 'Statistiques du compte et édition du README de profil';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.github-dashboard';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?string $readmeSha = null;

    public function mount(GitHubService $github): void
    {
        if (! $github->isConfigured()) {
            Notification::make()
                ->warning()
                ->title('GitHub non configuré')
                ->body('Définis GITHUB_TOKEN et GITHUB_USERNAME dans ton fichier .env.')
                ->persistent()
                ->send();

            $this->form->fill([
                'content' => '',
                'commit_message' => 'Update profile README via Folio admin',
            ]);

            return;
        }

        $this->loadReadme($github);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('README.md du profil')
                    ->description('Contenu du dépôt spécial username/username affiché sur ton profil GitHub.')
                    ->schema([
                        Tabs::make('readme')
                            ->tabs([
                                Tab::make('Édition')
                                    ->icon(Heroicon::OutlinedPencilSquare)
                                    ->visible(fn (): bool => self::isCurrentUserAdmin())
                                    ->schema([
                                        CodeEditor::make('content')
                                            ->label('Éditeur Markdown')
                                            ->language(Language::Markdown)
                                            ->extraAttributes([
                                                'class' => 'github-readme-editor',
                                            ])
                                            ->live(debounce: 400)
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Tab::make('Aperçu')
                                    ->icon(Heroicon::OutlinedEye)
                                    ->schema([
                                        TextEntry::make('readme_preview')
                                            ->hiddenLabel()
                                            ->markdown()
                                            ->prose()
                                            ->state(fn (Get $get): string => (string) ($get('content') ?? data_get($this->data, 'content') ?? ''))
                                            ->placeholder('Rien à prévisualiser pour le moment.')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->persistTabInQueryString('readme-tab'),
                        TextInput::make('commit_message')
                            ->label('Message de commit')
                            ->default('Update profile README via Folio admin')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn (): bool => self::isCurrentUserAdmin())
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        $form = Form::make([EmbeddedSchema::make('form')])
            ->id('form');

        if (! self::isCurrentUserAdmin()) {
            return $form;
        }

        return $form
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            self::applyAdminVisibility(
                Action::make('save')
                    ->label('Sauvegarder')
                    ->submit('save')
                    ->keyBindings(['mod+s']),
            ),
            self::applyAdminVisibility(
                Action::make('reload')
                    ->label('Recharger')
                    ->color('gray')
                    ->action('reloadReadme'),
            ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openProfile')
                ->label('Voir le profil')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): ?string => filled(config('services.github.username'))
                    ? 'https://github.com/'.config('services.github.username')
                    : null)
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled(config('services.github.username'))),
            self::applyAdminVisibility(
                Action::make('refreshStats')
                    ->label('Rafraîchir les stats')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('gray')
                    ->action(function (GitHubService $github): void {
                        $github->forgetCache();

                        Notification::make()
                            ->success()
                            ->title('Cache vidé')
                            ->body('Recharge la page pour voir les stats à jour.')
                            ->send();

                        $this->redirect(static::getUrl());
                    }),
            ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GitHubStatsOverview::class,
            GitHubLanguagesChart::class,
        ];
    }

    public function save(GitHubService $github): void
    {
        abort_unless(self::isCurrentUserAdmin(), 403);

        try {
            $data = $this->form->getState();

            if (blank($this->readmeSha)) {
                throw new RuntimeException('Impossible de sauvegarder : SHA du README manquant. Recharge le fichier d’abord.');
            }

            $result = $github->updateProfileReadme(
                $data['content'],
                $this->readmeSha,
                $data['commit_message'] ?? null,
            );

            $this->readmeSha = $result['sha'];
            $this->form->fill([
                'content' => $result['content'],
                'commit_message' => $data['commit_message'] ?? 'Update profile README via Folio admin',
            ]);

            Notification::make()
                ->success()
                ->title('README mis à jour')
                ->body('Le README.md du profil a été commité sur GitHub.')
                ->send();
        } catch (RuntimeException $exception) {
            Notification::make()
                ->danger()
                ->title('Échec de la sauvegarde')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Erreur inattendue')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        }
    }

    public function reloadReadme(GitHubService $github): void
    {
        abort_unless(self::isCurrentUserAdmin(), 403);

        try {
            $readme = $github->getProfileReadme();
            $this->readmeSha = $readme['sha'];

            $this->form->fill([
                'content' => $readme['content'],
                'commit_message' => 'Update profile README via Folio admin',
            ]);

            Notification::make()
                ->success()
                ->title('README rechargé')
                ->send();
        } catch (RuntimeException $exception) {
            Notification::make()
                ->danger()
                ->title('Impossible de charger le README')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Erreur inattendue')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        }
    }

    protected function loadReadme(GitHubService $github): void
    {
        try {
            $readme = $github->getProfileReadme();
            $this->readmeSha = $readme['sha'];

            $this->form->fill([
                'content' => $readme['content'],
                'commit_message' => 'Update profile README via Folio admin',
            ]);
        } catch (RuntimeException $exception) {
            $this->readmeSha = null;

            $this->form->fill([
                'content' => '',
                'commit_message' => 'Update profile README via Folio admin',
            ]);

            Notification::make()
                ->danger()
                ->title('Impossible de charger le README')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Erreur inattendue')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        }
    }
}
