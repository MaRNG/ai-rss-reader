---
version: 1
slug: "mockups-news-html"
primary_target: "mockups/news.html"
related_targets: []
---

# Surface: Novinky (/news), statický mockup

Scope: statická stránka Novinky jako Vite + HTML/CSS mockup (`mockups/news.html`, `assets/front/`). Mode: Operate.
Audience: autor + pár známých, ranní projití novinek na notebooku i mobilu stejně často. Úkol: přehlédnout nové
články ze všech feedů od nejnovějších, otevřít detail v aplikaci nebo proklik na zdroj. Obsah: ukázkový (syntetický).
Uživatel: zachovat rozložení, vzhled nesmí připomínat GoodRead referenci, chce oranžovou.

## Direction contract

THESIS: Oranžová RSS ikony jako materiál celé aplikace: rámec s navigací je plná RSS oranžová, výpis je čistý papír. Odmítá tmavý dashboardový rámec reference i krémovo-cihlovou „AI“ paletu.
OWN-WORLD: Ikonový pruh v plné oranžové #C94A05 s bílými ikonami a popisky (uživatel chce bílou; oranžová ztmavená, aby bílá měla 4,7:1 i pro 11px popisky) a vlastní značkou (oblouky feedu), aktivní položka jako bílá dlaždice s oranžovou ikonou, avatar jako hranatá dlaždice s bílým obrysem; sidebar světle šedý #F3F3F1 s inkoustovými názvy, počty v tabulkových číslicích; bílý výpis, 1px linky, žádné kulaté obrysové bubliny ani zaoblení nad 4px; zdroj v tlumené inkoustové #57524D (oranžová ve výpisu jen pro stav: tečka nového, hvězdička, počet), titulky Onest 600 výrazně větší než metadata, časy zarovnané vpravo do sloupce, nepřečtené označené oranžovou tečkou. Písmo Onest.
STORY: Návštěvník vidí kolik přibylo, projede titulky po dnech (Dnes, Včera), tečka říká co je nové, otevře článek nebo zdroj, zúží výpis na feed v sidebaru.
FIRST VIEWPORT: vlevo oranžový pruh 72px, sidebar 240px se seznamem feedů, vpravo hlavička s velkým „Novinky“ a počtem a přepínači jako textová tlačítka; pod ní nadpis dne a 7 řádků: náhled vlevo, titulek + perex + zdroj pod perexem (žádný štítek nad titulkem), čas vpravo nahoře, akce vpravo dole. Pod 1200px a na mobilu jsou přepínače v hlavičce jen ikony (místo), s přístupným názvem, tooltipem a plnou inkoustovou výplní při zapnutí; na mobilu čas a akce pod náhledem.
FORM: user steer po re-rollu (oranžová, stejné rozložení) přebíjí přidělený směr; seed 6979bf3c reroll 1. Převzaté disciplíny: jedna vyhrazená barva pro stav nového (orientační mapa), časy zarovnané k pravému okraji (J-card), stavy jako tištěné značky bez zaoblení (centre rail), hierarchie kontrastem velikosti (specimen).
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
