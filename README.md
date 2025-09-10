# VXD-Laravel-admin

A Laravel admin starter.

> **Screens / UI / Requirements:**  
> https://drive.google.com/drive/folders/1uT9BHMDA1i6n6xDjOAXxi_DnkJAf3Mu5?usp=drive_link

---

## Prerequisites

- **PHP** ≥ 8.1 (extensions: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`)
- **Composer** ≥ 2.x  
- **Node.js** ≥ 18.x and **npm** (or **yarn** / **pnpm**) for Vite
- **Git**

---

## Quick Start (Clone → `.env` → Run)

```bash
# 1) Clone
git clone <REPO_URL> vxd-laravel-admin
cd vxd-laravel-admin

# 2) Install PHP dependencies
composer install

# 3) Create environment file
cp .env.example .env

# 4) Generate app key
php artisan key:generate

# 5) install npm
npm install

# 6) project build and serve
npm run dev
php artisan serve 
