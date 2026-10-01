# Siftly — specifikace

Osobní RSS čtečka, která každé ráno nechá Clauda projít nové články, vybrat ty nejrelevantnější
a připravit krátké shrnutí („digest“).

## Obsah

| Dokument | O čem je |
|---|---|
| [roadmap.md](roadmap.md) | Fáze implementace: 1. RSS čtečka, 2. digest s Claudem |
| [ui.md](ui.md) | Stránky, statistiky, administrace a přihlášení |
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

## Otevřené otázky

- (fáze 2) Kolik článků má být v digestu (výchozí návrh: 10)?
- (fáze 2) Má digest chodit i e-mailem?
- Čtení článků přímo v aplikaci, nebo jen proklik na zdroj?
- Má být veřejná část (hlavní stránka, feedy, novinky, statistiky) přístupná bez přihlášení? Návrh: ano, přihlášení jen pro administraci a 👍/👎.
- „TOP 1 článek z feedu“ ve fázi 1 = nejnovější článek. Od fáze 2 nejrelevantnější podle Clauda?
- Statistiky počítat podle `fetched_at` (skok při prvním importu feedu), nebo podle `published_at`?
