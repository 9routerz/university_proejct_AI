# AU DS Competition: AI Data Agent

A data science competition platform for Air University Karachi. Participants clean and analyse a
messy dataset by chatting with an AI agent instead of writing code. The agent (Claude) writes pandas
code, runs it in a private E2B sandbox, and shows the output and charts. An AI judge scores each session.

Live site: https://au.novatixsolution.com · Created by [Novatix Solution](https://novatixsolution.com)

## Project structure

```
backend/        FastAPI server
  main.py         API endpoints: /start /chat /score /status /models /health
  config.py       all settings (read from backend/.env)
  sandbox.py      E2B sandboxes, Redis sessions, chat history
  data/           the dataset participants get (loaded as df)
  requirements.txt
  .env.example    copy to .env and fill in
ai/             The AI part
  llm.py          Claude through 9router: picks a model per job, falls back if one fails
  agent.py        understands each message, writes the code, answers questions
  scorer.py       AI judge that scores a session out of 100
frontend/       PHP website (Hostinger)
  index.php, about.php, guide.php, developer.php, agent.php (chat)
  api.php         talks to the backend; secrets never reach the browser
  bootstrap.php   settings, secure session, security headers
render.yaml     deploys the backend on Render
start-local.ps1 runs everything on your PC
```

## How a message flows

```
Browser → frontend/api.php → backend/main.py → ai/agent.py → Claude (9router)
                                                    ↓ code
                                         backend/sandbox.py → E2B sandbox (runs it on df)
```

## AI models (via 9router)

| Job | Model |
|---|---|
| Sort each message (code / question / score / blocked) | Claude Haiku 4.5 |
| Write code and answer questions | Chosen in the chat; default **Claude Sonnet 5.5** (also Opus 5.5, Opus 5, Haiku 4.5) |
| Score the session | Claude Opus 5.5 |

If a model fails, the next one in `CLAUDE_FALLBACK_MODELS` answers. All models are set in `backend/.env`.

## Run on your PC

```
copy backend\.env.example backend\.env        # fill in 9router, E2B and secrets; REDIS_URL=memory:// needs no Redis
copy frontend\.env.example frontend\.env      # BACKEND_URL=http://127.0.0.1:8000 and the same BACKEND_SECRET
powershell -ExecutionPolicy Bypass -File .\start-local.ps1
```

Open http://127.0.0.1:8080

## Deploy

**Backend on Render:** New > Blueprint > pick this repo. `render.yaml` creates the API and a Redis store.
Fill in `CLAUDE_API_KEY`, `CLAUDE_BASE_URL` (9router URL, no `/v1`), `E2B_API_KEY` and `BACKEND_SECRET`
(the same value the frontend uses). Check `https://<service>.onrender.com/health`.

**Frontend on Hostinger:** upload the contents of `frontend/` to `public_html`, plus a `config.local.php`:

```php
<?php
return [
    'BACKEND_URL'    => 'https://<service>.onrender.com',
    'BACKEND_SECRET' => '<same as on Render>',
    'ACCESS_CODE'    => '',   // optional code participants must enter
];
```

Turn on SSL in hPanel; `.htaccess` forces HTTPS and blocks the settings files.

## Security

- Backend endpoints need the `X-Backend-Secret` header; only the PHP server knows it. `/reset` needs `X-Admin-Key`.
- Frontend: CSRF tokens, HttpOnly + SameSite cookies, rate limits, optional access code, strict Content-Security-Policy.
- AI code runs only inside E2B sandboxes; dangerous code patterns are blocked first.
- Secrets live only in `.env` / `config.local.php` (git-ignored) and Render's environment settings.

## Good to know

- Render's free plan sleeps after ~15 minutes idle; the first request then takes about a minute.
- E2B's free plan limits how many sandboxes run at once and keeps each up to 1 hour.
- Never commit the clean reference (answer key) CSV.
