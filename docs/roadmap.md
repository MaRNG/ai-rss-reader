# Roadmapa

## Fáze 1: RSS čtečka (bez AI)

Funkční čtečka bez napojení na Clauda.

- Kostra projektu: Nette 3.2, Doctrine (nettrine) + MariaDB, `contributte/console`, Vite přes `nette/assets`.
- Entity `User`, `Feed`, `Article`, `Image`, `Setting` + migrace (viz [data-model.md](data-model.md)).
- Commandy `user:create`, `feeds:fetch`, `feeds:add`, `images:download`, `articles:prune` (viz [operations.md](operations.md)).
- Stahování feedů: SimplePie, podmíněný GET, deduplikace přes `guid_hash`, sanitizace HTML,
  volitelně plný text přes Readability.
- Stahování obrázků článků do `www/files/<feed_id>/a/b/c/d/<hash>.<ext>`, command `images:download`
  (viz [images.md](images.md)).
- Veřejná část (viz [ui.md](ui.md)):
  - bez přihlášení,
  - hlavní stránka s TOP 1 (nejnovějším) článkem z každého feedu,
  - přehled feedů s proklikem na články daného feedu,
  - novinky ze všech feedů promíchané dohromady,
  - čtení článku přímo v aplikaci (plný text) s proklikem na původní článek,
  - statistiky: počet článků podle feedu, graf denního přírůstku, graf denního přírůstku podle feedu.
  - Výpisy jsou vždy seřazené od nejnovějších.
- Administrace s přihlášením:
  - ruční přidávání a správa RSS feedů,
  - správa uživatelů (první uživatel vznikne přes CLI `user:create`),
  - nastavení profilu zájmů (`interest_profile`), který se uloží už teď a použije se ve fázi 2.
- Akce 👍/👎, hvězdička a přečteno přes Naja (jen pro přihlášené).
- Cron pro `feeds:fetch`, `images:download` a `articles:prune`.

Zpětná vazba (👍/👎, hvězdička, přečteno) se sbírá už ve fázi 1, aby měl Claude ve fázi 2 historii, ze které se může učit.

## Fáze 2: Ranní digest s Claudem

Celá specifikace je v [ai-digest.md](ai-digest.md).

- Entity `Digest`, `DigestItem` + migrace.
- Rozhraní `LlmClient` a implementace `ClaudeCliClient` (`claude -p`), volitelně `AnthropicApiClient`.
- Prompty a JSON schémata v `prompts/`.
- Command `digest:generate` (třídění a digest) a ranní cron.
- Frontend: dashboard s posledním digestem a archiv digestů.
- TOP 1 článek z každého feedu na hlavní stránce vybírá Claude (podle relevance z kroku třídění) místo nejnovějšího.

## Později (nápady)

- Digest e-mailem.
- Claude jednou týdně navrhne aktualizaci profilu zájmů podle zpětné vazby.
