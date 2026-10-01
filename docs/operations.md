# Provoz

## Konzolové commandy (`bin/console`)

| Command | Popis |
|---|---|
| `user:create <email> [--name=NAME]` | Vytvoří uživatele, heslo se zadá interaktivně. Slouží hlavně k vytvoření prvního uživatele, další lze přidat v administraci. |
| `feeds:fetch [--feed=ID]` | Stáhne všechny aktivní feedy (podmíněný GET přes ETag/Last-Modified), uloží nové články, případně dotáhne plný text. |
| `feeds:add <url>` | Přidá feed, ověří, že je platný, a načte titulek. |
| `digest:generate [--dry-run] [--since=DATETIME]` | *(fáze 2)* Vytvoří ranní digest, viz [ai-digest.md](ai-digest.md). `--dry-run` vypíše prompt a výsledek bez uložení. |
| `articles:prune [--days=90]` | Smaže staré články (kromě označených hvězdičkou a od fáze 2 i těch, které jsou v digestu). |
| `migrations:migrate` | Doctrine migrace (z `nettrine/migrations`). |

Všechny dlouhé commandy drží zámek (`symfony/lock`), takže souběžné spuštění skončí bez akce.

## Cron

```cron
# Fáze 1
*/30 * * * *  php /www/ai-rss-reader/bin/console feeds:fetch
0 3 * * 0     php /www/ai-rss-reader/bin/console articles:prune --days=90

# Fáze 2
CLAUDE_CODE_OAUTH_TOKEN=...   # nebo načíst ze souboru mimo git
45 5 * * *    php /www/ai-rss-reader/bin/console feeds:fetch && php /www/ai-rss-reader/bin/console digest:generate
```

Výstupy commandů se logují přes Tracy logger do `var/log/`.

## Konfigurace

- `config/common.neon`: služby, Doctrine, konzole, assets.
- `config/local.neon` (mimo git): DB přístupy; od fáze 2 i volba LLM backendu a modelů, cesta ke `claude`.

## Nasazení

- PHP 8.3+, MariaDB 10.11+, Node (jen pro build Vite), od fáze 2 Claude Code CLI.
- `composer install --no-dev`, `npm ci && npm run build`, `bin/console migrations:migrate`.
- Při první instalaci vytvořit prvního uživatele: `bin/console user:create <email>`.
