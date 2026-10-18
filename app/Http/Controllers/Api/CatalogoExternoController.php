<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CatalogoExternoController extends Controller
{
    public function index(Request $request)
    {
        $dados = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'skip' => ['sometimes', 'integer', 'min:0'],
        ]);

        $resposta = $this->consultar('/products', [
            'limit' => $dados['limit'] ?? 10,
            'skip' => $dados['skip'] ?? 0,
        ]);

        return response()->json([
            'fonte' => 'DummyJSON',
            'data' => $resposta->json('products'),
            'total' => $resposta->json('total'),
            'limit' => $resposta->json('limit'),
            'skip' => $resposta->json('skip'),
        ]);
    }

    public function show(int $id)
    {
        $resposta = $this->consultar('/products/'.$id);

        return response()->json([
            'fonte' => 'DummyJSON',
            'data' => $resposta->json(),
        ]);
    }

    private function consultar(string $caminho, array $parametros = []): Response
    {
        try {
            $resposta = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->get('https://dummyjson.com'.$caminho, $parametros);
        } catch (ConnectionException $exception) {
            abort(502, 'Não foi possível conectar à API externa. Tente novamente mais tarde.');
        }

        abort_if($resposta->status() === 404, 404, 'Produto não encontrado na API externa.');
        abort_unless($resposta->successful(), 502, 'A API externa está indisponível.');

        return $resposta;
    }
}
