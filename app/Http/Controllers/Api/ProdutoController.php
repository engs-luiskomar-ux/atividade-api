<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        return ProdutoResource::collection(Produto::orderByDesc('id')->paginate(10));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'quantidade' => ['sometimes', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $produto = Produto::create($dados)->refresh();

        return (new ProdutoResource($produto))->response()->setStatusCode(201);
    }

    public function show(Produto $produto)
    {
        return new ProdutoResource($produto);
    }

    public function update(Request $request, Produto $produto)
    {
        $presenca = $request->isMethod('put') ? 'required' : 'sometimes';
        $dados = $request->validate([
            'nome' => [$presenca, 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'preco' => [$presenca, 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'quantidade' => [$presenca, 'integer', 'min:0', 'max:4294967295'],
        ]);

        $produto->update($dados);

        return new ProdutoResource($produto);
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();

        return response()->noContent();
    }
}
