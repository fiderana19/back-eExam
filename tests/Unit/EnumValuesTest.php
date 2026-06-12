<?php

namespace Tests\Unit;

use App\Enums\QuestionType;
use App\Enums\TestStatus;
use App\Enums\UserRole;
use Tests\TestCase;

class EnumValuesTest extends TestCase
{
    public function test_user_role_enum_values()
    {
        $this->assertEquals('admin', UserRole::Admin->value);
        $this->assertEquals('enseignant', UserRole::Enseignant->value);
        $this->assertEquals('etudiant', UserRole::Etudiant->value);
        $this->assertTrue(UserRole::Admin === UserRole::from('admin'));
        $this->assertTrue(UserRole::Enseignant === UserRole::tryFrom('enseignant'));
    }

    public function test_test_status_enum_values()
    {
        $this->assertEquals('En attente', TestStatus::EnAttente->value);
        $this->assertEquals('En cours', TestStatus::EnCours->value);
        $this->assertEquals('Terminé', TestStatus::Termine->value);
        $this->assertTrue(TestStatus::EnAttente === TestStatus::from('En attente'));
    }

    public function test_question_type_enum_values()
    {
        $this->assertEquals('qcm', QuestionType::Qcm->value);
        $this->assertEquals('reponse courte', QuestionType::ReponseCourte->value);
        $this->assertEquals('developpement', QuestionType::Developpement->value);
        $this->assertTrue(QuestionType::Qcm === QuestionType::from('qcm'));
    }

    public function test_user_role_names_match_case_names()
    {
        $this->assertEquals('Admin', UserRole::Admin->name);
        $this->assertEquals('Enseignant', UserRole::Enseignant->name);
        $this->assertEquals('Etudiant', UserRole::Etudiant->name);
    }

    public function test_question_type_enum_is_string_backed()
    {
        $this->assertTrue((new \ReflectionEnum(QuestionType::class))->isBacked());
        $this->assertEquals('string', (new \ReflectionEnum(QuestionType::class))->getBackingType()->getName());
    }

    public function test_model_cast_values_match_enum_values()
    {
        $castMap = (new \App\Models\Utilisateur)->getCasts();
        $this->assertEquals(UserRole::class, $castMap['role']);

        $castMap = (new \App\Models\Test)->getCasts();
        $this->assertEquals(TestStatus::class, $castMap['status']);

        $castMap = (new \App\Models\Question)->getCasts();
        $this->assertEquals(QuestionType::class, $castMap['type_question']);
    }
}
