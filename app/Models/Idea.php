<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Idea extends Model
{
    use HasFactory;

    // protected $guarded = [
    //     'id',
    //     'created_at',
    //     'updated_at',
    // ];

    // protected $with = ['user:id,name', 'comments.user']; // способ включить быструю загрузку для таких отношений, я использую метод with, чтобы не использовать метод without

    protected $with = ['user:id,name,image', 'comments.user:id,name,image']; // способ включить быструю загрузку для таких отношений, я использую метод with, чтобы не использовать метод without

    protected $withCount = ['likes'];

    protected $fillable = [
        "user_id",
        'content',
        // 'likes',
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'idea_like', '', '')->withTimestamps();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, $search = '') : Builder
    {
        // $query->where("content", "like", "%" . request()->get("search", "") . "%");
        return $query->where("content", "like", "%" . $search . "%");
    }
}
