<x-filament-panels::page>
    @php
        $account = $this->getAccount();
        $range = $this->getDateRange();
        $accountOptions = $this->getAccountOptions();
    @endphp

    <div class="space-y-6">
        @if ($account === null)
            <x-filament::section>
                <x-slot name="heading">
                    Aucun compte Instagram
                </x-slot>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if (\App\Filament\Pages\InstagramDashboard::isCurrentUserDemo())
                        Aucun compte Instagram n’est configuré pour la démo. Les statistiques s’afficheront lorsqu’un compte sera disponible.
                    @else
                        Connecte un compte professionnel Instagram pour afficher les statistiques et les publications.
                    @endif
                </p>

                @unless (\App\Filament\Pages\InstagramDashboard::isCurrentUserDemo())
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('instagram.oauth.redirect') }}"
                            icon="heroicon-o-link"
                        >
                            Connecter Instagram
                        </x-filament::button>
                    </div>
                @endunless
            </x-filament::section>
        @else
            <style>
                .fi-instagram-profile-header {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: center !important;
                    gap: 1rem !important;
                    width: 100%;
                }

                .fi-instagram-profile-header__avatar {
                    width: 3.5rem;
                    height: 3.5rem;
                    flex: 0 0 3.5rem;
                    overflow: hidden;
                    border-radius: 9999px;
                    box-shadow: inset 0 0 0 2px rgb(255 255 255 / 0.1);
                }

                .fi-instagram-profile-header__avatar img {
                    display: block !important;
                    width: 3.5rem !important;
                    height: 3.5rem !important;
                    max-width: 3.5rem !important;
                    object-fit: cover;
                }

                .fi-instagram-profile-header__meta {
                    display: flex;
                    flex: 1 1 auto;
                    flex-wrap: wrap;
                    align-items: center;
                    column-gap: 0.75rem;
                    row-gap: 0.25rem;
                    min-width: 0;
                }

                @media (min-width: 768px) {
                    .fi-instagram-profile-header__meta {
                        flex-wrap: nowrap;
                    }
                }
            </style>

            <x-filament::section>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="fi-instagram-profile-header">
                        <div class="fi-instagram-profile-header__avatar">
                            @if (filled($account->profile_picture_url))
                                <img
                                    src="{{ $account->profile_picture_url }}"
                                    alt="{{ $account->username }}"
                                />
                            @else
                                <div
                                    style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background: rgb(255 255 255 / 0.05);"
                                >
                                    <x-filament::icon icon="heroicon-o-user-circle" class="h-8 w-8 text-gray-400" />
                                </div>
                            @endif
                        </div>

                        <div class="fi-instagram-profile-header__meta">
                            <span class="text-lg font-semibold text-gray-950 dark:text-white">
                                {{ '@'.$account->username }}
                            </span>
                            @if (filled($account->name))
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $account->name }}
                                </span>
                            @endif
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Dernière synchro :
                                {{ $account->last_synced_at?->format('d/m/Y H:i') ?? '—' }}
                                @if ($account->last_synced_status === 'failed' && filled($account->last_synced_error))
                                    · <span class="text-danger-600 dark:text-danger-400">{{ \Illuminate\Support\Str::limit($account->last_synced_error, 80) }}</span>
                                @endif
                            </span>
                            <x-filament::badge :color="$account->is_active ? 'success' : 'gray'">
                                {{ $account->is_active ? 'Actif' : 'Inactif' }}
                            </x-filament::badge>
                        </div>
                    </div>

                    @if ($accountOptions->count() > 1)
                        <div class="w-full max-w-xs">
                            <label class="mb-1 block text-sm font-medium text-gray-950 dark:text-white">
                                Compte
                            </label>
                            <select
                                wire:model.live="instagramAccountId"
                                class="fi-select-input block w-full rounded-lg border-none bg-white py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20"
                            >
                                @foreach ($accountOptions as $id => $username)
                                    <option value="{{ $id }}">{{ '@'.$username }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Période d'analyse · {{ $range['label'] }}
                </x-slot>

                <x-slot name="description">
                    {{ $range['start']->format('d/m/Y') }} – {{ $range['end']->format('d/m/Y') }}
                    · comparé à la période précédente de même durée (posts publiés).
                </x-slot>

                <div class="flex flex-wrap gap-2">
                    @foreach (['7d' => '7 jours', '30d' => '30 jours', 'mtd' => 'Mois en cours'] as $value => $label)
                        <x-filament::button
                            wire:click="$set('period', '{{ $value }}')"
                            :color="$this->period === $value ? 'primary' : 'gray'"
                            size="sm"
                        >
                            {{ $label }}
                        </x-filament::button>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
