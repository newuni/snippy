<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Paste extends Model
{
    protected $fillable = ['title', 'content', 'syntax', 'password', 'expires_at'];
    
    protected $hidden = ['password'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($paste) {
            $paste->slug = $paste->slug ?? self::generateSlug();
        });
    }

    public static function generateSlug(): string
    {
        do {
            $slug = Str::random(8);
        } while (self::where('slug', $slug)->exists());
        
        return $slug;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isProtected(): bool
    {
        return !empty($this->password);
    }

    public function checkPassword(string $password): bool
    {
        return $this->password && password_verify($password, $this->password);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
