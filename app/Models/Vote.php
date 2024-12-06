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
    protected $keyType = 'string';

    protected $fillable = [
        'vote_id',
        'student_id',
        'election_id',
        'position_id',
        'candidate_id',
        'vote_date',
    ];

    protected $casts = [
        'vote_date' => 'datetime',
    ];

    // Disable timestamps if your table doesn't have them
    public $timestamps = false;

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function election()
    {
        return $this->belongsTo(Election::class, 'election_id', 'election_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'candidate_id');
    }
}
