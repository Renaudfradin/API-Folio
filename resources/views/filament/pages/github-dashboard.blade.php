<x-filament-panels::page>
    <div class="mb-6 space-y-2">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Consulte les stats de ton compte GitHub et modifie le README.md affiché sur ton profil.
        </p>
    </div>

    <style>
        .github-readme-editor .cm-scroller,
        .github-readme-editor .cm-gutters {
            min-height: 70vh !important;
        }
    </style>

    {{ $this->content }}
</x-filament-panels::page>
