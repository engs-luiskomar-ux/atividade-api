<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cadastro de produtos e consulta ao catálogo online.">
    <title>Produtos</title>
    <link rel="stylesheet" href="{{ asset('css/produtos.css') }}">
    <script src="{{ asset('js/produtos.js') }}" defer></script>
</head>
<body>
    <main class="container">
        <header class="cabecalho">
            <div>
                <span class="etiqueta">CONTROLE DE ESTOQUE</span>
                <h1>Produtos<span>.</span></h1>
                <p>Organize seus produtos e explore o catálogo online.</p>
            </div>
            <span class="selo">Cadastro e consulta</span>
        </header>

        <nav class="abas" aria-label="Catálogos">
            <button type="button" id="aba-local" class="aba ativa" aria-pressed="true">Meus produtos</button>
            <button type="button" id="aba-online" class="aba" aria-pressed="false">Catálogo online</button>
        </nav>

        <p id="mensagem" class="mensagem" role="status" aria-live="polite" hidden></p>

        <section id="local" class="layout" aria-label="Meus produtos">
            <aside class="painel cadastro">
                <h2 id="titulo-formulario">Novo produto</h2>
                <p class="ajuda">Preencha os dados para adicionar ao estoque.</p>
                <form id="formulario">
                    <label for="nome">Nome</label>
                    <input id="nome" name="nome" placeholder="Ex.: Teclado USB" maxlength="255" required>

                    <label for="descricao">Descrição <span class="opcional">(opcional)</span></label>
                    <textarea id="descricao" name="descricao" rows="3" placeholder="Detalhes do produto"></textarea>

                    <div class="campos-duplos">
                        <div>
                            <label for="preco">Preço (R$)</label>
                            <input id="preco" name="preco" type="number" min="0" max="99999999.99" step="0.01" placeholder="0,00" required>
                        </div>
                        <div>
                            <label for="quantidade">Quantidade</label>
                            <input id="quantidade" name="quantidade" type="number" min="0" max="4294967295" step="1" value="0" required>
                        </div>
                    </div>

                    <button id="salvar" class="botao principal" type="submit">Cadastrar produto</button>
                    <button id="cancelar" class="botao secundario" type="button" hidden>Cancelar edição</button>
                </form>
            </aside>

            <div class="painel lista">
                <div class="titulo-lista">
                    <div><h2>Seu estoque</h2><p id="resumo" class="ajuda" aria-live="polite">Carregando produtos...</p></div>
                    <button id="atualizar" type="button" class="botao secundario pequeno">Atualizar</button>
                </div>
                <div class="tabela-container">
                    <table>
                        <thead><tr><th>Produto</th><th>Preço</th><th>Estoque</th><th class="acoes-titulo">Ações</th></tr></thead>
                        <tbody id="produtos"></tbody>
                    </table>
                </div>
                <p id="estado-lista" class="estado" role="status">Carregando...</p>
                <div class="paginacao">
                    <button id="anterior" type="button" class="botao secundario pequeno" disabled>Anterior</button>
                    <span id="pagina">Página 1</span>
                    <button id="proxima" type="button" class="botao secundario pequeno" disabled>Próxima</button>
                </div>
            </div>
        </section>

        <section id="online" class="painel" aria-label="Catálogo online" hidden>
            <div class="titulo-lista">
                <div><h2>Explore o catálogo</h2><p class="ajuda">Produtos da DummyJSON. Esta consulta não adiciona itens ao seu estoque.</p></div>
                <button id="carregar-online" type="button" class="botao secundario pequeno">Atualizar</button>
            </div>
            <p id="estado-online" class="estado" role="status"></p>
            <div id="catalogo" class="catalogo"></div>
            <p class="nota">Nomes e preços são exibidos como fornecidos pela API externa, sem conversão de moeda.</p>
        </section>
        <footer>Feito para simplificar o cadastro de produtos.</footer>
    </main>
</body>
</html>
