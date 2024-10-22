<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'positions';

    protected $primaryKey = 'position_id';

    // The attributes that are mass assignable
    protected $fillable = [
        'position_name',
        'max_vote',
    ];

    // Attributes that should be cast to native types (if needed)
    protected $casts = [
        'max_vote' => 'integer', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'position_id', 'position_id');
    }

}
