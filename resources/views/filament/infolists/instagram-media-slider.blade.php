@php
    /** @var array<int, array{preview: ?string, media_url: ?string, media_type: ?string}> $items */
    $frameWidthPx = 300;
    $frameHeightPx = 375;
@endphp

<style>
    [data-instagram-media-carousel] [data-carousel-frame] {
        box-sizing: border-box;
        width: {{ $frameWidthPx }}px;
        max-width: 100%;
        height: {{ $frameHeightPx }}px;
        flex-shrink: 0;
        overflow: hidden;
        border-radius: 0.75rem;
        border: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
    }

    [data-instagram-media-carousel][data-fullscreen='true'] [data-carousel-frame] {
        width: min(90vw, 420px);
        height: min(80vh, 525px);
        max-width: 100%;
        border-color: rgb(255 255 255 / 0.15);
        background: rgb(17 24 39);
    }

    [data-instagram-media-carousel] [data-carousel-viewport] {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: rgb(243 244 246);
    }

    [data-instagram-media-carousel][data-fullscreen='true'] [data-carousel-viewport] {
        background: rgb(3 7 18);
    }

    [data-instagram-media-carousel] [data-carousel-slide] {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    [data-instagram-media-carousel] [data-carousel-slide] img,
    [data-instagram-media-carousel] [data-carousel-slide] video {
        display: block;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }

    [data-instagram-media-carousel] [data-carousel-row] {
        display: flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        max-width: 100%;
    }

    [data-instagram-media-carousel][data-fullscreen='true'] {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        flex-direction: column;
        background: #000;
    }

    [data-instagram-media-carousel][data-fullscreen='true'] [data-carousel-row] {
        flex: 1;
        justify-content: center;
        align-items: center;
        min-height: 0;
        padding: 16px;
        width: 100%;
        max-width: 100%;
    }

    [data-instagram-media-carousel] [data-carousel-arrow] {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 9999px;
        border: 1px solid rgb(229 231 235);
        background: rgb(255 255 255 / 0.95);
        color: rgb(55 65 81);
        cursor: pointer;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
    }

    [data-instagram-media-carousel] [data-carousel-arrow]:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
</style>

<div
    wire:ignore
    data-instagram-media-carousel
    x-data="{
        index: 0,
        items: @js($items),
        fullscreen: false,
        get total() {
            return this.items.length
        },
        get current() {
            return this.items[this.index] ?? null
        },
        get canPrev() {
            return this.index > 0
        },
        get canNext() {
            return this.index < this.total - 1
        },
        mediaTypeLabel(type) {
            if (type === 'VIDEO') return 'Vidéo'
            if (type === 'CAROUSEL_ALBUM') return 'Diapo'
            return 'Image'
        },
        prev() {
            if (this.canPrev) {
                this.index--
            }
        },
        next() {
            if (this.canNext) {
                this.index++
            }
        },
        toggleFullscreen() {
            this.fullscreen = !this.fullscreen
        },
    }"
    x-bind:data-fullscreen="fullscreen ? 'true' : 'false'"
    x-on:keydown.escape.window="fullscreen = false"
    @keydown.arrow-left.prevent="prev()"
    @keydown.arrow-right.prevent="next()"
    @keydown.f.prevent="toggleFullscreen()"
    tabindex="0"
    style="width: 100%; max-width: 100%; outline: none;"
>
    @if ($items === [])
        <p style="font-size: 0.875rem; color: rgb(107 114 128);">
            Aucun média disponible pour ce post.
        </p>
    @else
        <div data-carousel-row>
            <button
                type="button"
                data-carousel-arrow
                x-show="total > 1"
                x-on:click="prev()"
                x-bind:disabled="!canPrev"
                aria-label="Image précédente"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 22px; height: 22px;" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
                </svg>
            </button>

            <div data-carousel-frame>
                <div data-carousel-viewport x-on:dblclick="toggleFullscreen()">
                    <template x-for="(item, i) in items" :key="i">
                        <div data-carousel-slide x-show="index === i" x-cloak>
                            <template x-if="item.media_type === 'VIDEO' && item.media_url">
                                <video :src="item.media_url" controls></video>
                            </template>
                            <template x-if="item.media_type !== 'VIDEO' || !item.media_url">
                                <img
                                    :src="item.preview || item.media_url"
                                    alt=""
                                    loading="lazy"
                                />
                            </template>
                        </div>
                    </template>

                    <div
                        x-show="current"
                        style="pointer-events: none; position: absolute; top: 8px; left: 8px; z-index: 10; display: flex; align-items: center; gap: 6px; border-radius: 9999px; background: rgb(0 0 0 / 0.55); padding: 2px 10px; font-size: 11px; font-weight: 500; color: #fff;"
                    >
                        <span x-text="mediaTypeLabel(current?.media_type)"></span>
                        <span x-show="total > 1" style="opacity: 0.85;">
                            · <span x-text="index + 1"></span>/<span x-text="total"></span>
                        </span>
                    </div>

                    <button
                        type="button"
                        x-on:click="toggleFullscreen()"
                        :aria-label="fullscreen ? 'Quitter le plein écran' : 'Plein écran'"
                        style="position: absolute; top: 8px; right: 8px; z-index: 10; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 9999px; border: none; background: rgb(0 0 0 / 0.55); color: #fff; cursor: pointer;"
                    >
                        <x-filament::icon
                            icon="heroicon-m-arrows-pointing-out"
                            style="width: 16px; height: 16px;"
                            x-show="!fullscreen"
                        />
                        <x-filament::icon
                            icon="heroicon-m-x-mark"
                            style="width: 16px; height: 16px;"
                            x-show="fullscreen"
                        />
                    </button>
                </div>
            </div>

            <button
                type="button"
                data-carousel-arrow
                x-show="total > 1"
                x-on:click="next()"
                x-bind:disabled="!canNext"
                aria-label="Image suivante"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 22px; height: 22px;" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <p
            x-show="total > 1 && !fullscreen"
            style="margin: 8px 0 0; font-size: 0.75rem; color: rgb(107 114 128);"
        >
            Flèches pour parcourir le carrousel · F pour le plein écran
        </p>
    @endif
</div>
