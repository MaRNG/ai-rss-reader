# Siftly — specifikace

Osobní RSS čtečka, která každé ráno nechá Clauda projít nové články, vybrat ty nejrelevantnější
a připravit krátké shrnutí („digest“).

## Obsah

| Dokument | O čem je |
|---|---|
| [roadmap.md](roadmap.md) | Fáze implementace: 1. RSS čtečka, 2. digest s Claudem |
| [ui.md](ui.md) | Stránky, statistiky, administrace a přihlášení |
| [images.md](images.md) | Stahování a ukládání obrázků článků do `www/files` |
| [architecture.md](architecture.md) | Stack, komponenty, struktura adresářů |
| [data-model.md](data-model.md) | Doctrine entity a schéma v MariaDB |
| [ai-digest.md](ai-digest.md) | Ranní zpracování Claudem: pipeline, prompty, volání `claude -p` (fáze 2) |
| [operations.md](operations.md) | Cron, konzolové commandy, nasazení |

## Cíle

- Stahovat články z vlastního seznamu RSS/Atom feedů.
- Každé ráno připravit digest: top N relevantních článků s odůvodněním a souhrn všeho ostatního.
- Učit se z mé zpětné vazby (👍/👎, hvězdička, přečteno).
- Jednoduchý webový frontend pro čtení článků (TOP článek z každého feedu, přehled feedů, novinky, statistiky) a digestu.
- Administrace s přihlášením pro ruční správu feedů a uživatelů.

## Mimo rozsah (zatím)

- Veřejná registrace, role a oprávnění (uživatele zakládá admin, všichni mají stejná práva).
- Zpětná vazba a digest zvlášť pro každého uživatele.
- Mobilní aplikace, push notifikace.
- Vlastní trénování modelů nebo embeddingy.

## Rozhodnutí

- Články se čtou přímo v aplikaci a detail má i proklik na původní článek.
- Veřejná část (hlavní stránka, feedy, novinky, detail článku, statistiky) je bez přihlášení.
  Přihlášení vyžaduje jen administrace a akce 👍/👎, hvězdička, přečteno.
- TOP 1 článek z feedu: ve fázi 1 nejnovější, ve fázi 2 ho vybírá Claude.
- Statistiky se počítají podle `fetched_at`.
- Obrázky článků se stahují do `www/files/<feed_id>/a/b/c/d/<hash>.<ext>`.

## Otevřené otázky

- (fáze 2) Kolik článků má být v digestu (výchozí návrh: 10)?
- (fáze 2) Má digest chodit i e-mailem?
