# Catálogo Ju Make

Um catálogo de produtos online com área administrativa e integração com WhatsApp.

## Funcionalidades

### Área Administrativa
- Login protegido
- Cadastro de produtos com:
  - Nome
  - Valor
  - Quantidade em estoque
  - Descrição
  - Imagem
- Gerenciamento de banners
- Configuração da logomarca
- Configuração do número do WhatsApp

### Página Principal
- Exibição da logomarca
- Carrossel de banners
- Busca de produtos
- Listagem de produtos com:
  - Imagem
  - Nome
  - Preço
  - Estoque
- Botões de ação:
  - Comprar pelo WhatsApp
  - Adicionar ao Carrinho
- Carrinho de compras com:
  - Lista de produtos
  - Quantidades
  - Valores
  - Total
  - Finalização via WhatsApp

## Como Usar

1. Acesse a área administrativa em `admin.html`
   - Usuário padrão: `admin`
   - Senha padrão: `admin123`

2. Configure as informações da loja:
   - Faça upload da logomarca
   - Cadastre o número do WhatsApp

3. Cadastre seus produtos:
   - Preencha todos os campos obrigatórios
   - Faça upload das imagens

4. Adicione banners:
   - Faça upload das imagens para o carrossel

5. Acesse a página principal em `index.html` para visualizar o catálogo

## Tecnologias Utilizadas

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Font Awesome
- LocalStorage para persistência de dados

## Personalização

O design pode ser personalizado editando as variáveis CSS em `css/style.css`:

```css
:root {
    --primary-color: #ff69b4;
    --secondary-color: #ffb6c1;
    --accent-color: #ffd700;
    --text-color: #333;
    --background-color: #fff;
    --light-pink: #fff0f5;
}
```

## Segurança

Para ambiente de produção, recomenda-se:
1. Implementar autenticação mais segura
2. Usar um backend para armazenamento de dados
3. Implementar validação de uploads
4. Adicionar proteção contra XSS e CSRF

## Suporte

Para suporte ou dúvidas, entre em contato através do WhatsApp configurado no painel administrativo.
