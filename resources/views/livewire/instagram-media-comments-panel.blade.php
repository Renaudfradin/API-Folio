<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600 dark:text-gray-300">
            @if ($this->media->comments_count === 0)
                Aucun commentaire sur ce post.
            @else
                <span class="font-medium text-gray-950 dark:text-white">{{ $this->repliedCount }}</span>
                répondu(s) /
                <span class="font-medium text-gray-950 dark:text-white">{{ $this->media->comments_count }}</span>
                commentaire(s) au total
            @endif
        </p>

        <x-filament::button
            wire:click="refreshComments"
            wire:loading.attr="disabled"
            wire:target="refreshComments"
            size="sm"
            color="gray"
            icon="heroicon-m-arrow-path"
        >
            Rafraîchir
        </x-filament::button>
    </div>

    @if ($this->media->comments_count > 0 && count($this->comments) === 0)
        <div class="rounded-lg border border-warning-500/30 bg-warning-500/10 px-4 py-3 text-sm text-warning-700 dark:text-warning-400">
            <p class="mb-2">
                {{ $this->media->comments_count }} commentaire(s) sur Instagram, mais l’API de lecture renvoie une liste vide
                (bug connu avec Instagram Login). <strong>Le webhook ne récupère pas les anciens commentaires</strong> :
                il enregistre seulement les <strong>nouveaux</strong> après configuration.
            </p>
            <p class="mb-2">
                Pour tester : validez l’URL <code class="text-xs">/instagram/webhook</code> dans Meta, cochez le champ
                <code class="text-xs">comments</code>, reconnectez le compte Instagram, resynchronisez les posts, puis postez un
                <strong>nouveau</strong> commentaire sur ce post. Surveillez
                <code class="text-xs">storage/logs/laravel.log</code> (ligne « Instagram webhook comment event »).
            </p>
            <p>
                <strong>Rafraîchir</strong> appelle encore l’API GET (souvent vide) : utile après un webhook réussi, pas pour l’historique.
            </p>
        </div>
    @endif

    @if (count($this->comments) === 0)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Relancez une synchronisation pour récupérer les commentaires.
        </p>
    @else
        <ul class="space-y-4">
            @foreach ($this->comments as $comment)
                <li class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                    <div class="flex gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-500/15 text-sm font-semibold text-primary-600 dark:text-primary-400"
                            aria-hidden="true"
                        >
                            {{ strtoupper(substr($comment['username'] ?? '?', 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span class="font-semibold text-gray-950 dark:text-white">
                                    @{{ $comment['username'] ?? 'inconnu' }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $this->formatRelativeTime($comment['timestamp'] ?? null) }}
                                </span>
                                @if (($comment['like_count'] ?? 0) > 0)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        · {{ $comment['like_count'] }} j’aime
                                    </span>
                                @endif
                            </div>

                            <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">
                                {{ $comment['text'] }}
                            </p>

                            @if (filled($comment['id']))
                                <button
                                    type="button"
                                    wire:click="startReply(@js($comment['id']), @js($comment['username'] ?? ''))"
                                    class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400"
                                >
                                    Répondre
                                </button>
                            @endif

                            @if (! empty($comment['replies']))
                                <ul class="ms-2 space-y-3 border-s-2 border-primary-500/40 ps-4">
                                    @foreach ($comment['replies'] as $reply)
                                        <li class="flex gap-3">
                                            <div
                                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200"
                                                aria-hidden="true"
                                            >
                                                {{ strtoupper(substr($reply['username'] ?? '?', 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                                        @{{ $reply['username'] ?? 'inconnu' }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $this->formatRelativeTime($reply['timestamp'] ?? null) }}
                                                    </span>
                                                </div>
                                                <p class="whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-200">
                                                    {{ $reply['text'] }}
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($replyingToCommentId !== null)
        <div class="rounded-xl border-2 border-primary-500/50 bg-primary-500/5 p-4 dark:bg-primary-500/10">
            <p class="mb-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                Réponse à @{{ $replyingToUsername }}
            </p>

            <div class="flex gap-3">
                @if (filled($this->media->account?->profile_picture_url))
                    <img
                        src="{{ $this->media->account->profile_picture_url }}"
                        alt=""
                        class="h-9 w-9 shrink-0 rounded-full object-cover"
                    />
                @else
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold dark:bg-white/10"
                        aria-hidden="true"
                    >
                        {{ strtoupper(substr($this->media->account?->username ?? '?', 0, 1)) }}
                    </div>
                @endif

                <div class="min-w-0 flex-1 space-y-2">
                    <textarea
                        wire:model="replyMessage"
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                        placeholder="Votre réponse…"
                    ></textarea>

                    <div class="flex flex-wrap gap-2">
                        <x-filament::button
                            wire:click="sendReply"
                            wire:loading.attr="disabled"
                            wire:target="sendReply"
                            size="sm"
                            icon="heroicon-m-paper-airplane"
                        >
                            Envoyer
                        </x-filament::button>

                        <x-filament::button
                            wire:click="cancelReply"
                            size="sm"
                            color="gray"
                        >
                            Annuler
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
