const elemento = (id) => document.getElementById(id);
const formulario = elemento('formulario');
const dinheiro = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const numero = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
let produtos = [];
let pagina = 1;
let ultimaPagina = 1;
let editando = null;
let consultouOnline = false;

function mensagem(texto, erro = false) {
    const aviso = elemento('mensagem');
    aviso.textContent = texto;
    aviso.className = erro ? 'mensagem erro' : 'mensagem';
    aviso.hidden = !texto;
}

async function requisicao(url, metodo = 'GET', dados) {
    const opcoes = { method: metodo, headers: { Accept: 'application/json' } };
    if (dados !== undefined) {
        opcoes.headers['Content-Type'] = 'application/json';
        opcoes.body = JSON.stringify(dados);
    }
    let resposta;
    try {
        resposta = await fetch(url, opcoes);
    } catch {
        throw new Error('Não foi possível conectar. Confira se o servidor está ligado.');
    }
    if (resposta.status === 204) return null;
    const json = await resposta.json();
    if (!resposta.ok) {
        const erros = json.errors ? Object.values(json.errors).flat().join(' ') : json.message;
        throw new Error(erros || 'Não foi possível concluir a operação.');
    }
    return json;
}

function criar(tag, classe, texto) {
    const item = document.createElement(tag);
    if (classe) item.className = classe;
    if (texto !== undefined) item.textContent = texto;
    return item;
}

async function carregarProdutos(destino = pagina) {
    elemento('atualizar').disabled = true;
    elemento('anterior').disabled = true;
    elemento('proxima').disabled = true;
    elemento('estado-lista').textContent = 'Carregando produtos...';
    elemento('estado-lista').hidden = false;
    try {
        const json = await requisicao('/api/produtos?page=' + destino);
        if (!json.data.length && destino > json.meta.last_page) {
            return await carregarProdutos(json.meta.last_page);
        }
        produtos = json.data;
        pagina = json.meta.current_page;
        ultimaPagina = json.meta.last_page;
        elemento('produtos').replaceChildren();
        for (const produto of produtos) {
            const linha = criar('tr');
            const nome = criar('td');
            nome.append(criar('span', 'nome-produto', produto.nome));
            if (produto.descricao) nome.append(criar('span', 'descricao', produto.descricao));
            const quantidade = criar('td');
            quantidade.append(criar('span', produto.quantidade ? 'estoque' : 'estoque zerado', produto.quantidade + ' un.'));
            const acoes = criar('td');
            const botoes = criar('div', 'acoes');
            for (const acao of ['Editar', 'Excluir']) {
                const botao = criar('button', acao === 'Excluir' ? 'acao excluir' : 'acao', acao);
                botao.type = 'button';
                botao.setAttribute('aria-label', acao + ' ' + produto.nome);
                botao.addEventListener('click', () => acao === 'Editar' ? editar(produto) : excluir(produto, botao));
                botoes.append(botao);
            }
            acoes.append(botoes);
            linha.append(nome, criar('td', 'preco', dinheiro.format(produto.preco)), quantidade, acoes);
            elemento('produtos').append(linha);
        }
        elemento('resumo').textContent = json.meta.total + (json.meta.total === 1 ? ' produto cadastrado' : ' produtos cadastrados');
        elemento('pagina').textContent = 'Página ' + pagina + ' de ' + ultimaPagina;
        elemento('estado-lista').textContent = 'Nenhum produto cadastrado. Adicione o primeiro ao lado.';
        elemento('estado-lista').hidden = produtos.length > 0;
    } catch (erro) {
        elemento('estado-lista').textContent = 'Não foi possível carregar a lista. Clique em Atualizar para tentar novamente.';
        mensagem(erro.message, true);
    } finally {
        elemento('atualizar').disabled = false;
        elemento('anterior').disabled = pagina <= 1;
        elemento('proxima').disabled = pagina >= ultimaPagina;
    }
}

function limparFormulario() {
    editando = null;
    formulario.reset();
    elemento('titulo-formulario').textContent = 'Novo produto';
    elemento('salvar').textContent = 'Cadastrar produto';
    elemento('cancelar').hidden = true;
}

