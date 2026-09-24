<p align="center">
  <img src="https://raw.githubusercontent.com/Twila-Digital/twila-parcelemais-php-sdk/production/assets/logo-light.svg" alt="Parcele+" width="180" style="max-width: 100%;">
</p>

<p align="center">
  <a href="LICENSE"><img alt="License" src="https://img.shields.io/github/license/Twila-Digital/twila-parcelemais-php-sdk"></a>
  <a href="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/actions/workflows/ci.yml"><img alt="CI" src="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/actions/workflows/ci.yml/badge.svg"></a>
  <a href="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/actions/workflows/quality.yml"><img alt="Quality" src="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/actions/workflows/quality.yml/badge.svg"></a>
  <a href="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/security/code-scanning"><img alt="Security" src="https://github.com/Twila-Digital/twila-parcelemais-php-sdk/actions/workflows/security.yml/badge.svg"></a>
  <a href="https://codecov.io/gh/Twila-Digital/twila-parcelemais-php-sdk"><img alt="Coverage" src="https://codecov.io/gh/Twila-Digital/twila-parcelemais-php-sdk/branch/production/graph/badge.svg"></a>
  <img alt="PHP" src="https://img.shields.io/badge/PHP-7.4%2B-539E43">
</p>

# twila/parcelemais

