<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $table = 'votes';
    protected $primaryKey = 'vote_id';
    public $incrementing = false;

    protected $fillable = [
        'vote_id',
        'student_id',
        'election_id',
        'position_id',
        'candidate_id',
        'vote_date',
    ];
}
