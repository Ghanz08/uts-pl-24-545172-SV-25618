# 💡 IdeaVault — Hackathon & Side-Project Idea Vault

UTS Konstruksi dan Evolusi Perangkat Lunak (SVPL261501) · Ghani Zulhusni Bahri · NIM 24/545172/SV/25618

Ide bagus jarang datang pas lagi di depan laptop. IdeaVault adalah tempat parkir ide project yang muncul
pas mandi, nongkrong, atau di kelas — supaya waktu ada lomba atau tugas, tinggal buka vault dan pilih.

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12, Laravel Sanctum (token), dijalankan oleh **FrankenPHP 1.13.3 / PHP 8.4.27** |
| Frontend | Vue 3 + Vite, Vue Router, Axios, disajikan oleh Nginx 1.30 |
| Database | MySQL 8.4 (named volume `db_data`) |
| CI/CD | GitHub Actions → GitHub Container Registry (ghcr.io) |

```
.
├── backend/                 Laravel API  (Dockerfile, .dockerignore, docker/Caddyfile)
├── frontend/                Vue app      (Dockerfile multi-stage, .dockerignore, docker/default.conf.template)
├── postman/                 IdeaVault.postman_collection.json
├── .github/workflows/ci.yml backend-test, frontend-build, deploy (main only)
├── docker-compose.yml
└── .env.example             salin ke .env (tidak di-commit)
```

## Fitur

- **Login/logout token** (Sanctum). Satu-satunya endpoint publik adalah `POST /api/login`.
- **CRUD `project_ideas`** — setiap user hanya bisa melihat/mengubah ide miliknya sendiri.
- **Status siklus ide**: 💡 spark → 🧪 brewing → 🔨 building → 🚀 shipped, atau 🧊 shelved.
- **Asal ide**: 🚿 shower, ☕ nongkrong, 🎓 kelas, 🌙 mimpi, 📱 scrolling.
- **Hype level 1–5 🔥**, target event, deadline (badge H-n), link repo, pin favorit.
- **🎲 Idea Roulette** — "gak tau mau ngerjain apa?": pilih acak ide yang masih terbuka, dibobot hype level.
- **Statistik**: jumlah ide per status dan *shipping rate*.

### Tabel `project_ideas`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `user_id` | FK users | pemilik ide |
| `title` | string | wajib |
| `tagline` | string? | pitch satu kalimat |
| `description` | text? | penjelasan fitur |
| `tech_stack` | json array | `["Laravel","Vue"]` |
| `difficulty` | `easy\|medium\|hard` | |
| `status` | `spark\|brewing\|building\|shipped\|shelved` | API juga mengirim `is_completed` (= shipped) |
| `origin` | `shower\|nongkrong\|kelas\|mimpi\|scrolling` | |
| `hype_level` | 1–5 | |
| `target_event` | string? | misal "Gemastik 2026" |
| `deadline` | date? | |
| `repo_url` | url? | |
| `is_pinned` | bool | |

## Menjalankan dengan Docker Compose

```bash
cp .env.example .env
# isi APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD. APP_KEY bisa dibuat dengan:
echo "base64:$(openssl rand -base64 32)"

docker compose up -d --build
docker compose ps
```

- Aplikasi: <http://localhost:8080> — login dengan akun demo `demo@ideavault.test` / `password123`
  (dibuat oleh seeder saat pertama kali jalan; bisa diganti lewat `DEMO_USER_EMAIL`/`DEMO_USER_PASSWORD` di `.env`).
- API langsung (untuk Postman): <http://localhost:8000/api>

Data tetap ada setelah `docker compose down` lalu `docker compose up -d` karena MySQL menyimpan datanya di
named volume `db_data`. Hanya `docker compose down -v` yang menghapusnya.

## Alur data: Vue → Laravel → MySQL

```
Browser (Vue + Axios)
   │  GET/POST/PUT/DELETE  /api/project-ideas   + header Authorization: Bearer <token>
   ▼
frontend container — Nginx :80
   │  location /api/  →  proxy_pass http://backend:8000   (nama service Compose)
   ▼
backend container — FrankenPHP :8000 (Caddy + PHP 8.4)
   │  Laravel: auth:sanctum → FormRequest validation → Eloquent
   │  DB_HOST=db                                          (nama service Compose)
   ▼
db container — MySQL 8.4 :3306  →  named volume db_data
```

1. Halaman login mengirim `POST /api/login`. Laravel memvalidasi kredensial, membuat token Sanctum, dan
   Vue menyimpannya di `localStorage`.
2. Interceptor Axios (`frontend/src/api/http.js`) menempelkan `Authorization: Bearer <token>` ke setiap
   request berikutnya. Base URL-nya diambil dari `VITE_API_URL` (default `/api`), tidak ditulis mati di komponen.
