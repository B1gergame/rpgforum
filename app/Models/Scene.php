<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'scene_actors')
            ->withPivot(['visibility','can_post','note']);
    }
}
