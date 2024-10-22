<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $table = 'candidates';
    protected $primaryKey = 'candidate_id';
    public $timestamps = false;

    protected $fillable = [
        'student_ID',
        'student_name',
        'campaign_statement',
        'picture',
        'partylist',
        'election_ID',
        'position_ID',
        'dateRegistered',
    ];

    /**
     * Relationship with Election model.
     */
    public function election()
    {
        return $this->belongsTo(Election::class, 'election_id', 'election_id');
    }
    
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }
    
    /**
     * Relationship with Student model.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