3. Request dari browser masuk ke Nginx di container `frontend`, yang meneruskan `/api/*` ke service
   `backend` lewat jaringan Docker Compose — bukan ke IP laptop. Karena origin-nya sama, tidak ada masalah CORS.
4. FrankenPHP menjalankan Laravel. Middleware `auth:sanctum` mengecek token (401 kalau tidak ada),
   FormRequest memvalidasi input (422 kalau gagal), lalu Eloquent menulis/membaca tabel `project_ideas`
   di service `db`.
5. Laravel mengembalikan JSON (`201` saat create) yang langsung dirender ulang oleh Vue.

## API

Semua request dan response berformat JSON. Semua endpoint selain login butuh header
`Authorization: Bearer <token>`.

| Method | Endpoint | Keterangan | Status sukses |
|---|---|---|---|
| POST | `/api/login` | login, dapat token | 200 |
| POST | `/api/logout` | cabut token saat ini | 200 |
| GET | `/api/me` | profil user | 200 |
| GET | `/api/project-ideas` | daftar ide (`?status=&difficulty=&q=`) | 200 |
| POST | `/api/project-ideas` | tambah ide | **201** |
| GET | `/api/project-ideas/{id}` | detail ide | 200 |
| PUT/PATCH | `/api/project-ideas/{id}` | ubah ide | 200 |
| DELETE | `/api/project-ideas/{id}` | hapus ide | 200 |
| GET | `/api/project-ideas/roulette` | Idea Roulette | 200 |
| GET | `/api/project-ideas/stats` | statistik | 200 |

Error: `401` tanpa/dengan token tidak valid, `422` validasi gagal, `404` data tidak ada atau milik user lain.

**Postman**: impor `postman/IdeaVault.postman_collection.json`. Jalankan *Auth → Login* dulu; token dan
`idea_id` tersimpan otomatis ke variabel collection. Setiap request punya test status code, jadi seluruh
collection bisa dijalankan sekaligus lewat *Run collection*.

## Pengembangan lokal (tanpa Docker)

```bash
# backend
cd backend && composer install && cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --seed
php artisan serve            # http://localhost:8000
php artisan test             # feature test

# frontend
cd frontend && npm ci && cp .env.example .env
npm run dev                  # http://localhost:5173, /api diproksikan ke DEV_BACKEND_URL
```

## Docker

- **`backend/Dockerfile`** — base `dunglas/frankenphp:1.13.3-php8.4-alpine` (versi dipin; PHP 8.4.27 sudah
  memuat patch CVE-2026-109618/109619). `composer.json` + `composer.lock` disalin dan di-install **sebelum**
  kode aplikasi, supaya layer dependency ter-cache. Berjalan sebagai user non-root, punya `HEALTHCHECK` ke `/up`.
- **`frontend/Dockerfile`** — multi-stage: `node:24.22.1-alpine` untuk `npm ci && npm run build`, lalu hanya
  folder `dist/` yang disalin ke `nginx:1.30.6-alpine`. Image akhir tidak berisi `node_modules` maupun Node.js.
- Tidak ada nilai rahasia di Dockerfile. `APP_KEY` dan kredensial DB masuk lewat environment saat runtime
  (dari `.env` di lokal, dari GitHub Secrets di CI).

### Bukti FrankenPHP

```bash
docker compose logs backend | grep http.log.access   # access log Caddy/FrankenPHP per request API
curl -sI http://localhost:8000/up | grep -i server   # Server: Caddy
docker compose exec backend frankenphp version
```

## CI/CD (`.github/workflows/ci.yml`)

| Job | Jalan saat | Isi |
|---|---|---|
| `backend-test` | push ke `main`/`dev`/`feature/**`, PR ke `main`/`dev` | `composer install` → `php artisan test` |
| `frontend-build` | sama | `npm ci` → `npm run build` |
| `deploy` | **hanya push ke `main`**, setelah dua job di atas hijau | tulis `.env` dari Secrets → `docker compose up --wait` → smoke test login + list ide → push image ke GHCR dengan tag commit SHA |

Image hasil deploy:

- `ghcr.io/<owner>/<repo>-backend:<commit-sha>`
- `ghcr.io/<owner>/<repo>-frontend:<commit-sha>`

Secrets yang harus diisi di **Settings → Secrets and variables → Actions**: `APP_KEY`, `DB_PASSWORD`.

## Alur Git

- Branch: `main` (rilis), `dev` (integrasi), `feature/*` (fitur).
- Perubahan masuk ke `dev` dan `main` hanya lewat Pull Request.
- Branch protection di `main` dan `dev`: wajib PR dan status check `backend-test` + `frontend-build` lulus.
