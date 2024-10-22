<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    // Specify the table if it doesn't follow Laravel's naming convention
    protected $table = 'students';

    // The primary key associated with the table.
    protected $primaryKey = 'student_id';

    // Indicates if the IDs are auto-incrementing.
    public $incrementing = false; // Since student_id is a VARCHAR, not an INT.

    // The attributes that are mass assignable.
    protected $fillable = [
        'student_id',
        'fullname',
        'school_email',
        'faculty',
        'program',
        'status'
    ];

    // The attributes that should be cast to native types.
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Define relationships if necessary
    public function votes()
    {
        return $this->hasMany(StudentVote::class, 'student_id');
    }
    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'student_id', 'student_id');
    }
}

