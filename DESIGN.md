---
name: Siftly
description: Osobní RSS čtečka – rámec aplikace v plné RSS oranžové, výpis článků jako čistý bílý papír.
colors:
  orange: "#c94a05"
  orange-ink: "#b8430a"
  on-orange: "#ffffff"
  sidebar: "#f4f3f1"
  sidebar-line: "#e6e4e0"
  sidebar-hover: "#ebe9e6"
  page: "#ffffff"
  line: "#eceae7"
  line-strong: "#dedbd7"
  ink: "#1c1917"
  ink-soft: "#57524d"
  ink-faint: "#6b655f"
  hover: "#f4f3f1"
  thumb-bg: "#ece9e5"
typography:
  display:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "32px"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.03em"
  headline:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "24px"
    fontWeight: 700
    letterSpacing: "-0.03em"
  title:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "18px"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "-0.012em"
  section:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "15px"
    fontWeight: 700
  body:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "15px"
    fontWeight: 400
    lineHeight: 1.5
  excerpt:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "14.5px"
    fontWeight: 400
    lineHeight: 1.5
  meta:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "14px"
    fontWeight: 500
    lineHeight: 1.6
    fontFeature: "tnum"
  label:
    fontFamily: "Onest, system-ui, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "13px"
    fontWeight: 500
rounded:
  tile: "3px"
  sm: "4px"
  dot: "50%"
spacing:
  xs: "4px"
  sm: "8px"
  md: "12px"
  lg: "16px"
  row: "20px"
  xl: "24px"
  gutter: "40px"
components:
  rail-link:
    backgroundColor: "{colors.orange}"
    textColor: "{colors.on-orange}"
    rounded: "{rounded.sm}"
    size: "48px"
  rail-link-active:
    backgroundColor: "{colors.on-orange}"
    textColor: "{colors.orange}"
    rounded: "{rounded.sm}"
    size: "48px"
  rail-avatar:
    backgroundColor: "{colors.orange}"
    textColor: "{colors.on-orange}"
    rounded: "{rounded.sm}"
    size: "40px"
  feed-item:
    backgroundColor: "{colors.sidebar}"
    textColor: "{colors.ink-soft}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    height: "40px"
    padding: "0 12px"
  feed-item-active:
    backgroundColor: "{colors.page}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "40px"
    padding: "0 12px"
  tool-toggle:
    backgroundColor: "{colors.page}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
    height: "36px"
    padding: "0 14px"
  tool-toggle-pressed:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.page}"
    rounded: "{rounded.sm}"
    height: "36px"
  action-icon:
    textColor: "{colors.ink-faint}"
    rounded: "{rounded.sm}"
    size: "34px"
  action-icon-pressed:
    textColor: "{colors.orange}"
    rounded: "{rounded.sm}"
    size: "34px"
  pager-more:
    backgroundColor: "{colors.page}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    height: "40px"
    padding: "0 18px"
  new-dot:
    backgroundColor: "{colors.orange}"
    rounded: "{rounded.dot}"
    size: "8px"
---

# Design System: Siftly

## Overview

**Creative North Star: „Oranžová RSS ikony“**

Celá aplikace je postavená z jednoho materiálu: plné oranžové barvy, kterou má RSS ikona. Rámec s hlavní navigací je tím materiálem natřený celý, výpis článků vedle něj je čistý bílý papír s tenkými linkami. Oranžová tak nese identitu a orientaci (kde jsem, co je nové), papír nese čtení. Rozložení (pruh, sidebar s feedy, výpis po dnech) vychází z referenční čtečky, vzhled ne: žádný tmavý dashboardový rámec, žádná krémovo-cihlová paleta.

Hustota je čtenářská, ne dashboardová. Řádek článku je náhled vlevo, titulek s perexem a zdrojem uprostřed, čas a akce ve sloupci zarovnaném k pravému okraji. Hierarchie vzniká kontrastem velikosti a řezu jednoho písma (Onest), ne barvou a ne dekorací. Stavy (nový, přečtený, zapnutý přepínač, aktivní položka) jsou vyjádřené jako tištěné značky: plná výplň, tečka, ztlumení. Žádné bubliny, žádné obrysové kroužky, žádná zaoblení nad 4px.

