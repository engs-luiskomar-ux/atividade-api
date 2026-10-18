<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoApiTest extends TestCase
{
    use RefreshDatabase;

    private function dados(): array
    {
        return ['nome' => 'Teclado', 'descricao' => 'USB', 'preco' => 89.90, 'quantidade' => 10];
    }

    public function test_crud_completo_com_resource(): void
    {
        $criado = $this->postJson('/api/produtos', $this->dados());
        $criado->assertCreated()->assertJsonPath('data.nome', 'Teclado')
            ->assertJsonPath('data.quantidade', 10)
            ->assertJsonStructure(['data' => ['id', 'nome', 'descricao', 'preco', 'quantidade', 'criado_em']]);
        $this->assertIsFloat($criado->json('data.preco'));
        $id = $criado->json('data.id');

        $this->getJson('/api/produtos')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonStructure(['links', 'meta']);
        $this->getJson('/api/produtos/'.$id)->assertOk()->assertJsonPath('data.id', $id);
        $this->putJson('/api/produtos/'.$id, [
            'nome' => 'Teclado atualizado', 'descricao' => null, 'preco' => 100, 'quantidade' => 3,
        ])->assertOk()->assertJsonPath('data.nome', 'Teclado atualizado')->assertJsonPath('data.descricao', null);
        $this->assertDatabaseHas('produtos', ['id' => $id, 'quantidade' => 3]);
        $this->deleteJson('/api/produtos/'.$id)->assertNoContent();
        $this->assertDatabaseMissing('produtos', ['id' => $id]);
        $this->getJson('/api/produtos/'.$id)->assertNotFound();
    }

    public function test_patch_preserva_campos_nao_enviados(): void
    {
        $produto = Produto::create($this->dados());
        $this->patchJson('/api/produtos/'.$produto->id, ['quantidade' => 0])
            ->assertOk()->assertJsonPath('data.quantidade', 0)->assertJsonPath('data.nome', 'Teclado');
        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'preco' => 89.90]);
    }

    public function test_campos_obrigatorios_e_limites(): void
    {
        $this->postJson('/api/produtos', [])->assertUnprocessable()->assertJsonValidationErrors(['nome', 'preco']);
        foreach ([
            ['preco' => -1], ['preco' => 1.999], ['preco' => 100000000],
            ['quantidade' => -1], ['quantidade' => 1.5], ['quantidade' => null],
            ['nome' => ''], ['nome' => str_repeat('a', 256)],
        ] as $invalido) {
            $this->postJson('/api/produtos', array_replace($this->dados(), $invalido))
                ->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalido));
        }
        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_put_exige_os_campos_principais(): void
    {
        $produto = Produto::create($this->dados());
        $this->putJson('/api/produtos/'.$produto->id, ['nome' => 'Novo'])
            ->assertUnprocessable()->assertJsonValidationErrors(['preco', 'quantidade']);
        $this->assertDatabaseHas('produtos', ['id' => $produto->id, 'nome' => 'Teclado']);
    }

    public function test_quantidade_padrao_e_preco_zero(): void
    {
        $this->postJson('/api/produtos', ['nome' => 'Amostra', 'preco' => 0])
            ->assertCreated()->assertJsonPath('data.quantidade', 0)->assertJsonPath('data.descricao', null);
    }

    public function test_paginacao_de_dez_produtos(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Produto::create(array_replace($this->dados(), ['nome' => 'Produto '.$i]));
        }
        $this->getJson('/api/produtos')->assertOk()->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)->assertJsonPath('data.0.nome', 'Produto 12');
        $this->getJson('/api/produtos?page=2')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_produto_inexistente_retorna_404_em_json(): void
    {
        $this->getJson('/api/produtos/99999')->assertNotFound();
        $this->putJson('/api/produtos/99999', $this->dados())->assertNotFound();
        $this->patchJson('/api/produtos/99999', ['quantidade' => 1])->assertNotFound();
        $this->deleteJson('/api/produtos/99999')->assertNotFound();
    }
}
