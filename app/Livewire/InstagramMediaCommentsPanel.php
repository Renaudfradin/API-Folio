<?php

namespace App\Livewire;

use App\Models\InstagramMedia;
use App\Services\Instagram\InstagramGraphService;
use App\Services\Instagram\InstagramSyncService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\RequestException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class InstagramMediaCommentsPanel extends Component
{
    public int $instagramMediaId;

    public ?string $replyingToCommentId = null;

    public ?string $replyingToUsername = null;

    public string $replyMessage = '';

    public function mount(int $instagramMediaId): void
    {
        $this->instagramMediaId = $instagramMediaId;
    }

    #[Computed]
    public function media(): InstagramMedia
    {
        return InstagramMedia::query()
            ->with('account')
            ->findOrFail($this->instagramMediaId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function comments(): array
    {
        return $this->media->comments ?? [];
    }

    #[Computed]
    public function repliedCount(): int
    {
        $accountUsername = $this->media->account?->username;

        if (! filled($accountUsername)) {
            return 0;
        }

        return collect($this->comments)
            ->sum(function (array $comment) use ($accountUsername): int {
                $replies = $comment['replies'] ?? [];

                return collect($replies)
                    ->filter(fn (array $reply): bool => ($reply['username'] ?? null) === $accountUsername)
                    ->count();
            });
    }

    public function refreshComments(InstagramSyncService $syncService): void
    {
        try {
            $syncService->syncCommentsForMedia($this->media);
            unset($this->media, $this->comments, $this->repliedCount);

            Notification::make()
                ->title('Commentaires mis à jour')
                ->success()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Échec de la synchronisation')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    public function startReply(string $commentId, string $username): void
    {
        $this->replyingToCommentId = $commentId;
        $this->replyingToUsername = $username;
        $this->replyMessage = '@'.$username.' ';
    }

    public function cancelReply(): void
    {
        $this->replyingToCommentId = null;
        $this->replyingToUsername = null;
        $this->replyMessage = '';
    }

    public function sendReply(InstagramGraphService $graph, InstagramSyncService $syncService): void
    {
        $message = trim($this->replyMessage);

        if ($this->replyingToCommentId === null || $message === '') {
            Notification::make()
                ->title('Message vide')
                ->warning()
                ->send();

            return;
        }

        $account = $this->media->account;

        if ($account === null || ! filled($account->access_token)) {
            Notification::make()
                ->title('Compte Instagram indisponible')
                ->danger()
                ->send();

            return;
        }

        try {
            $graph->postCommentReply($this->replyingToCommentId, $message, $account->access_token);
            $syncService->syncCommentsForMedia($this->media);
            unset($this->media, $this->comments, $this->repliedCount);
            $this->cancelReply();

            Notification::make()
                ->title('Réponse publiée')
                ->success()
                ->send();
        } catch (RequestException $exception) {
            $body = $exception->response?->json('error.message') ?? $exception->getMessage();

            Notification::make()
                ->title('Impossible d’envoyer la réponse')
                ->body($body)
                ->danger()
                ->send();
        } catch (Throwable $throwable) {
            Notification::make()
                ->title('Impossible d’envoyer la réponse')
                ->body($throwable->getMessage())
                ->danger()
                ->send();
        }
    }

    public function formatRelativeTime(?string $timestamp): string
    {
        if (! filled($timestamp)) {
            return '-';
        }

        return Carbon::parse($timestamp)->locale('fr')->diffForHumans();
    }

    public function render(): View
    {
        return view('livewire.instagram-media-comments-panel');
    }
}
