# Ranní digest (Claude)

> **Fáze 2.** Ve fázi 1 se neimplementuje, viz [roadmap.md](roadmap.md).

## Pipeline `digest:generate`

1. **Výběr vstupu:** články s `fetched_at` od konce posledního úspěšného digestu.
   Pokud je jich 0, digest se nevytváří.
2. **Krok 1, třídění:** do promptu jde profil zájmů, posledních ~30 článků s 👍/👎 jako příklady
   a pro každý nový článek `id`, zdroj, titulek a perex (zkrácený na ~500 znaků).
   Výstup (JSON schema): `[{article_id, score 0–10, reason}]`.
   Při velkém počtu článků (> ~300) se vstup dělí do dávek.
3. **Krok 2, digest:** top N článků podle `score` (výchozí 10) s plným `content_text`
   (zkráceným na ~8 000 znaků).
   Výstup: `{overview_md, items: [{article_id, summary_md, cluster_key}]}`.
   Claude zároveň sloučí duplicitní zprávy z více zdrojů (`cluster_key`).
4. **Uložení:** `Digest` + `DigestItem` (všechny ohodnocené články, shrnutí jen top N), `status = done`.
   Při chybě `status = failed` a `error`; frontend zobrazí poslední úspěšný digest.

Prompty a JSON schémata jsou ve složce `prompts/` (verzované v gitu), ne v PHP kódu.
Výstupy se validují proti schématu a neplatné `article_id` se zahodí.

## Backend: `claude -p` (subscription)

Výchozí implementace `ClaudeCliClient` spouští Claude Code v neinteraktivním režimu přes `symfony/process`:

```bash
claude -p \
  --model sonnet \
  --output-format json \
  --json-schema "$(cat prompts/triage.schema.json)" \
  --tools "" \
  --append-system-prompt "$SYSTEM_PROMPT" \
  < user-prompt.txt
```

- `--tools ""` vypne všechny nástroje, Claude jen čte vstup a odpovídá.
- `--json-schema` vynutí strukturovaný výstup a `--output-format json` vrací obálku s výsledkem a metadaty.
- Vstup jde přes stdin, aby se nenarazilo na limit délky argumentů.
- Proces se spouští s timeoutem (např. 10 min) a se zámkem `symfony/lock`.
- Model je konfigurovatelný v `config/local.neon` (třeba `sonnet` pro třídění, `opus` pro digest).

### Autentizace na serveru

- Claude Code nainstalovat pod uživatelem, pod kterým běží cron.
- `claude setup-token` vytvoří dlouhodobý OAuth token; ten se předá cronu jako `CLAUDE_CODE_OAUTH_TOKEN`.
- **V prostředí cronu nesmí být nastavený `ANTHROPIC_API_KEY`**, jinak má přednost a volání se účtuje na API.

### Proč `claude -p` a ne API Haiku

Odhad pro ~150 nových článků denně: ~45k vstupních a ~8k výstupních tokenů za den.

| Varianta | Cena za měsíc (odhad) |
|---|---|
| `claude -p` se subscription | 0 Kč navíc, čerpá limity předplatného (jeden běh denně je zanedbatelný) |
| API Haiku 4.5 ($1 / $5 za MTok) | ~2,5 USD, s Batch API ~1,3 USD |
| API Sonnet 5.5 ($2 / $10 za MTok) | ~5 USD |
| API Opus 5.5 ($4 / $20 za MTok) | ~10 USD |

Subscription vychází zadarmo a nabídne silnější model než Haiku. API je spolehlivější pro
provoz (žádný token k obnovování, žádné sdílení limitů s interaktivní prací, přesné `usage`) a i s Haiku stojí
jen pár dolarů měsíčně. Proto je v kódu rozhraní `LlmClient` a přechod na `AnthropicApiClient` je
jen změna konfigurace.

## Zpětná vazba a učení

- 👍/👎, hvězdička a otevření článku se ukládají k `Article`.
- Při dalším digestu jdou poslední hodnocené články do promptu jako příklady („tohle mě zajímalo / nezajímalo“).
- Profil zájmů se edituje ručně v Nastavení. Později by mohl Claude jednou týdně navrhnout jeho aktualizaci
  podle zpětné vazby (otevřená otázka).
