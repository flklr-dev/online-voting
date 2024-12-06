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

    public function getPositionPriority()
    {
        $positionName = strtolower($this->position_name);
        
        // Priority mapping
        $priorities = [
            'president' => 1,
            'governor' => 1,
            'mayor' => 1,
            'vice president' => 2,
            'vice governor' => 2,
            'vice mayor' => 2,
            'secretary' => 3,
            'treasurer' => 4,
            'auditor' => 5,
            'pio' => 6,
            'business manager' => 7
        ];

        // Return priority if found, otherwise return 99 (lowest priority)
        foreach ($priorities as $key => $priority) {
            if (str_contains($positionName, $key)) {
                return $priority;
            }
        }
        
        return 99;
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'position_id', 'position_id');
    }

}
