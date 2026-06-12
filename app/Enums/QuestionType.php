<?php

namespace App\Enums;

enum QuestionType: string
{
    case Qcm = 'qcm';
    case ReponseCourte = 'reponse courte';
    case Developpement = 'developpement';
}
