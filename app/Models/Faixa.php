<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faixa extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = ['nome', 'duracao', 'spotify_link'];

    public function albuns(){
        return $this->belongsToMany('App\Models\Album');
    }
    
}
