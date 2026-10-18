<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogoExternoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_lista_consume_api_com_paginacao(): void
    {
        Http::fake(['dummyjson.com/products*' => Http::response([
            'products' => [['id' => 1, 'title' => 'Produto externo']], 'total' => 100, 'limit' => 2, 'skip' => 5,
        ])]);
        $this->getJson('/api/catalogo-externo?limit=2&skip=5')->assertOk()
            ->assertJsonPath('fonte', 'DummyJSON')->assertJsonPath('data.0.title', 'Produto externo')
            ->assertJsonPath('total', 100)->assertJsonPath('skip', 5);
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://dummyjson.com/products?')
            && $request['limit'] === '2' && $request['skip'] === '5');
    }

    public function test_consulta_um_produto_externo(): void
    {
        Http::fake(['dummyjson.com/products/1' => Http::response(['id' => 1, 'title' => 'Externo'])]);
        $this->getJson('/api/catalogo-externo/1')->assertOk()->assertJsonPath('data.id', 1);
        Http::assertSentCount(1);
    }

    public function test_produto_externo_inexistente(): void
    {
        Http::fake(['dummyjson.com/*' => Http::response([], 404)]);
        $this->getJson('/api/catalogo-externo/99999')->assertNotFound();
    }

    public function test_falha_da_api_externa_retorna_502(): void
    {
        Http::fake(['dummyjson.com/*' => Http::response([], 500)]);
        $this->getJson('/api/catalogo-externo')->assertStatus(502)
            ->assertJsonPath('message', 'A API externa está indisponível.');
    }

    public function test_falha_de_conexao_retorna_502(): void
    {
        Http::fake(['dummyjson.com/*' => Http::failedConnection()]);
        $this->getJson('/api/catalogo-externo')->assertStatus(502)
            ->assertJsonPath('message', 'Não foi possível conectar à API externa. Tente novamente mais tarde.');
    }

    public function test_parametros_invalidos_nao_disparam_consulta_externa(): void
    {
        Http::fake();
        foreach (['limit=0', 'limit=31', 'limit=abc', 'skip=-1'] as $query) {
            $this->getJson('/api/catalogo-externo?'.$query)->assertUnprocessable();
        }
        $this->getJson('/api/catalogo-externo/abc')->assertNotFound();
        Http::assertNothingSent();
    }
}
