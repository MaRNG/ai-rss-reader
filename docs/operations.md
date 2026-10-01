# Provoz

## Konzolové commandy (`bin/console`)

| Command | Popis |
|---|---|
| `user:create <email> [--name=NAME]` | Vytvoří uživatele, heslo se zadá interaktivně. Slouží hlavně k vytvoření prvního uživatele, další lze přidat v administraci. |
| `feeds:fetch [--feed=ID]` | Stáhne všechny aktivní feedy (podmíněný GET přes ETag/Last-Modified), uloží nové články, případně dotáhne plný text. |
| `feeds:add <url>` | Přidá feed, ověří, že je platný, a načte titulek. |
| `digest:generate [--dry-run] [--since=DATETIME]` | *(fáze 2)* Vytvoří ranní digest, viz [ai-digest.md](ai-digest.md). `--dry-run` vypíše prompt a výsledek bez uložení. |
| `images:download [--limit=200]` | Stáhne čekající obrázky článků do `www/files` a přepíše jejich URL v článcích, viz [images.md](images.md). |
| `articles:prune [--days=90]` | Smaže staré články (kromě označených hvězdičkou a od fáze 2 i těch, které jsou v digestu) a obrázky, na které už nevede žádný článek. |
| `db:purge [--feeds] [--all]` | Smaže data z databáze: bez přepínačů články a obrázky (i soubory ve `www/files`), `--feeds` navíc feedy, `--all` vše včetně uživatelů a nastavení. Vypíše, co smaže, a vyžaduje napsání slova „smazat“; s `-n` nic nesmaže. |
| `migrations:migrate` | Doctrine migrace (z `nettrine/migrations`). |

Všechny dlouhé commandy drží zámek (`symfony/lock`), takže souběžné spuštění skončí bez akce.

## Cron

```cron
# Fáze 1
*/30 * * * *  php /www/ai-rss-reader/bin/console feeds:fetch && php /www/ai-rss-reader/bin/console images:download
0 3 * * 0     php /www/ai-rss-reader/bin/console articles:prune --days=90

# Fáze 2
CLAUDE_CODE_OAUTH_TOKEN=...   # nebo načíst ze souboru mimo git
45 5 * * *    php /www/ai-rss-reader/bin/console feeds:fetch && php /www/ai-rss-reader/bin/console digest:generate
```

Výstupy commandů se logují přes Tracy logger do `var/log/`.

## Konfigurace

- `config/common.neon`: služby, Doctrine, konzole, assets.
- `config/local.neon` (mimo git): DB přístupy; od fáze 2 i volba LLM backendu a modelů, cesta ke `claude`.

## Instalace (lokální vývoj)

```bash
composer install
npm ci && npm run build                  # assety do www/assets (manifest pro nette/assets)
cp config/local.neon.dist config/local.neon   # přístup k DB
bin/console migrations:migrate
bin/console user:create <email> --name "Jméno"
bin/console feeds:add https://www.root.cz/rss/clanky/
bin/console images:download
```

- Lokálně běží přes Caddy na https://ai-rss-reader.localhost (`/www/_server/sites/ai-rss-reader.caddy`):
  `/files/*`, `/assets/*` a `/mockups/*` jsou jen statické soubory, vše ostatní jde na PHP-FPM (`www/index.php`).
- Debug režim (Tracy) je zapnutý pro požadavky z `127.0.0.1`/`::1` nebo pokud existuje soubor `var/debug`.
  Konzole běží bez debug režimu (`SIFTLY_DEBUG=1` ho zapne); po změně konfigurace smazat `var/temp/cache`.
- `npm run dev` spustí Vite dev server; nette/assets ho pozná podle `www/assets/.vite/nette.json`.
  Přes HTTPS z Caddy ale prohlížeč nenačte skripty z `http://localhost:5173`, takže běžně se používá `npm run build`.
- Kontrola kódu: `vendor/bin/phpstan analyse`, `vendor/bin/latte-lint App`, `npx tsc -p .`.

## Nasazení

- PHP 8.4+ (vyvíjeno na 8.5), MariaDB 10.5+, Node (jen pro build Vite), od fáze 2 Claude Code CLI.
- `composer install --no-dev`, `npm ci && npm run build`, `bin/console migrations:migrate`.
- Složka `www/files` musí být zapisovatelná pro uživatele cronu i webserveru a webserver v ní nesmí spouštět PHP
  (nginx: `location ^~ /files/ { try_files $uri =404; }` bez předání na PHP-FPM; Apache: `php_flag engine off` v `.htaccess`).
- Při první instalaci vytvořit prvního uživatele: `bin/console user:create <email>`.
