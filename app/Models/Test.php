<?php

namespace App\Models;

use App\Enums\TestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Test extends Model
{
    use HasFactory;

    protected $table = 'tests';
    protected $primaryKey = 'id_test';
    protected $fillable = [
        'id_utilisateur',
        'id_groupe',
        'titre',
        'description',
        'duree_minutes',
        'max_questions',
        'note_max',
        'date_declechement',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TestStatus::class,
        ];
    }

    // ─── Relations ────────────────────────────────────────────────

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'id_utilisateur', 'id_utilisateur');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'id_groupe', 'id_groupe');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'id_test');
    }

    public function tentatives()
    {
        return $this->hasMany(Tentative::class, 'id_test');
    }

    // ─── Vérifications ────────────────────────────────────────────

    public function isOwnedBy(Utilisateur $user): bool
    {
        return (int) $this->id_utilisateur === (int) $user->id_utilisateur;
    }

    public function isEditableBy(Utilisateur $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->isEnseignant() && $this->isOwnedBy($user);
    }

    // ─── Actions ──────────────────────────────────────────────────

    public function launch(): void
    {
        $this->status = TestStatus::EnCours;
        $this->date_declechement = Carbon::now();
        $this->save();
    }

    public function finish(): void
    {
        $this->status = TestStatus::Termine;
        $this->save();
    }

    // ─── Statistiques ─────────────────────────────────────────────

    public function getStats(): array
    {
        $tentatives = $this->tentatives;
        $total = $tentatives->count();
        $seuil = $this->note_max / 2;
        $supMoyenne = $tentatives->where('note_obtenue', '>=', $seuil)->count();
        $infMoyenne = $tentatives->where('note_obtenue', '<', $seuil)->count();

        return [
            'test' => $this,
            'total' => $total,
            'sup' => $supMoyenne,
            'sous' => $infMoyenne,
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePending(Builder $query): void
    {
        $query->where('status', TestStatus::EnCours->value);
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', TestStatus::Termine->value);
    }

    public function scopeCorrected(Builder $query): void
    {
        $query->completed()
              ->has('tentatives')
              ->whereDoesntHave('tentatives', function ($q) {
                  $q->where('est_noter', false);
              });
    }

    public function scopeWithUnnotedAttempts(Builder $query, ?int $userId = null): void
    {
        $query->completed()
              ->whereHas('tentatives', function ($q) {
                  $q->where('est_noter', false);
              })
              ->when($userId, function ($q) use ($userId) {
                  $q->where('id_utilisateur', $userId);
              });
    }
}
