<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        'content',
        'likes',
    ];

    // protected $with = ['user:id,name,image']; // способ включить быструю загрузку для таких отношений, я использую метод with, чтобы не использовать метод without

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function idea()
    {
        return $this->belongsTo(Idea::class);
    }
}
