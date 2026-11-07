<?php

namespace App\Services\Articles\Translation;

use App\Models\Article;

class TranslationPrompt
{
    /**
     * @param  list<string>  $languages
     */
    private static function joined(array $languages): string
    {
        return implode(', ', $languages);
    }

    /**
     * @param  array{title?: string, content?: array<mixed>}  $french
     */
    public static function text(array $french): string
    {
        $languages = [];

        foreach (Article::LOCALES as $locale) {
            if ($locale === Article::DEFAULT_LOCALE) {
                continue;
            }

            $languages[] = $locale.' ('.Article::LOCALE_LABELS[$locale].')';
        }

        $languageList = self::joined($languages);

        $article = json_encode([
            'title' => (string) ($french['title'] ?? ''),
            'content' => is_array($french['content'] ?? null) ? $french['content'] : [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
        Tu traduis un article de blog photo, du français vers plusieurs langues.
        Réponds uniquement avec un objet JSON, sans markdown et sans commentaire.
        Les clés de premier niveau sont exactement : en, de, es, ru, ky.
        Chaque valeur a la forme {"title":"...","content":...} et reprend les mêmes blocs que le français, dans le même ordre, avec les mêmes clés.
        Conserve les types, les niveaux de titre, les URL d’images et les URL de fichiers.
        Traduis le titre, les textes des titres, le HTML des paragraphes en gardant les balises, le texte alternatif des images et le libellé des fichiers.
        Langues cibles : {$languageList}.
        N’utilise aucun outil et ne modifie aucun fichier.

        Article français :
        {$article}
        PROMPT;
    }
}
