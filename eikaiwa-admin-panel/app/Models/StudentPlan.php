<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPlan extends Model
{
    protected $fillable = [
        'student_id',
        'plan_name',
        'start_date',
        'finish_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'finish_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
