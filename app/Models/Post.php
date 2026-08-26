<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    // Los campos que se pueden llenar masivamente
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'image', // ← Asegúrate que esta línea esté aquí
    ];

    /**
     * Relación con el usuario (quien creó el post)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function comments() {
    return $this->hasMany(Comment::class);
}

public function likes() {
    return $this->hasMany(Like::class);
}
}