Bílá na oranžové je možná proto, že oranžová je záměrně tmavší (#c94a05): bílá na ní má 4,7:1, takže projdou i 11px popisky spodní lišty na mobilu. Světlejší RSS oranžová (#ea5b0c) by dala jen 3,5:1.

**Key Characteristics:**
- Rámec v plné RSS oranžové s bílými ikonami a popisky; obsah na bílém papíře.
- Jedno písmo (Onest 400/500/600/700), hierarchie velikostí a řezem.
- Oranžová ve výpisu jen jako stav: tečka nového, hvězdička, počet nových.
- Ploché povrchy, 1px linky, nejvýš 4px zaoblení.
- Časy a čísla v tabulkových číslicích, zarovnané do sloupce.

## Colors

Jedna sytá barva (RSS oranžová) a teplá, lehce nahnědlá škála neutrálů; nic dalšího.

### Primary
- **RSS oranžová** (orange): plocha celého navigačního pruhu, dlaždice „Všechny feedy“ v sidebaru, `theme-color` prohlížeče. Ve výpisu jen jako značka stavu: tečka nového článku, vyplněná hvězdička, focus ring. Je to jediná oranžová v systému; žádné další odstíny pro plochy.
- **Oranžový inkoust** (orange-ink): tmavší varianta pro oranžový *text* na bílé a šedé (5,46:1 na bílé): počet nových v hlavičce, hover titulku a zdroje, ikona a hover „Přidat feed“. Kde má být oranžová čitelná jako písmo, použij tuto.
- **Bílá na oranžové** (on-orange): ikony, nápis „Siftly“, avatar a popisky na oranžové; zároveň výplň aktivní dlaždice v pruhu (s oranžovou ikonou) a ikona na oranžové dlaždici feedu.

### Neutral
- **Papír** (page): pozadí výpisu, hlavičky a lepkavých nadpisů dnů; výplň aktivní položky v sidebaru.
- **Teplá šeď sidebaru** (sidebar): pozadí sloupce s feedy; stejná hodnota slouží jako hover (hover) na papíře.
- **Linka sidebaru** (sidebar-line) a **hover sidebaru** (sidebar-hover): pravý okraj sidebaru, oddělení patičky, hover položek feedu.
- **Linka** (line): 1px oddělovač mezi řádky článků.
- **Silná linka** (line-strong): spodní hrana hlavičky, obrysy textových tlačítek a „Načíst další“.
- **Inkoust** (ink): titulky, nadpisy, 2px čára pod nadpisem dne, výplň zapnutého přepínače.
- **Tlumený inkoust** (ink-soft): perex, název zdroje, neaktivní názvy feedů.
- **Slabý inkoust** (ink-faint): časy, počty, popisky, přečtené titulky, ikony akcí. Stále 5,75:1 na bílé.
- **Podklad náhledu** (thumb-bg): výplň rámečku náhledu, než se načte obrázek.

### Named Rules
**The One Orange Rule.** V systému je právě jedna oranžová plocha (orange) a jedna oranžová pro text (orange-ink). Nepřidávej světlejší ani tmavší oranžové plochy, gradienty ani tinty.

**The State-Only Orange Rule.** Na bílém papíře výpisu se oranžová objeví jen jako stav: tečka nového, zapnutá hvězdička, počet nových, hover odkazu, focus. Zdroj, metadata a dekorace jsou inkoustové.

**The White-On-Orange Rule.** Na oranžové je vždy bílá (on-orange). Oranžová je proto tak tmavá (#c94a05), aby bílá měla 4,7:1 i pro 11px popisky; světlejší odstín nepoužívej.

## Typography

**Display Font:** Onest (s fallbackem system-ui, -apple-system, Segoe UI, sans-serif)
**Body Font:** Onest
**Label/Mono Font:** Onest s tabulkovými číslicemi (`font-variant-numeric: tabular-nums`) pro časy a počty

**Character:** Jedna moderní groteska s českou diakritikou v řezech 400–700. Výrazné nadpisy jsou stažené (záporný prostrkání), drobné texty zůstávají otevřené.

### Hierarchy
- **Display** (700, 32px, 1.1, -0.03em): nadpis stránky v hlavičce („Novinky“, název feedu). 26px pod 900px, 22px pod 600px.
- **Headline** (700, 22px, -0.02em): nadpis „Feedy“ v hlavičce sidebaru.
- **Logo** (700, 17px, -0.02em): jen nápis „Siftly“ bílou v horní části oranžového pruhu, žádná obrazová značka. Na mobilu (spodní lišta) se nezobrazuje.
- **Title** (600, 18px, 1.3, -0.012em, `text-wrap: pretty`): titulek článku. 16px v kompaktním režimu a na mobilu. Přečtený článek: 500 a slabý inkoust.
- **Section** (700, 15px): nadpis dne („Dnes“), datum vedle v 400 a slabém inkoustu.
- **Body** (400, 15px, 1.5): základní text.
- **Excerpt** (400, 14.5px, 1.5): perex, oříznutý na 2 řádky, tělo řádku max 72ch. 13.5px na mobilu.
- **Meta** (500, 14px, 1.6, tabulkové číslice): čas článku, podtitulek hlavičky (14px, 400).
- **Label** (500–600, 13px): textová tlačítka, nadpis „Feedy“, název zdroje (600), počty. Popisky spodní lišty na mobilu 11px/600.

### Named Rules
**The Size-Not-Color Rule.** Hierarchii nese velikost a řez, ne barva. Titulek (18/600) je viditelně větší než všechna metadata (13–14px).

**The Tabular Rule.** Každé číslo, které se porovnává ve sloupci (časy, počty), má tabulkové číslice.

## Layout

Aplikace je mřížka tří sloupců: oranžový pruh (`--rail-w` 76px), sidebar s feedy (`--sidebar-w` 248px), výpis (`minmax(0, 1fr)`). Pruh i sidebar jsou lepkavé na plnou výšku okna. Hlavička výpisu je lepkavá (`--topbar-h` 104px), pod ní se lepí nadpis dne. Vodorovný okraj výpisu je `--gutter` (40px).

Řádek článku je mřížka náhled (`--thumb-w` × `--thumb-h`, 120 × 80px) / tělo / postranní sloupec, mezera 24px, svislý padding `--row-pad` (20px), dělený 1px linkou. Postranní sloupec má čas nahoře a akce dole, obojí zarovnané vpravo. Kompaktní režim mění jen proměnné: náhled 56 × 40px, padding 10px, skryje perex a zdroj, čas a akce do jedné řady.

Responzivita:
- **≤ 1200px:** sidebar 224px, okraj 28px, přepínače v hlavičce jen jako ikony 36 × 36px (s `aria-label` a `title`).
- **≤ 900px:** pruh 64px, hlavička 80px, sidebar se stává vysouvacím panelem (288px) přes výpis se scrimem; v hlavičce se objeví tlačítko menu.
- **≤ 600px:** pruh se stává spodní lištou 64px s ikonou a popiskem; hlavička 68px, okraj 16px, náhled 84 × 64px; čas a akce se přesunou pod náhled.

Rytmus mezer stojí na krocích 4 / 8 / 12 / 16 / 20 / 24 / 40px.

## Elevation & Depth

Systém je plochý. Hloubku nesou plochy (oranžová, teplá šeď, bílá) a 1px linky, ne stíny. Stín se objeví jen tam, kde něco skutečně leží nad něčím jiným: aktivní položka sidebaru jako lístek na šedém podkladu a vysunutý panel feedů na úzkých displejích.

### Shadow Vocabulary
- **Lístek** (`box-shadow: 0 1px 2px rgb(28 25 23 / 0.08), 0 0 0 1px var(--sidebar-line)`): jen aktivní feed v sidebaru.
- **Panel** (`box-shadow: 8px 0 24px rgb(28 25 23 / 0.16)`): vysunutý seznam feedů pod 900px, spolu se scrimem `rgb(28 25 23 / 0.35)`.

### Named Rules
**The Flat Paper Rule.** Řádky, hlavička, tlačítka a náhledy nemají stín. Oddělení dělá linka, ne vznášení.

## Shapes

Hranatý, tiskový tvarový jazyk. Interaktivní prvky, náhledy a avatar mají 4px; drobné dlaždice favikon a ikony „Všechny feedy“ 3px. Jediný kulatý tvar v systému je 8px tečka nového článku. Avatar je čtvercová dlaždice s 2px inkoustovým obrysem a iniciálami, ne kruh. Ikony jsou Lucide v tahu 1,75 (16–22px); logo je jen nápis „Siftly“, bez obrazové značky.

**The 4px Ceiling Rule.** Žádné zaoblení nad 4px, žádné pilulky, žádné kulaté obrysové bubliny. Výjimkou je jen stavová tečka.

## Components

### Navigační pruh
Oranžová plocha s bílými ikonami 22px v dlaždicích 48 × 48px (4px). Hover: bílá 16 % (`rgb(255 255 255 / 0.16)`). Aktivní: plná bílá dlaždice s oranžovou ikonou. Focus ring v pruhu je bílý, ne oranžový. Dole avatar 40 × 40px s 2px bílým obrysem, při hoveru se vyplní bílou. Na mobilu spodní lišta s ikonou 20px a popiskem 11px/600.

### Položka feedu
40px vysoký řádek (4px), favikona 20px, název v tlumeném inkoustu 14px/500, počet vpravo 13px tabulkově. Hover: sidebar-hover a inkoust. Aktivní: bílý lístek se stínem „Lístek“, tučný inkoustový počet. „Všechny feedy“ má oranžovou dlaždici 20px s bílou ikonou. Patička „Přidat feed“ 14px/600 s ikonou v orange-ink.

### Buttons
- **Shape:** 4px.
- **Textový přepínač (tool):** 36px, padding 0 14px, 1px silná linka, bílý, 13px/500, ikona 16px. Hover: teplá šeď. Zapnutý (`aria-pressed="true"`): plná inkoustová výplň, bílý text. Pod 1200px jen ikona, popisek zůstává jako `aria-label` se shodným textem.
- **Akce řádku:** ikona 18px v poli 34 × 34px (38 × 38px na mobilu), bez obrysu, slabý inkoust. Hover: teplá šeď a inkoust. Zapnutá hvězdička: oranžová, vyplněná.
- **Načíst další:** 40px, padding 0 18px, 1px silná linka, 14px/600, hover teplá šeď.
- **Focus:** 2px oranžový outline s odsazením 2px (na oranžové bílý).

### Řádek článku (signature)
Náhled 120 × 80px (4px, object-fit cover), při hoveru řádku se obrázek pomalu přiblíží na 1.05 (0,5s, `--ease-out`). Titulek 18/600; nový článek má před titulkem oranžovou tečku 8px se skrytým textem „Nový“. Pod titulkem perex na 2 řádky, pod perexem název zdroje 13px/600 v tlumeném inkoustu (žádný štítek nad titulkem). Přečtený: titulek 500 slabým inkoustem, náhled na 60 % průhlednosti. Čas vpravo nahoře, akce vpravo dole.

### Nadpis dne
Lepkavý pod hlavičkou, 15px/700 s datem v 400 slabým inkoustem, zakončený 2px inkoustovou čarou přes celou šířku výpisu.

### Hlavička
Lepkavá, bílá, spodní 1px silná linka. Display nadpis, pod ním počet nových v orange-ink 700 tabulkově a text ve slabém inkoustu (na mobilu zkrácený). Vpravo textové přepínače.

## Do's and Don'ts

### Do:
- **Do** dávej na oranžovou plochu vždy bílou on-orange (4,7:1), i pro 11px popisky.
- **Do** používej orange-ink pro jakýkoli oranžový text na bílé nebo šedé.
- **Do** označuj stav plnou výplní (inkoustová dlaždice, inkoustový přepínač, vyplněná hvězdička) nebo tečkou, ne obrysem a bublinou.
- **Do** zarovnávej časy a počty vpravo v tabulkových číslicích.
- **Do** dávej ikonovým tlačítkům přístupný název; u přepínačů, které se pod 1200px zúží na ikonu, musí `aria-label` obsahovat viditelný popisek.
- **Do** řeš hustotu výpisu přes proměnné (`--thumb-w`, `--thumb-h`, `--row-pad`), ne přes nové komponenty.

### Don't:
- **Don't** zavádět druhou oranžovou, gradient nebo oranžový tint na plochách.
- **Don't** barvit název zdroje, metadata ani dekorace oranžovou; oranžová ve výpisu znamená stav.
- **Don't** dávat bílý text ani bílé ikony na oranžovou (3,5:1).
- **Don't** zaoblovat víc než 4px, kreslit pilulky nebo kulaté avatary.
- **Don't** dávat štítek ani zdroj nad titulek článku; zdroj patří pod perex.
- **Don't** přidávat stíny řádkům, tlačítkům ani hlavičce; plochy oddělují linky.
- **Don't** vracet tmavý dashboardový rámec ani krémovo-cihlovou paletu.
