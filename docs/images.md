# Obrázky článků (fáze 1)

Obrázky z článků se stahují k nám a servírují se z `www/files/`. Čtení v aplikaci tak nezávisí na zdroji
a nepředává mu informace o čtenáři.

## Umístění souborů

```
www/files/<feed_id>/<h1>/<h2>/<h3>/<h4>/<hash>.<ext>
```

Příklad: `www/files/12/a/b/c/d/abcd4f0e9c1b2a7d8e6f5a4b3c2d1e0f9a8b7c6d.jpg`

- `<feed_id>`: ID feedu, takže obrázky jsou rozdělené podle feedu a při smazání feedu stačí smazat jeho složku.
- `<hash>`: sha1 z URL zdrojového obrázku (40 hex znaků). Stejná URL ve stejném feedu = stejný soubor (deduplikace).
- `<h1>`–`<h4>`: první čtyři znaky hashe. Do jedné složky tak nespadne příliš mnoho souborů.
- `<ext>`: odvozená ze skutečného typu obrázku, ne z URL.
- Veřejná URL: `/files/12/a/b/c/d/abcd….jpg`.

Výpočet cesty má na starosti jediná třída `App\Model\Image\ImageStorage`, aby šlo schéma případně změnit na jednom místě.

## Stahování

Stahování je samostatný krok, aby pomalé obrázky nebrzdily stahování feedů:

1. `feeds:fetch` uloží článek s původními URL obrázků. Pro každý `<img src>` v obsahu a pro hlavní obrázek
   článku (enclosure / `media:content`, jinak první obrázek v textu) založí záznam `Image` ve stavu `pending`.
2. `images:download` zpracuje čekající obrázky: stáhne je, ověří, uloží a v `content_html` dotčených článků
   přepíše `src` na lokální URL.
3. Pokud se obrázek nepodaří stáhnout, zůstane v článku původní URL. Pokus se opakuje nejvýš 3krát
   (`attempts`), pak se obrázek označí jako `failed`.

`srcset` a `<picture><source>` se při sanitizaci odstraní, stahuje se jen `src`.

## Bezpečnost a limity

- Povolené jen `http`/`https`. Neplatí pro adresy, které se přeloží na lokální nebo privátní IP (ochrana proti SSRF).
- Typ se ověřuje z obsahu souboru (`finfo` + `getimagesize`), ne z hlavičky ani přípony.
  Povolené typy: JPEG, PNG, GIF, WebP, AVIF. **SVG se nestahuje** (může obsahovat skripty).
- Maximální velikost 10 MB, timeout 15 s, maximálně 5 přesměrování.
- Soubor se zapisuje nejdřív do dočasného souboru ve `var/temp` a do `www/files` se přesune až po ověření.
- Ve `www/files` je zakázané spouštění PHP (konfigurace webserveru, viz [operations.md](operations.md)).
- `www/files/` je v `.gitignore`.

## Úklid

- `articles:prune` po smazání starých článků smaže i obrázky, na které už nevede žádný článek (záznam i soubor).
- Smazání feedu v administraci smaže i složku `www/files/<feed_id>/`.
