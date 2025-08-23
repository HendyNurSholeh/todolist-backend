<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'user_id',
        'due_date'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope untuk filter todo berdasarkan user yang login
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope untuk filter todo yang completed
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    // Scope untuk filter todo yang pending
    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }
}
