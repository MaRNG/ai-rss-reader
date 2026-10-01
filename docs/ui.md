# Stránky a administrace

Výpisy článků jsou vždy seřazené **od nejnovějších** (`published_at DESC`; pokud chybí, použije se `fetched_at`).
Dlouhé výpisy se stránkují (výchozí 30 článků na stránku).

## Veřejná část (fáze 1)

| Stránka | URL | Obsah |
|---|---|---|
| Hlavní stránka | `/` | Pro každý aktivní feed **TOP 1 článek**, tedy jeho nejnovější článek. Karty jsou seřazené podle data článku, nejnovější první. Karta obsahuje název feedu, titulek, perex, datum a odkaz na detail feedu. |
| Přehled feedů | `/feeds` | Seznam aktivních feedů: název, web, počet článků, datum posledního článku. Kliknutí otevře detail feedu. |
| Detail feedu | `/feeds/<id>` | Články daného feedu, stránkované. |
| Novinky | `/news` | Promíchané články ze všech feedů, stránkované. |
| Detail článku | `/article/<id>` | Obsah článku (`content_html`) a odkaz na zdroj. |
| Statistiky | `/stats` | Viz níže. |

Ve fázi 2 se na hlavní stránku přidá ranní digest (viz [ai-digest.md](ai-digest.md)).

### Statistiky `/stats`

1. **Počet článků podle feedu:** tabulka s celkovým počtem článků za každý feed a počtem za posledních 7 a 30 dní.
2. **Denní přírůstek:** spojnicový graf počtu nově přidaných článků za den (všechny feedy dohromady).
3. **Denní přírůstek podle feedu:** skládaný sloupcový graf, kde každý sloupec je den a barevné segmenty jsou jednotlivé feedy.

- Období grafů se volí přepínačem 7 / 30 / 90 dní (výchozí 30). Dny bez článků se zobrazí jako 0.
- „Přidaný“ článek se počítá podle `fetched_at`, tedy podle toho, kdy ho Siftly stáhl.
  Při prvním importu nového feedu proto vznikne v grafu jednorázový skok (viz otevřené otázky).
- Grafy kreslí **Chart.js** (bundlovaný přes Vite). Data poskytuje presenter jako JSON a agregují se SQL dotazem
  `GROUP BY DATE(fetched_at), feed_id`.

## Administrace `/admin` (fáze 1)

Přístup jen po přihlášení. Nepřihlášený uživatel je přesměrován na `/admin/sign/in`.

| Stránka | Obsah |
|---|---|
| Přihlášení / odhlášení | Formulář e-mail + heslo. |
| Feedy | Seznam všech feedů (i neaktivních) s posledním stažením a poslední chybou. Přidání feedu ručně zadáním URL (ověří se, že je platný, a načte se titulek), editace (název, `fetch_full_text`, aktivní), smazání, tlačítko „stáhnout teď“. |
| Uživatelé | Seznam uživatelů, vytvoření nového, editace (jméno, e-mail, změna hesla, aktivní), smazání. Uživatel nemůže smazat ani deaktivovat sám sebe. |
| Nastavení | Profil zájmů (`interest_profile`). Uloží se už ve fázi 1, použije se ve fázi 2. |

### Uživatelé a přihlášení

- Nette Security: vlastní `Authenticator` nad entitou `User`, hesla přes `Nette\Security\Passwords` (bcrypt/argon2).
- Všichni uživatelé mají stejná práva (žádné role). Data (feedy, články) jsou společná.
- **První uživatel** se vytvoří z CLI: `bin/console user:create <email> [--name=...]`. Heslo se zadá
  interaktivně (skrytý vstup), aby nezůstalo v historii shellu. Další uživatele lze přidávat v administraci i z CLI.
- Odhlášení po 14 dnech nečinnosti a ochrana proti CSRF formuláři Nette.

## Zpětná vazba

Akce 👍/👎, hvězdička a „přečteno“ jsou dostupné jen přihlášenému uživateli (AJAX přes Naja).
Zpětná vazba je zatím společná pro všechny uživatele, ne pro každého zvlášť.
