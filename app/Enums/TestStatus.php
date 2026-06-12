<?php

namespace App\Enums;

enum TestStatus: string
{
    case EnAttente = 'En attente';
    case EnCours = 'En cours';
    case Termine = 'Terminé';
}
