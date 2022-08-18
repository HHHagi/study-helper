<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function books(){
        return $this->hasMany(Book::class);
    }
    public function tasks(){
        return $this->hasMany(Task::class);
    }
    public function likes(){
        return $this->hasMany(Like::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }
    public function public_categories()
    {
        return $this->hasOne(Public_category::class);
    }
    public function private_categories()
    {
        return $this->hasOne(Private_category::class);
    }
}
