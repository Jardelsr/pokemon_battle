# Pokemon Battle Simulator

Desafio técnico ateliware — simulador de batalha Pokémon usando a PokéAPI.

## Stack

| Tecnologia    | Versão |
|---------------|--------|
| PHP           | 8.3    |
| Laravel       | 11     |
| PostgreSQL    | 16     |
| Nginx         | latest |
| TailwindCSS   | CDN    |

## Pré-requisitos

- Docker e Docker Compose instalados

## Setup

```bash
# 1. Clone o repositório
git clone https://github.com/Jardelsr/pokemon_battle.git
cd pokemon_battle

# 2. Copie o arquivo de variáveis de ambiente
cp .env.example .env

# 3. Suba os containers
docker compose up -d --build

# 4. Instale as dependências PHP
docker compose exec app composer install

# 5. Gere a chave da aplicação
docker compose exec app php artisan key:generate

# 6. Execute as migrations
docker compose exec app php artisan migrate
```

A aplicação estará disponível em **http://localhost:8080**.

## Como usar

1. Acesse `http://localhost:8080`
2. Digite os nomes de dois Pokémon nos campos do formulário (ex: `pikachu`, `charizard`)
3. Clique em **Batalhar**
4. O resultado mostrará os sprites, HPs, tipos e o vencedor (ou empate)
5. O histórico das últimas 5 batalhas aparece na página inicial

## Regras de negócio

- O Pokémon com maior **HP base** vence
- HPs iguais resultam em **empate**
- Nomes são normalizados (lowercase, sem acentos) antes de consultar a PokéAPI
- Pokémon inválido exibe mensagem de erro clara
- Falha de rede exibe mensagem de serviço indisponível
- Cada batalha é persistida no banco de dados

## Arquitetura

```
app/
  Http/Controllers/
    BattleController.php     # thin controller — valida, delega, retorna view
  Services/
    PokeApiService.php       # toda comunicação HTTP com a PokéAPI
    BattleService.php        # lógica de batalha + persistência
  DTOs/
    PokemonDTO.php           # readonly class — name, hp, sprite, types
    BattleResultDTO.php      # readonly class — result, winner, ambos DTOs
  Exceptions/
    PokemonNotFoundException.php
    PokeApiException.php
    PokemonValidationException.php
  Models/
    Battle.php               # HasUuids, HasFactory, scopeRecent
database/
  migrations/
    ..._create_battles_table.php
  factories/
    BattleFactory.php
resources/views/
  layouts/app.blade.php
  battle/
    index.blade.php          # formulário + histórico
    result.blade.php         # cards, sprites, barras de stat, badges de tipo
```

**Princípios aplicados:**
- Controllers finos: apenas validação, delegação e resposta
- Toda lógica de negócio em Services
- DTOs `readonly` para transferência de dados tipada
- Exceptions customizadas para cada cenário de erro
- Singletons registrados no `AppServiceProvider`

## Testes

```bash
docker compose exec app php artisan test
```

Resultado esperado: **22 testes, 83 assertions**.

| Suite                         | Testes |
|-------------------------------|--------|
| Unit\PokeApiServiceTest       | 6      |
| Unit\BattleServiceTest        | 7      |
| Feature\BattleTest            | 7      |
| Feature\ExampleTest + Unit    | 2      |

Todos os testes usam `Http::fake()` — **nenhuma chamada real à PokéAPI**.

## Variáveis de ambiente relevantes

```env
POKEAPI_BASE_URL=https://pokeapi.co/api/v2
POKEAPI_TIMEOUT=5
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=pokemon_battle
DB_USERNAME=pokemon
DB_PASSWORD=secret
```
