<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Conversation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function conversationable(): MorphTo
    {
        return $this->morphTo();
    }
}
