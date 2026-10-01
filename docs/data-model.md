# Datový model

MariaDB, kódování `utf8mb4` / `utf8mb4_unicode_ci`. Schéma spravují Doctrine entity (atributy)
a `nettrine/migrations`.

## Entity

### User

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| email | VARCHAR(255) UNIQUE | přihlašovací jméno |
| name | VARCHAR(255) | |
| password_hash | VARCHAR(255) | `Nette\Security\Passwords` |
| active | BOOL | neaktivní uživatel se nepřihlásí |
| created_at | DATETIME | |
| last_login_at | DATETIME NULL | |

### Feed

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| url | VARCHAR(2048) | URL feedu |
| site_url | VARCHAR(2048) NULL | |
| title | VARCHAR(255) | |
| etag | VARCHAR(255) NULL | pro podmíněný GET |
| last_modified | VARCHAR(255) NULL | pro podmíněný GET |
| last_fetched_at | DATETIME NULL | |
| last_error | TEXT NULL | poslední chyba stahování |
| error_count | INT | po N chybách v řadě se feed deaktivuje |
| fetch_full_text | BOOL | stahovat plný text přes Readability |
| active | BOOL | |

### Article

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| feed_id | FK → feed | |
| guid_hash | CHAR(64) UNIQUE | sha256 z GUID, jinak z URL; zajišťuje deduplikaci |
| url | VARCHAR(2048) | |
| title | VARCHAR(512) | |
| author | VARCHAR(255) NULL | |
| summary | TEXT NULL | perex z feedu |
| content_html | MEDIUMTEXT NULL | sanitizovaný HTML pro zobrazení (po stažení obrázků s lokálními URL) |
| content_text | MEDIUMTEXT NULL | čistý text pro LLM |
| published_at | DATETIME NULL | |
| fetched_at | DATETIME | |
| read_at | DATETIME NULL | |
| starred | BOOL | |
| feedback | TINYINT | -1 👎, 0 nic, 1 👍 |
| main_image_id | FK → image NULL | hlavní obrázek pro karty ve výpisech |

Indexy: `(fetched_at)`, `(published_at)`, `(feed_id, published_at)` pro TOP 1 článek z feedu a výpis feedu,
`(feed_id, fetched_at)` pro statistiky, FULLTEXT `(title, content_text)` pro vyhledávání.

### Image

Stažené obrázky článků, viz [images.md](images.md).

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| feed_id | FK → feed | |
| source_url | VARCHAR(2048) | původní URL |
| hash | CHAR(40) | sha1 ze `source_url`, zároveň název souboru |
| path | VARCHAR(255) NULL | relativně k `www/files`, např. `12/a/b/c/d/abcd….jpg` |
| mime_type | VARCHAR(32) NULL | |
| size | INT NULL | bajty |
| width / height | INT NULL | |
| status | VARCHAR(16) | `pending` / `done` / `failed` |
| attempts | TINYINT | počet pokusů o stažení |
| downloaded_at | DATETIME NULL | |

Unikátní index `(feed_id, hash)`, index `(status)`.

### ArticleImage

Vazba M:N mezi `Article` a `Image` (jeden obrázek může použít víc článků téhož feedu):
`article_id`, `image_id`, složený PK.

### Digest (fáze 2)

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| created_at | DATETIME | |
| period_from / period_to | DATETIME | které články pokrývá (podle `fetched_at`) |
| overview_md | TEXT | souhrnný přehled „co se děje“ (Markdown) |
| model | VARCHAR(64) | použitý model |
| backend | VARCHAR(16) | `cli` / `api` |
| articles_total | INT | kolik článků vstupovalo |
| status | VARCHAR(16) | `running` / `done` / `failed` |
| error | TEXT NULL | |
| usage_json | JSON NULL | tokeny/náklady, pokud jsou k dispozici |

### DigestItem (fáze 2)

| Sloupec | Typ | Poznámka |
|---|---|---|
| id | INT PK | |
| digest_id | FK → digest | |
| article_id | FK → article | |
| score | TINYINT | 0–10 relevance z kroku třídění |
| reason | VARCHAR(512) | proč je článek relevantní |
| summary_md | TEXT NULL | shrnutí (jen pro top N) |
| position | INT | pořadí v digestu |
| cluster_key | VARCHAR(64) NULL | stejné téma z více zdrojů |

### Setting

Key–value tabulka (`name` PK, `value` TEXT). Klíče pro digest se plně využijí až ve fázi 2. Hlavní klíče:

- `interest_profile`: volný text o mých zájmech („PHP, Nette, AI, self-hosting; nezajímá mě krypto…“)
- `digest_top_n`: počet článků v digestu (výchozí 10)
- `digest_language`: jazyk výstupu (výchozí `cs`)

## Retence

`articles:prune --days=90` maže články starší než 90 dní, pokud nejsou `starred` a nejsou
v žádném digestu (vazby z `digest_item` zůstávají platné).
