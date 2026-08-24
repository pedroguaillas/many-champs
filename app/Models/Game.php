<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    // state [creado, planificado, jugando, finalizado]

    protected $fillable = [
        'club1_id', 'club2_id', 'progress_id',
        'captain1_id', 'captain2_id', 'date',
        'time', 'state'
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'time' => 'datetime:H:i:s',
    ];

    public function gameitems()
    {
        return $this->hasMany(GameItem::class);
    }
}