SDK oficial em PHP para a API do [Parcele+](https://www.cartaosimples.com.br) — crédito direto ao consumidor (CDC) e parcelamento no momento da compra.

> Uso restrito a server-side. O `clientSecret` nunca deve ser embarcado em um app mobile, SPA ou qualquer código que rode no navegador/dispositivo do usuário final.

## Compatibilidade

| Runtime | Versões aceitas |
| --- | --- |
| PHP | 7.4 ou superior (CI cobre 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 e 8.5) |

Cliente síncrono, baseado em [Guzzle](https://docs.guzzlephp.org/). Não usa `enum` nativo (PHP 8.1+) nem `readonly`/constructor promotion — o SDK precisa compilar e rodar de verdade em PHP 7.4, então os "enums" (`OrderStatus`, `WebHookType`, etc.) são classes com constantes inteiras.

## Instalação

```bash
composer require twila/parcelemais
```

## Configuração

```php
use Twila\ParceleMais\ParceleMaisClient;
use Twila\ParceleMais\Config\ClientOptions;
use Twila\ParceleMais\Config\Environment;

$client = new ParceleMaisClient(new ClientOptions(
    '<seu-client-id>',
    '<seu-client-secret>',
    Environment::STAGING
));
```

`ParceleMaisClient` deve ser reaproveitado (não crie uma instância por requisição) — ele mantém o cache do token de acesso e o estado do circuit breaker durante o ciclo de vida do processo/worker.

### Simulando parcelas

```php
use Twila\ParceleMais\Simulations\SimulateInstallmentsRequest;

$parcelas = $client->simulations->simulateInstallments(new SimulateInstallmentsRequest(1500.0));

foreach ($parcelas as $parcela) {
    echo "{$parcela->term}x de {$parcela->installmentAmount} (total {$parcela->totalAmount})" . PHP_EOL;
}
```

### Criando um pedido

```php
use Twila\ParceleMais\Orders\Address;
use Twila\ParceleMais\Orders\CreateOrderRequest;

$pedidoId = $client->orders->create(new CreateOrderRequest(
    '12345678901',
    '+5511999998888',
    '12345678000195',
    1500.0,
    'Maria Souza',
    'maria.souza@exemplo.com.br',
    '1990-05-20T00:00:00-03:00',
    new Address('Av. Paulista', '1578', 'Bela Vista', 'São Paulo', 'SP', '01311000')
));
```

`create` retorna só o `id` do pedido — a API não devolve o pedido completo na criação; use `$client->orders->get($pedidoId)` se precisar dos dados completos logo em seguida.

## Clientes por recurso

| Cliente | Métodos |
| --- | --- |
| `$client->orders` | `create`, `get`, `list`, `startCdcSale`, `importInvoice` |
| `$client->simulations` | `simulateInstallments`, `simulateValues` |
| `$client->customers` | `get`, `list` |
| `$client->establishments` | `create`, `get`, `list`, `update`, `updateBankAccount`, `activate`, `deactivate` |
| `$client->webhooks` | `create`, `list`, `listAudit`, `update`, `delete` |

## Paginação

`orders->list(...)`, `customers->list(...)` e `webhooks->listAudit(...)` retornam um `PagedResult` — sem auto-paginação, você controla explicitamente o avanço de página:

```php
use Twila\ParceleMais\Orders\ListOrdersRequest;

$page = $client->orders->list(new ListOrdersRequest(null, null, null, null, null, null, null, 1, 20));

foreach ($page->items as $order) {
    echo $order->id . PHP_EOL;
}

if ($page->hasNext) {
    $next = $client->orders->list(new ListOrdersRequest(null, null, null, null, null, null, null, 2, 20));
}
```

## Tratamento de erros

| Erro | Quando |
| --- | --- |
| `ParceleMaisConfigurationException` | Configuração do `ParceleMaisClient` inválida (ex: `clientId`/`clientSecret` ausentes) |
| `ParceleMaisAuthenticationException` | Falha ao gerar/renovar o token de acesso |
| `ParceleMaisValidationException` | `400` — erro de validação, com `getFieldErrors()` por campo |
| `ParceleMaisRateLimitException` | `429` |
| `ParceleMaisTimeoutException` | Timeout de rede, timeout total, ou circuit breaker aberto |
| `ParceleMaisApiException` | Qualquer outro erro de API (`404`, `409`, `5xx`) |
| `ParceleMaisWebhookSignatureException` | Assinatura de webhook inválida ou expirada |

```php
use Twila\ParceleMais\Errors\ParceleMaisApiException;

try {
    $client->orders->get($orderId);
} catch (ParceleMaisApiException $e) {
    echo "{$e->getStatusCode()} {$e->getErrorCode()}: {$e->getMessage()}" . PHP_EOL;
}
```

## Validando webhooks

```php
use Twila\ParceleMais\Webhooks\WebhookEvent;

$evento = WebhookEvent::parse($rawBody, $signatureHeader, $signingSecret);
```

Verifica a assinatura HMAC-SHA256 do cabeçalho e a janela de replay (5 minutos) antes de expor o evento. Lança `ParceleMaisWebhookSignatureException` se a assinatura for inválida ou o evento estiver fora da janela.

## Samples

- `samples/sample-cli` — script standalone, sem framework
- `samples/sample-slim` — `ParceleMaisClient` como singleton no container DI do [Slim Framework](https://www.slimframework.com/)

## Qualidade, segurança e cobertura

- **Build/Test** (`ci.yml`) — `phpstan analyse` (nível 8) + `phpcs` (PSR-12) + suíte de testes (`phpunit`) em PHP 7.4–8.5.
- **Quality** (`quality.yml`) — análise estática via Codacy CLI (PHPMD), resultados publicados na aba **Security → Code scanning** do repositório.
- **Security** (`security.yml`) — [Psalm](https://psalm.dev/) com taint analysis (o CodeQL não suporta PHP) + `composer audit`, rodando a cada PR/push e semanalmente.
- **Coverage** — cobertura de testes coletada via Xdebug/PCOV e publicada no [Codecov](https://codecov.io/gh/Twila-Digital/twila-parcelemais-php-sdk).

## Documentação completa

[documentacao.parcelemais.com.br](https://documentacao.parcelemais.com.br) — referência de todos os endpoints, autenticação, webhooks e mais.

## Contribuindo

Veja [CONTRIBUTING.md](CONTRIBUTING.md).

## Código de conduta

Este projeto segue o [Código de Conduta](CODE_OF_CONDUCT.md).

## Licença

[MIT](LICENSE)