function editar(produto) {
    editando = produto.id;
    for (const campo of ['nome', 'descricao', 'preco', 'quantidade']) {
        elemento(campo).value = produto[campo] ?? '';
    }
    elemento('titulo-formulario').textContent = 'Editar produto';
    elemento('salvar').textContent = 'Salvar alterações';
    elemento('cancelar').hidden = false;
    mensagem('');
    formulario.scrollIntoView({ behavior: 'smooth', block: 'center' });
    elemento('nome').focus({ preventScroll: true });
}

formulario.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    const id = editando;
    const botao = elemento('salvar');
    botao.disabled = true;
    elemento('cancelar').disabled = true;
    botao.textContent = 'Salvando...';
    mensagem('');
    try {
        await requisicao(id ? '/api/produtos/' + id : '/api/produtos', id ? 'PUT' : 'POST', {
            nome: elemento('nome').value.trim(),
            descricao: elemento('descricao').value.trim() || null,
            preco: Number(elemento('preco').value),
            quantidade: Number(elemento('quantidade').value),
        });
        limparFormulario();
        mensagem(id ? 'Produto atualizado!' : 'Produto cadastrado!');
        await carregarProdutos(id ? pagina : 1);
    } catch (erro) {
        mensagem(erro.message, true);
    } finally {
        botao.disabled = false;
        elemento('cancelar').disabled = false;
        botao.textContent = editando ? 'Salvar alterações' : 'Cadastrar produto';
    }
});

async function excluir(produto, botao) {
    if (!confirm('Excluir o produto "' + produto.nome + '"?')) return;
    botao.disabled = true;
    try {
        await requisicao('/api/produtos/' + produto.id, 'DELETE');
        if (editando === produto.id) limparFormulario();
        mensagem('Produto excluído.');
        await carregarProdutos();
    } catch (erro) {
        mensagem(erro.message, true);
    } finally {
        botao.disabled = false;
    }
}

async function carregarOnline() {
    elemento('carregar-online').disabled = true;
    elemento('estado-online').hidden = false;
    elemento('estado-online').textContent = 'Consultando o catálogo online...';
    elemento('catalogo').replaceChildren();
    try {
        const json = await requisicao('/api/catalogo-externo?limit=12');
        for (const produto of json.data) {
            const card = criar('article', 'card');
            if (produto.thumbnail && /^https:\/\//.test(produto.thumbnail)) {
                const imagem = criar('img');
                imagem.src = produto.thumbnail;
                imagem.alt = produto.title;
                imagem.loading = 'lazy';
                card.append(imagem);
            }
            const conteudo = criar('div', 'card-conteudo');
            conteudo.append(criar('h3', '', produto.title), criar('p', '', 'Preço: ' + numero.format(produto.price)), criar('p', '', 'Estoque: ' + produto.stock));
            card.append(conteudo);
            elemento('catalogo').append(card);
        }
        consultouOnline = true;
        elemento('estado-online').textContent = 'Nenhum produto disponível.';
        elemento('estado-online').hidden = json.data.length > 0;
    } catch (erro) {
        elemento('estado-online').textContent = 'Consulta indisponível. Confira sua conexão e tente atualizar.';
        mensagem(erro.message, true);
    } finally {
        elemento('carregar-online').disabled = false;
    }
}

for (const aba of ['local', 'online']) {
    elemento('aba-' + aba).addEventListener('click', () => {
        for (const nome of ['local', 'online']) {
            elemento(nome).hidden = nome !== aba;
            elemento('aba-' + nome).classList.toggle('ativa', nome === aba);
            elemento('aba-' + nome).setAttribute('aria-pressed', String(nome === aba));
        }
        mensagem('');
        if (aba === 'online' && !consultouOnline) carregarOnline();
    });
}

elemento('cancelar').addEventListener('click', limparFormulario);
elemento('atualizar').addEventListener('click', () => carregarProdutos());
elemento('anterior').addEventListener('click', () => carregarProdutos(pagina - 1));
elemento('proxima').addEventListener('click', () => carregarProdutos(pagina + 1));
elemento('carregar-online').addEventListener('click', carregarOnline);
carregarProdutos();
