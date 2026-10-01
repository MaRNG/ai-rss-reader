# Architektura

## Přehled

```
            ┌──────────────── cron ─────────────────┐
            │ */30  bin/console feeds:fetch          │
            │ 45 5  bin/console digest:generate      │
            └───────────────┬───────────────────────┘
                            ▼
 RSS zdroje ──► FeedFetcher ──► MariaDB (feeds, articles)
                                    │
                                    ▼
                         DigestGenerator ──► LlmClient ──► claude -p
                                    │
                                    ▼
                      MariaDB (digests, digest_items)
                                    │
           Nette presentery (Latte + Vite + Naja) ◄── uživatel, 👍/👎
```

## Stack

| Vrstva | Volba |
|---|---|
| Framework | Nette 3.2, Latte |
| ORM | Doctrine přes `nettrine/orm`, `nettrine/dbal`, `nettrine/migrations` |
| DB | MariaDB (utf8mb4) |
| Konzole | Symfony Console přes `contributte/console` |
| Zámky | `symfony/lock` (aby se dva běhy cronu nepřekrývaly) |
| Spouštění procesů | `symfony/process` (volání `claude -p`) |
| RSS | `simplepie/simplepie` |
| Extrakce plného textu | `fivefilters/readability.php` (pro feedy s jen perexem) |
| Sanitizace HTML | `ezyang/htmlpurifier` |
| Frontend build | Vite přes `nette/assets` |
| Frontend JS | TypeScript, Naja (AJAX snippety Nette) |
| Grafy | Chart.js (statistiky) |
| Přihlášení | Nette Security, `Passwords` |

Frontend je server-side renderovaný (Latte). Vite bundluje TS/CSS, Naja zajišťuje AJAXové akce
(👍/👎, označit přečtené, hvězdička) bez reloadu stránky. Plná SPA není potřeba.
Popis jednotlivých stránek je v [ui.md](ui.md).

## Struktura adresářů

Kořenový adresář kódu je `App/` a jmenný prostor odpovídá cestě (PSR-4 `"App\\": "App/"`).
Položky označené *(f2)* přibudou až ve fázi 2.

```
App/
  Bootstrap.php                      temp → var/temp, log → var/log
  Core/
    RouterFactory.php
  Command/                           Symfony Console commandy (contributte/console)
    UserCreateCommand.php            user:create
    FeedAddCommand.php               feeds:add
    FeedFetchCommand.php             feeds:fetch
    ImageDownloadCommand.php         images:download
    ArticlePruneCommand.php          articles:prune
    DigestGenerateCommand.php        digest:generate (f2)
  Model/
    Database/
      Entity/                        Doctrine entity (atributy)
        User.php
        Feed.php
        Article.php
        Image.php
        Setting.php
        Digest.php                   (f2)
        DigestItem.php               (f2)
      Repository/                    Doctrine repozitáře, dotazy nad entitami
        UserRepository.php
        FeedRepository.php
        ArticleRepository.php        TOP 1 z feedu, výpisy, stránkování
        ImageRepository.php
        SettingRepository.php
        StatsRepository.php          agregace pro /stats
        DigestRepository.php         (f2)
      Migration/                     Doctrine migrace
    Feed/                            stahování a zpracování feedů
      FeedFetcher.php                podmíněný GET, uložení nových článků
      FeedParser.php                 obal nad SimplePie
      ContentExtractor.php           Readability, plný text
      HtmlSanitizer.php              HTML Purifier, content_html / content_text
    Image/                           obrázky článků (viz images.md)
      ImageExtractor.php             najde obrázky v článku a založí Image (pending)
      ImageDownloader.php            stažení + validace + přepis src v content_html
      ImageStorage.php               výpočet cesty <feed_id>/a/b/c/d/<hash>.<ext>, zápis, mazání
    Security/
      Authenticator.php              Nette\Security\Authenticator nad User
      UserFacade.php                 vytvoření/editace uživatele (sdílí admin i CLI)
    Settings/
      InterestProfile.php
    Digest/                          (f2) DigestGenerator, PromptBuilder
    Llm/                             (f2) LlmClient, ClaudeCliClient, AnthropicApiClient
  UI/
    Shared/                          společné pro Front i Admin
      BasePresenter.php
      @layout.latte
      Component/
        Paginator/                   Paginator control + šablona
        ArticleCard/                 karta článku (hlavní stránka, výpisy)
      Trait/
        ArticleFeedbackSignals.php   handle 👍/👎, hvězdička, přečteno (jen přihlášený)
    Front/
      Presenter/
        BaseFrontPresenter.php
        @layout.latte
        Home/
          HomePresenter.php
          Action/
            DefaultAction.php        trait: TOP 1 článek z každého feedu
          default.latte
        Feed/
          FeedPresenter.php
          Action/
            DefaultAction.php        trait: přehled feedů
            DetailAction.php         trait: články feedu
          default.latte
          detail.latte
        News/
          NewsPresenter.php
          Action/DefaultAction.php
          default.latte
        Article/
          ArticlePresenter.php
          Action/DetailAction.php
          detail.latte
        Stats/
          StatsPresenter.php
          Action/DefaultAction.php
          default.latte
        Digest/                      (f2)
        Error/
          ErrorPresenter.php, Error4xxPresenter.php + šablony
    Admin/
      Presenter/
        BaseAdminPresenter.php       kontrola přihlášení v startup()
        @layout.latte
        Sign/
          SignPresenter.php
          Action/InAction.php, OutAction.php
          in.latte
        Dashboard/
          DashboardPresenter.php     rozcestník administrace
        Feed/
          FeedPresenter.php
          Action/DefaultAction.php, AddAction.php, EditAction.php
          default.latte, add.latte, edit.latte
        User/
          UserPresenter.php
          Action/DefaultAction.php, AddAction.php, EditAction.php
          default.latte, add.latte, edit.latte
        Settings/
          SettingsPresenter.php
          Action/DefaultAction.php
          default.latte
      Form/
        SignInFormFactory.php
        FeedFormFactory.php
        UserFormFactory.php
        SettingsFormFactory.php
assets/                              vstupy pro Vite
  front/main.ts, front/main.css
  front/stats.ts                     Chart.js grafy
  admin/main.ts, admin/main.css
bin/
  console
config/
  common.neon                        aplikace, mapping, Tracy, session
  services.neon                      služby, commandy, repozitáře
  doctrine.neon                      nettrine (dbal, orm, migrations)
  local.neon.dist                    šablona lokální konfigurace
  local.neon                         (mimo git) DB přístupy
prompts/                             (f2) prompty a JSON schémata
tests/
var/
  log/                               (mimo git) Tracy logy
  temp/                              (mimo git) cache DI, Latte, Doctrine proxy
www/
  index.php
  assets/                            (mimo git) build výstup Vite
  files/                             (mimo git) stažené obrázky: <feed_id>/a/b/c/d/<hash>.<ext>
docs/
composer.json, package.json, vite.config.ts, phpstan.neon
```

