<?php

namespace App\Enums;

enum Stack: string
{
    case Laravel = 'laravel';
    case Nuxt = 'nuxt';
    case React = 'react';
    case Vue = 'vue';
    case Symfony = 'symfony';
    case Wordpress = 'wordpress';
    case Node = 'node';
    case Python = 'python';
    case PHP = 'php';
}
