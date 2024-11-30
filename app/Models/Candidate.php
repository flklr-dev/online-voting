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
        'student_id',
        'student_name',
        'campaign_statement',
        'picture',
        'partylist_id',
        'election_id',
        'position_id',
        'dateRegistered',
    ];

    /**
     * Relationship with Election model.
     */
    public function election()
    {
        return $this->belongsTo(Election::class, 'election_id', 'election_id');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
    
    
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'position_id');
    }
    
    public function partylist()
    {
        return $this->belongsTo(Partylist::class, 'partylist_id', 'partylist_id');
    }
    
}
