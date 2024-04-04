<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = ['nome', 'ano', 'imagem', 'spotify_link'];
    protected $table = "albuns";

    public function faixas(){
        return $this->belongsToMany('App\Models\Faixa');
    }

}
