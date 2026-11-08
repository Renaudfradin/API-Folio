<x-filament-widgets::widget class="fi-wi-instagram">
    <div class="fi-sc-section">
        <section class="fi-section fi-section-not-contained fi-section-has-header">
            <header class="fi-section-header">
                <div class="fi-section-header-content fi-section-header-content-has-heading">
                    <div class="fi-section-header-text-ctn">
                        <h3 class="fi-section-header-heading">Instagram</h3>
                        <p class="fi-section-header-description">Compte, abonnés et publications</p>
                    </div>

                    @if ($url)
                        <div class="fi-section-header-after-ctn">
                            <x-filament::link :href="$url" icon="heroicon-o-arrow-right" icon-position="after">
                                Ouvrir
                            </x-filament::link>
                        </div>
                    @endif
                </div>
            </header>

            <div class="fi-section-content-ctn">
                <div class="fi-wi-stats-overview-stat fi-home-instagram-card">
                    @if ($account === null)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Aucun compte connecté.
                        </p>
                    @else
                        <div class="fi-home-instagram-body">
                            <div class="fi-home-instagram">
                                <div class="fi-home-instagram__avatar">
                                    @if (filled($account->profile_picture_url))
                                        <img src="{{ $account->profile_picture_url }}" alt="" />
                                    @else
                                        <div class="fi-home-instagram__avatar-fallback">
                                            <x-filament::icon icon="heroicon-o-user-circle" class="h-8 w-8 text-gray-400" />
                                        </div>
                                    @endif
                                </div>

                                <div class="fi-home-instagram__meta">
                                    <span class="text-lg font-semibold text-gray-950 dark:text-white">
                                        {{ filled($account->username) ? '@'.$account->username : ($account->name ?: 'Compte Instagram') }}
                                    </span>
                                    @if (filled($account->name) && filled($account->username))
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $account->name }}
                                        </span>
                                    @endif
                                    <span @class([
                                        'text-xs',
                                        'text-danger-600 dark:text-danger-400' => $syncIsStale,
                                        'text-gray-500 dark:text-gray-400' => ! $syncIsStale,
                                    ])>
                                        {{ $syncLabel }}
                                    </span>
                                </div>
                            </div>

                            <div class="fi-home-instagram__metrics">
                                @foreach ($metrics as $metric)
                                    <div class="fi-home-instagram__metric">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $metric['label'] }}</span>
                                        <span class="text-lg font-semibold text-gray-950 dark:text-white">{{ $metric['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <style>
        .fi-wi-widget.fi-wi-instagram,
        .fi-wi-instagram > .fi-sc-section,
        .fi-wi-instagram > .fi-sc-section > .fi-section {
            height: 100%;
        }

        .fi-wi-instagram > .fi-sc-section > .fi-section {
            grid-template-rows: auto minmax(0, 1fr);
        }

        .fi-wi-instagram .fi-section-content-ctn,
        .fi-wi-instagram .fi-home-instagram-card,
        .fi-wi-instagram .fi-home-instagram-body {
            height: 100%;
            min-height: 0;
        }

        .fi-home-instagram-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.25rem;
        }

        .fi-home-instagram {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 1rem;
            width: 100%;
        }

        .fi-home-instagram__avatar,
        .fi-home-instagram__avatar-fallback {
            width: 3.5rem;
            height: 3.5rem;
            flex: 0 0 3.5rem;
            overflow: hidden;
            border-radius: 9999px;
        }

        .fi-home-instagram__avatar {
            box-shadow: inset 0 0 0 2px rgb(255 255 255 / 0.1);
        }

        .fi-home-instagram__avatar img {
            display: block;
            width: 3.5rem;
            height: 3.5rem;
            max-width: 3.5rem;
            object-fit: cover;
        }

        .fi-home-instagram__avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(255 255 255 / 0.05);
        }

        .fi-home-instagram__meta {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            align-items: flex-start;
            row-gap: 0.15rem;
            min-width: 0;
        }

        .fi-home-instagram__metrics {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.85rem;
            flex: 1 1 auto;
        }

        .fi-home-instagram__metric {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
        }

        .fi-home-instagram__metric span:last-child {
            font-variant-numeric: tabular-nums;
        }
    </style>
</x-filament-widgets::widget>
