<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Joueur extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    protected $appends = ['full_name', 'age', 'photo_url'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            if (str_starts_with($this->photo, 'http')) {
                return $this->photo;
            }
            return asset('storage/' . $this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->prenom . ' ' . $this->nom) . '&color=7F9CF5&background=EBF4FF';
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAgeAttribute(): int
    {
        return $this->date_naissance->age;
    }
}
