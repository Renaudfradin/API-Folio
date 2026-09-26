<x-filament-panels::page>
    @php
        $connection = $this->getConnection();
        $range = $this->getDateRange();
    @endphp

    <div class="space-y-6">
        @if ($connection === null)
            <x-filament::section>
                <x-slot name="heading">
                    Aucune connexion LinkedIn
                </x-slot>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Connecte ton compte LinkedIn pour afficher les statistiques et les publications, comme sur Buffer.
                </p>

                <div class="mt-4">
                    <x-filament::button
                        tag="a"
                        href="{{ route('linkedin.oauth.redirect') }}"
                        icon="heroicon-o-link"
                    >
                        Connecter LinkedIn
                    </x-filament::button>
                </div>
            </x-filament::section>
        @else
            <style>
                .fi-linkedin-profile-header {
                    display: flex !important;
                    flex-direction: row !important;
                    align-items: center !important;
                    gap: 1rem !important;
                    width: 100%;
                }

                .fi-linkedin-profile-header__avatar {
                    position: relative;
                    width: 3.5rem;
                    height: 3.5rem;
                    flex: 0 0 3.5rem;
                    overflow: hidden;
                    border-radius: 9999px;
                    box-shadow: inset 0 0 0 2px rgb(255 255 255 / 0.1);
                }

                .fi-linkedin-profile-header__avatar img {
                    display: block !important;
                    width: 3.5rem !important;
                    height: 3.5rem !important;
                    max-width: 3.5rem !important;
                    object-fit: cover;
                }

                .fi-linkedin-profile-header__badge {
                    position: absolute;
                    right: -0.15rem;
                    bottom: -0.15rem;
                    display: flex;
                    height: 1.25rem;
                    width: 1.25rem;
                    align-items: center;
                    justify-content: center;
                    border-radius: 9999px;
                    background: #0a66c2;
                    color: white;
                    font-size: 0.55rem;
                    font-weight: 700;
                    line-height: 1;
                }

                .fi-linkedin-profile-header__meta {
                    display: flex;
                    flex: 1 1 auto;
                    flex-wrap: wrap;
                    align-items: center;
                    column-gap: 0.75rem;
                    row-gap: 0.25rem;
                    min-width: 0;
                }
            </style>

            <x-filament::section>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="fi-linkedin-profile-header">
                        <div class="fi-linkedin-profile-header__avatar">
                            @if (filled($connection->profile_picture_url))
                                <img
                                    src="{{ $connection->profile_picture_url }}"
                                    alt="{{ $connection->profile_name }}"
                                />
                            @else
                                <div
                                    style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background: rgb(255 255 255 / 0.05);"
                                >
                                    <x-filament::icon icon="heroicon-o-user-circle" class="h-8 w-8 text-gray-400" />
                                </div>
                            @endif
                            <span class="fi-linkedin-profile-header__badge" aria-hidden="true">in</span>
                        </div>

                        <div class="fi-linkedin-profile-header__meta">
                            <span class="text-lg font-semibold text-gray-950 dark:text-white">
                                {{ $connection->profile_name ?? 'Profil LinkedIn' }}
                            </span>
                            @if (filled($connection->profile_url))
                                <a
                                    href="{{ $connection->profile_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    Voir le profil
                                </a>
                            @endif
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Dernière synchro :
                                {{ $connection->last_synced_at?->format('d/m/Y H:i') ?? '—' }}
                                @if ($connection->last_synced_status === 'failed' && filled($connection->last_synced_error))
                                    · <span class="text-danger-600 dark:text-danger-400">{{ \Illuminate\Support\Str::limit($connection->last_synced_error, 80) }}</span>
                                @endif
                            </span>
                            <x-filament::badge :color="filled($connection->access_token) ? 'success' : 'gray'">
                                {{ filled($connection->access_token) ? 'Connecté' : 'Inactif' }}
                            </x-filament::badge>
                        </div>
                    </div>
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
