# PhoneShop CRM

A small PHP CRM for phone repair shops, built for GitHub + Hostinger deployment with uploads stored outside the web root.

## Local quick start

```bash
mkdir -p /private/tmp/phoneshopcrm-uploads/{images,teachers,lessons,documents}
export PHONESHOPCRM_ENV_PATH=/Users/taytay/Documents/Phoneshopcrm/.env.local
cp .env.example .env.local
php database/init_sqlite.php
php -S 127.0.0.1:8080
```

Edit `.env.local` so `UPLOAD_BASE_PATH` points to your external upload directory.

## Deployment

See [HOSTINGER_DEPLOY.md](HOSTINGER_DEPLOY.md) before deploying. The key rule: never place user uploads inside `public_html`.
