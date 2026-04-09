# WC Rastreio Frenet

Plugin para WooCommerce que adiciona campo de código de rastreio nos pedidos e envia automaticamente um e-mail ao cliente com o link de acompanhamento via Frenet.

## Funcionalidades

- Metabox "Rastreio Frenet" na tela de edição do pedido no painel administrativo
- Botão de rastreio integrado às páginas de pedidos no front-end (Minha Conta e Shortcode de Rastreamento)
- Detecção automática da transportadora pelo padrão do código de rastreio:
  - **Correios** — formato `AA000000000AA` (ex: `AB123456789BR`) → `https://rastreio.frenet.com.br/COR/`
  - **Loggi** — 8 caracteres alfanuméricos (ex: `NSWC7NR6`) → `https://rastreio.frenet.com.br/LOG/`
- E-mail automático ao cliente quando o código é adicionado ou atualizado
- Formatação automática do código (maiúsculas, remoção de espaços)
- Compatível com HPOS (High-Performance Order Storage) do WooCommerce

## Requisitos

- WordPress 5.6 ou superior
- PHP 7.4 ou superior
- WooCommerce ativo

## Instalação

1. Copie a pasta `wc-rastreio-frenet` para o diretório `wp-content/plugins/` do seu WordPress
2. Ative o plugin em **Plugins → Plugins instalados**

## Como usar

1. Acesse um pedido em **WooCommerce → Pedidos**
2. No painel lateral, localize a metabox **Rastreio Frenet**
3. Insira o código de rastreio e salve o pedido
4. O cliente receberá automaticamente um e-mail com o código e o link de rastreamento

O e-mail só é disparado quando o código é inserido pela primeira vez ou alterado.

## Configuração do E-mail

O template do e-mail pode ser configurado em **WooCommerce → Configurações → E-mails → Código de Rastreio Frenet**, onde é possível personalizar assunto, título e formato (HTML, texto simples ou multipart).

## Autor

**Frederico de Castro**
[https://www.fredericodecastro.com.br](https://www.fredericodecastro.com.br)

## Licença

GPL-2.0-or-later
