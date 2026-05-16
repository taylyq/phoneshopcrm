# Hostinger Deployment Guide

## Web root

Deploy this repository with Hostinger Git so the repository contents land directly in your hosting account's `public_html` directory. This repo is intentionally flat for Hostinger: `index.php`, `serve-file.php`, `diagnostics.php`, and `assets/` live at the web root.

Git/Hostinger redeployment may overwrite anything inside `public_html`, so do not put persistent uploads, secrets, logs, or generated database files there. The included `.htaccess` blocks direct browser access to `app/`, `database/`, `scripts/`, `storage/`, `config.php`, `.env`, and docs.

## Environment file

Put the production env file outside `public_html` whenever possible:

```text
/home/USERNAME/phoneshopcrm.env
```

Set `PHONESHOPCRM_ENV_PATH=/home/USERNAME/phoneshopcrm.env` in Hostinger if your PHP setup supports server environment variables. If not, the app also checks these locations:

```text
/home/USERNAME/phoneshopcrm.env
/home/USERNAME/.env
/home/USERNAME/domains/YOUR_DOMAIN/phoneshopcrm.env
/home/USERNAME/domains/YOUR_DOMAIN/.env
```

Avoid placing `.env` in `public_html`; the app intentionally loads outside-web-root env files first. If you temporarily place `.env` in `public_html` while debugging, delete it after confirming the external env path works. Never commit real `.env` files to GitHub.

Required production values:

```dotenv
APP_ENV=production
APP_URL=https://your-domain.com
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_database
DB_USERNAME=u123456789_user
DB_PASSWORD=replace-with-hostinger-password
UPLOAD_BASE_PATH=/home/USERNAME/uploads
ADMIN_PIN=change-this-pin
```

## Upload storage

Create upload folders manually using FTP, SSH, or Hostinger File Manager before first use:

```text
/home/USERNAME/uploads/images
/home/USERNAME/uploads/teachers
/home/USERNAME/uploads/lessons
/home/USERNAME/uploads/documents
```

Ensure PHP can write to each folder. The app stores only relative paths such as `images/photo.jpg` or `documents/invoice.pdf` in the database. It never stores full server paths.

`UPLOAD_BASE_PATH` must be the shared parent folder, not an individual type folder. Use `/home/USERNAME/uploads`, not `/home/USERNAME/uploads/images`.

## Serving files

Uploaded files are served only through:

```text
/serve-file.php?type=images&file=photo.jpg
/serve-file.php?type=documents&file=invoice.pdf
```

Do not create public upload folders or direct URLs for uploads.

## Diagnostics

Open `/diagnostics.php` after deployment if the app cannot connect to the database or write uploads. It shows safe setup details only:

- env file found/missing
- DB driver, host, database, username
- password set/missing
- safe connection failure reason
- upload folders writable/unwritable

It never displays the actual password.

## Verification

Run before launch:

```bash
php scripts/verify_deployment.php
```

Also verify:

- PHP syntax passes.
- External upload folders exist and are writable.
- `public_html` does not contain user-uploaded files.
- Uploads are saved with `move_uploaded_file()` to `UPLOAD_BASE_PATH`.
- HTML references uploads through `/serve-file.php`.
- Real `.env` files are not committed.
- Create a ticket with an image/document and confirm it displays/downloads through the PHP endpoint.

## Demo data

After the env file and upload folders are ready, seed the live MySQL database from SSH:

```bash
cd /home/USERNAME/domains/YOUR_DOMAIN/public_html
PHONESHOPCRM_ENV_PATH=/home/USERNAME/phoneshopcrm.env php scripts/seed_demo.php
```

The seeder creates the database tables if needed, writes small demo files into `UPLOAD_BASE_PATH`, and inserts four demo repair tickets. It only removes/recreates records with demo phone numbers like `demo-555-0101`; real customer records are left alone.
