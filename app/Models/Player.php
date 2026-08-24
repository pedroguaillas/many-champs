<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id', 'club_id', 'cedula',
        'first_name', 'last_name', 'photo',
        'date_of_birth', 't_shirt', 'phone'
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
    ];

    public function gameitems()
    {
        return $this->hasMany(GameItem::class);
    }
}
