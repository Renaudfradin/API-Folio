@php
    /** @var array<int, array{preview: ?string, media_url: ?string, media_type: ?string}> $items */
@endphp

<div
    wire:ignore
    x-data="{
        index: 0,
        items: @js($items),
        get total() {
            return this.items.length
        },
        prev() {
            if (this.index > 0) {
                this.index--
            }
        },
        next() {
            if (this.index < this.total - 1) {
                this.index++
            }
        },
        goTo(i) {
            this.index = i
        },
    }"
    @keydown.arrow-left.prevent="prev()"
    @keydown.arrow-right.prevent="next()"
    tabindex="0"
    class="w-full rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
>
    @if ($items === [])
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Aucun média disponible pour ce post.
        </p>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
            <div class="relative flex min-h-[16rem] items-center justify-center sm:min-h-[24rem]">
                <template x-for="(item, i) in items" :key="i">
                    <div
                        x-show="index === i"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="absolute inset-0 flex items-center justify-center p-2"
                    >
                        <template x-if="item.media_type === 'VIDEO' && item.media_url">
                            <video
                                :src="item.media_url"
                                controls
                                class="max-h-[70vh] max-w-full rounded-lg object-contain"
                            ></video>
                        </template>
                        <template x-if="item.media_type !== 'VIDEO' || !item.media_url">
                            <img
                                :src="item.preview || item.media_url"
                                alt=""
                                class="max-h-[70vh] max-w-full rounded-lg object-contain"
                                loading="lazy"
                            />
                        </template>
                    </div>
                </template>

                <button
                    type="button"
                    x-show="total > 1"
                    x-on:click="prev()"
                    :disabled="index === 0"
                    class="absolute start-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-700 shadow-sm transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:bg-gray-900/90 dark:text-gray-200 dark:hover:bg-gray-900"
                    aria-label="Image précédente"
                >
                    <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5" />
                </button>

                <button
                    type="button"
                    x-show="total > 1"
                    x-on:click="next()"
                    :disabled="index === total - 1"
                    class="absolute end-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white/90 text-gray-700 shadow-sm transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:bg-gray-900/90 dark:text-gray-200 dark:hover:bg-gray-900"
                    aria-label="Image suivante"
                >
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5" />
                </button>
            </div>

            <div
                x-show="total > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-4 py-3 dark:border-white/10"
            >
                <p class="text-sm tabular-nums text-gray-600 dark:text-gray-300">
                    <span x-text="index + 1"></span>
                    /
                    <span x-text="total"></span>
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(item, i) in items" :key="'dot-' + i">
                        <button
                            type="button"
                            x-on:click="goTo(i)"
                            class="h-2 w-2 rounded-full transition"
                            :class="index === i ? 'bg-primary-600 dark:bg-primary-400' : 'bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500'"
                            :aria-label="'Aller à la diapositive ' + (i + 1)"
                        ></button>
                    </template>
                </div>
            </div>
        </div>
    @endif
</div>
