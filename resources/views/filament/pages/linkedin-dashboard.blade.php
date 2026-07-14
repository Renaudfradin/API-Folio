<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Vue d'ensemble LinkedIn
            </x-slot>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                La connexion LinkedIn sert ici à récupérer l'identité OIDC et à centraliser les données importées
                dans vos propres ressources Filament pour les expériences, projets, sélections et statistiques.
            </p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                État de la connexion
            </x-slot>

            @if ($connection)
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Nom LinkedIn</div>
                        <div class="mt-1 font-medium">{{ $connection->profile_name ?? '-' }}</div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Dernière synchro</div>
                        <div class="mt-1 font-medium">
                            {{ $connection->last_synced_at?->format('d/m/Y H:i') ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">URL LinkedIn</div>
                        <div class="mt-1 font-medium break-all">
                            {{ $connection->profile_url ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Statut</div>
                        <div class="mt-1 font-medium text-success-600">
                            Connecté
                        </div>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Aucune connexion LinkedIn n'est encore enregistrée pour votre compte.
                </p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
