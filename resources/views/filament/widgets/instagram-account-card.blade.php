<x-filament-widgets::widget>
    <x-filament::section heading="Instagram" icon="heroicon-o-camera">
        @if ($url)
            <x-slot name="afterHeader">
                <x-filament::link :href="$url" icon="heroicon-o-arrow-right" icon-position="after">
                    Ouvrir
                </x-filament::link>
            </x-slot>
        @endif

        @if ($account === null)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Aucun compte connecté.
            </p>
        @else
            <div class="fi-home-instagram">
                <div class="fi-home-instagram__avatar">
                    @if (filled($account->profile_picture_url))
                        <img src="{{ $account->profile_picture_url }}" alt="{{ $account->username }}" />
                    @else
                        <div style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background: rgb(255 255 255 / 0.05);">
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
        @endif
    </x-filament::section>

    <style>
        .fi-home-instagram {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 1rem;
            width: 100%;
        }

        .fi-home-instagram__avatar {
            width: 3.5rem;
            height: 3.5rem;
            flex: 0 0 3.5rem;
            overflow: hidden;
            border-radius: 9999px;
            box-shadow: inset 0 0 0 2px rgb(255 255 255 / 0.1);
        }

        .fi-home-instagram__avatar img {
            display: block;
            width: 3.5rem;
            height: 3.5rem;
            max-width: 3.5rem;
            object-fit: cover;
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
            gap: 0.65rem;
            margin-top: 1.25rem;
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