## Konvence

### Presenter + traity pro akce

Presenter je tenký: drží závislosti (konstruktor) a skládá akce z traitů. Každá akce
(`action<X>` / `render<X>`, případně její formuláře a signály) je ve vlastním traitu v podsložce `Action/`.

```php
namespace App\UI\Front\Presenter\Feed;

final class FeedPresenter extends BaseFrontPresenter
{
    use Action\DefaultAction;
    use Action\DetailAction;

    public function __construct(
        private readonly FeedRepository $feedRepository,
        private readonly ArticleRepository $articleRepository,
    ) {
        parent::__construct();
    }
}
```

```php
namespace App\UI\Front\Presenter\Feed\Action;

trait DetailAction
{
    public function renderDetail(int $id, int $page = 1): void
    {
        $feed = $this->feedRepository->find($id) ?? $this->error();
        $this->template->feed = $feed;
        $this->template->articles = $this->articleRepository->findByFeed($feed, $page);
    }
}
```

- Traity používají závislosti presenteru přes `$this`. Pro PHPStan mají `@phpstan-require-extends`
  na svůj presenter.
- Šablona akce leží vedle presenteru (`detail.latte`), což Nette 3.2 najde automaticky.
- Komponenty a formuláře, které patří jen k jedné akci, vytváří `createComponent<X>()` v traitu dané akce.

### Mapování presenterů

```neon
application:
    mapping:
        Front: App\UI\Front\Presenter\**Presenter
        Admin: App\UI\Admin\Presenter\**Presenter
```

`Front:Feed` → `App\UI\Front\Presenter\Feed\FeedPresenter`.

### Model

- `Model/Database/Entity`: jen entity, bez logiky závislé na službách.
- `Model/Database/Repository`: veškeré čtecí dotazy (QueryBuilder/DQL). Presentery nesahají na `EntityManager` napřímo.
- Zápisy, které mají víc kroků, jdou přes služby/fasády (`FeedFetcher`, `UserFacade`) a ty používají
  i commandy, takže admin a CLI sdílejí stejnou logiku.

### Commandy

- Jeden command = jedna třída v `App/Command/`, název `<Oblast><Akce>Command`.
- Registrace v `services.neon`, název commandu přes atribut `#[AsCommand(name: 'feeds:fetch')]`.
- Commandy jsou tenké a volají služby z `Model/`.

## LLM vrstva (fáze 2)

`DigestGenerator` nezávisí přímo na `claude -p`, ale na rozhraní:

```php
interface LlmClient
{
    /** Vrátí data odpovídající $jsonSchema. */
    public function complete(string $systemPrompt, string $userPrompt, array $jsonSchema, string $model): array;
}
```

- `ClaudeCliClient`: výchozí implementace, volá `claude -p` (subscription), viz [ai-digest.md](ai-digest.md).
- `AnthropicApiClient`: volitelná implementace přes `anthropic-ai/sdk` (placené API). Přepíná se v `services.neon`.

Díky tomu jde backend změnit bez zásahu do pipeline.
