<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent; 

class PromptLog extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'PromptLog';

    protected $fillable = [
        'prompt',
        'response',
        'timestamp',
        'responseEvaluation',
    ];

    protected $casts = [
        'responseEvaluation' => 'array',
    ];
}
