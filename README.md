# Bilet Satın Alma Platformu

PHP ve SQLite kullanılarak geliştirilmiş, çok kullanıcılı bir otobüs bileti satış platformu.

## Ana Özellikler

* **Kullanıcılar İçin:** Sefer arama, bilet alma (sanal bakiye ile), koltuk seçimi, kupon kullanma, biletlerim sayfasında görüntüleme, iptal etme (1 saat kuralı) ve PDF indirme.
* **Firma Admin İçin:** Kendi firmasına ait seferleri ve kuponları yönetme (CRUD).
* **Admin İçin:** Tüm firmaları, firma adminlerini ve global kuponları yönetme (CRUD).

## Teknolojiler

* **Backend:** PHP 8.2
* **Veritabanı:** SQLite
* **Frontend:** HTML, CSS, JavaScript, Bootstrap 5.3
* **PDF:** tFPDF
* **Sunucu:** Apache (Docker üzerinden)
* **Paketleme:** Docker & Docker Compose

## Kurulum ve Çalıştırma (Docker ile)

1.  Bilgisayarınızda [Docker Desktop](https://www.docker.com/products/docker-desktop/) kurulu ve çalışır olsun.
2.  Proje dosyalarını indirin.
3.  Komut istemcisini proje ana dizininde açın.
4.  `docker-compose up --build` komutunu çalıştırın.
5.  Tarayıcıdan `http://localhost:7777/` adresine gidin (`docker-compose.yml` içindeki porta göre değişebilir).

## Veritabanı Yapısı

* `users`: Kullanıcılar (yolcu, firma_admin, admin)
* `companies`: Otobüs firmaları
* `trips`: Seferler
* `tickets`: Satın alınan biletler
* `coupons`: İndirim kuponları
