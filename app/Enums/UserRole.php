<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Enseignant = 'enseignant';
    case Etudiant = 'etudiant';
}
