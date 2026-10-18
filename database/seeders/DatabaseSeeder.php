<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['nome' => 'Teclado USB', 'descricao' => 'Teclado para computador', 'preco' => 89.90, 'quantidade' => 10],
            ['nome' => 'Mouse sem fio', 'descricao' => 'Mouse com conexão USB', 'preco' => 59.90, 'quantidade' => 15],
            ['nome' => 'Monitor 24 polegadas', 'descricao' => 'Monitor Full HD', 'preco' => 799.90, 'quantidade' => 5],
        ] as $dados) {
            Produto::firstOrCreate(['nome' => $dados['nome']], $dados);
        }
    }
}
