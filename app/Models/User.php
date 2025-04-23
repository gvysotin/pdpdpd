<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'bio',        
        'email',
        'image',        
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            //'password' => 'hashed',
        ];
    }

    public function ideas()
    {
        return $this->hasMany(Idea::class)->latest();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    // Этот метод возвращает коллекцию пользователей, за которыми подписан текущий пользователь.
    public function followings()
    {
        return $this->belongsToMany(User::class,'follower_user', 'follower_id', 'user_id')->withTimestamps();
    }

    // Этот метод возвращает коллекцию пользователей, которые подписаны на текущего пользователя.
    public function followers()
    {
        return $this->belongsToMany(User::class,'follower_user', 'user_id', 'follower_id')->withTimestamps();
    }

    // Метод для определения того подписаны ли мы на текущего пользователя или нет.
    public function follows(User $user)
    {
        //
        //dd($this);
        //dd($user);
        return $this->followings()->where('user_id', $user->id)->exists();
    }

    public function likes()
    {
        // return $this->belongsToMany(Idea::class, 'idea_like', 'idea_id', 'user_id')->withTimestamps();
        return $this->belongsToMany(Idea::class, 'idea_like')->withTimestamps();
    }

    public function likesIdea(Idea $idea)
    {
        return $this->likes()->where('idea_id', $idea->id)->exists();
    }


    public function getImageURL() {
        if($this->image) {
            return url('storage/' . $this->image);
        }
        return "https://api.dicebear.com/6.x/fun-emoji/svg?seed={$this->name}";
    }

    public function hasReceivedWelcomeEmail(): bool
    {
        return $this->welcome_email_sent_at !== null;
    }

    public function markWelcomeEmailAsSent(): void
    {
        $this->update(['welcome_email_sent_at' => now()]);
    }

}
