<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
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
        'status',
        'username', // add this to make sure username is set
        'password', // add this to make sure password is hashed and saved
    ];

    protected $hidden = ['password']; // Hide password field in responses

    // Mutator to automatically hash passwords
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

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

