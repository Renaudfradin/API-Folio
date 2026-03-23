<?php

namespace App\Enums;

enum Platform: string
{
    case WelcomeToTheJungle = 'welcometothejungle';
    case Linkedin = 'linkedin';
    case Indeed = 'indeed';
    case Glassdoor = 'glassdoor';
    case Jobteaser = 'jobteaser';
    case JobupCh = 'jobup.ch';
    case Mail = 'mail';
    case PoleEmploi = 'pole-emploi';
    case FreeWork = 'freework';
    case Other = 'other';
}
