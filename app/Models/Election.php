<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'election_id';

    // Allow mass assignment for these attributes
    protected $fillable = [
        'election_name',
        'description',
        'election_type',
        'restriction',
        'start_date',
        'end_date',
        'election_status',
    ];

    // Cast start_date and end_date to DateTime objects
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'election_id', 'election_id');
    }
    

    // In Election.php
    public function positions()
    {
        return $this->hasManyThrough(Position::class, Candidate::class, 'election_id', 'position_id', 'election_id', 'position_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'election_id', 'election_id');
    }

}
