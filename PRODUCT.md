# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Nette 3.2 + Latte, Doctrine + MariaDB, Symfony Console (contributte/console), Vite přes `nette/assets`,
TypeScript + Naja. Statické mockupy vznikají rovnou jako Vite + HTML/CSS (`assets/front/`), aby se CSS a TS
daly později napojit na Latte šablony. Podrobně v `docs/architecture.md`.

## Users

Primárně autor (jeden člověk), který si ráno projde novinky ze svých RSS feedů. K tomu malý okruh
známých (kolegové, rodina), kteří sledují stejné feedy a chodí na veřejnou část bez přihlášení.
Administraci (feedy, uživatelé) používá jen pár přihlášených lidí.

## Product Purpose

Osobní RSS čtečka Siftly: stahuje články z vybraných feedů, umožňuje je číst přímo v aplikaci
a ve fázi 2 každé ráno nechá Clauda vybrat nejrelevantnější články a připravit krátký digest.
Úspěch = ráno během pár minut vím, co se v mých zdrojích stalo, a přečtu si jen to, co stojí za to.

## Positioning

Ne další obecná čtečka pro masy, ale „síto“ nad vlastním malým seznamem zdrojů. Ve fázi 2 třídí
Claude podle osobního profilu zájmů a zpětné vazby (👍/👎).

## Operating Context

- Ranní rituál: otevřít Siftly, projít novinky / TOP článek z každého feedu, rozkliknout pár článků.
- Čtení probíhá v aplikaci (plný text, lokálně stažené obrázky), s proklikem na původní článek.
- Feedy se stahují cronem každých 30 minut; obsah je česky i anglicky.

## Capabilities and Constraints

- Veřejná část bez přihlášení: hlavní stránka (TOP 1 článek z každého feedu), přehled feedů a detail feedu,
  novinky ze všech feedů, detail článku, statistiky. Výpisy vždy od nejnovějších.
- Administrace s přihlášením: správa feedů a uživatelů, profil zájmů.
- Fáze 2: ranní digest s Claudem (`claude -p`).
- UI je česky (předpoklad, uživatel komunikuje česky; nepotvrzeno výslovně).
- Specifikace: `docs/`.

## Brand Commitments

- Název: **Siftly**.
- Vzhled nemá působit „AI generovaně“. Výslovná reference: GoodRead RSS reader list view
  (https://cdn.psdrepo.com/images/2x/goodread-rss-reader-list-view-psd-attached-c4.jpg):
  tmavý sidebar s feedy a počty, světlý výpis článků s náhledem, zdrojem a časem.
- Rozložení zůstává podle reference (navigace vlevo, seznam feedů, výpis článků). Vzhled už ale nemá
  referenci kopírovat (změna 2026-10-01): vlastní identita postavená na **oranžové** (výslovné přání uživatele).

## Evidence on Hand

Žádná reálná data ani loga zatím nejsou. Mockupy používají ukázkové feedy a články; nevydávat je za skutečné.

## Product Principles

1. Rychle přehlédnout, pomalu číst: výpis je hustý a skenovatelný, detail článku je klidný.
2. Obsah zdrojů je hlavní, aplikace ustupuje.
3. Malé a osobní: žádné onboardingy, marketing ani sociální prvky.
4. Zdroj je vždy na jedno kliknutí (proklik na původní článek).
