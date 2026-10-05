# Bus Ticket Purchase Platform

![PHP](https://img.shields.io/badge/PHP-8.2-777bb4)
![SQLite](https://img.shields.io/badge/SQLite-003B57)
![Docker](https://img.shields.io/badge/Docker-2496ED)
![License](https://img.shields.io/badge/license-MIT-green)

<p align="center"><b><a href="#english">English</a></b> · <b><a href="#türkçe">Türkçe</a></b></p>

---

## English

A multi-user bus-ticket sales platform built with PHP and SQLite.

### Features

- **For passengers:** search trips, buy tickets (with a virtual balance), pick seats, use coupons, view "my tickets", cancel (1-hour rule) and download a PDF.
- **For company admins:** manage their own company's trips and coupons (CRUD).
- **For the super admin:** manage all companies, company admins and global coupons (CRUD).

### Tech stack

- **Backend:** PHP 8.2
- **Database:** SQLite
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5.3
- **PDF:** tFPDF
- **Server:** Apache (via Docker)
- **Packaging:** Docker & Docker Compose

### Setup (with Docker)

1. Make sure [Docker Desktop](https://www.docker.com/products/docker-desktop/) is installed and running.
2. Download the project files.
3. Open a terminal in the project root.
4. Run `docker-compose up --build`.
5. Visit `http://localhost:7777/` (the port may differ per `docker-compose.yml`).

### Database schema

- `users` — passengers, company admins, super admin
- `companies` — bus companies
- `trips` — scheduled trips
- `tickets` — purchased tickets
- `coupons` — discount coupons

### License

MIT — see [LICENSE](./LICENSE).

---

## Türkçe

PHP ve SQLite kullanılarak geliştirilmiş, çok kullanıcılı bir otobüs bileti satış platformu.

### Ana özellikler

- **Kullanıcılar için:** sefer arama, bilet alma (sanal bakiye ile), koltuk seçimi, kupon kullanma, "biletlerim" sayfasında görüntüleme, iptal etme (1 saat kuralı) ve PDF indirme.
- **Firma admin için:** kendi firmasına ait seferleri ve kuponları yönetme (CRUD).
- **Admin için:** tüm firmaları, firma adminlerini ve global kuponları yönetme (CRUD).

### Teknolojiler

- **Backend:** PHP 8.2
- **Veritabanı:** SQLite
- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5.3
- **PDF:** tFPDF
- **Sunucu:** Apache (Docker üzerinden)
- **Paketleme:** Docker & Docker Compose

### Kurulum ve çalıştırma (Docker ile)

1. Bilgisayarınızda [Docker Desktop](https://www.docker.com/products/docker-desktop/) kurulu ve çalışır olsun.
2. Proje dosyalarını indirin.
3. Komut istemcisini proje ana dizininde açın.
4. `docker-compose up --build` komutunu çalıştırın.
5. Tarayıcıdan `http://localhost:7777/` adresine gidin (`docker-compose.yml` içindeki porta göre değişebilir).

### Veritabanı yapısı

- `users` — kullanıcılar (yolcu, firma_admin, admin)
- `companies` — otobüs firmaları
- `trips` — seferler
- `tickets` — satın alınan biletler
- `coupons` — indirim kuponları

### Lisans

MIT — bkz. [LICENSE](./LICENSE).
