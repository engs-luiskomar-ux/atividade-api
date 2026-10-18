<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $fillable = ['nome', 'descricao', 'preco', 'quantidade'];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'quantidade' => 'integer',
        ];
    }
}
