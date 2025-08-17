<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $fillable = ['kind','name','avatar_url','owner_user_id','system_code'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function scenes()
    {
        return $this->belongsToMany(Scene::class, 'scene_actors')
            ->withPivot(['visibility','can_post','note']);
    }
}
