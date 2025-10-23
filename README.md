# Bilet Satın Alma Platformu

[cite_start]**Başlangıç Tarihi:** 04.10.2025 [cite: 2]
[cite_start]**Teslim Tarihi:** 24.10.2025 [cite: 3]

## Proje Açıklaması

[cite_start]Bu proje, PHP ve SQLite kullanılarak geliştirilmiş dinamik, veritabanı destekli ve çok kullanıcılı bir otobüs bileti satış platformudur[cite: 5, 6]. [cite_start]Proje, farklı kullanıcı rolleri (Ziyaretçi, Yolcu, Firma Yetkilisi, Admin) için yetkilendirme sistemlerini ve temel web güvenlik prensiplerini içermektedir[cite: 7, 14].

## Özellikler

### Genel Kullanıcı (Ziyaretçi & Yolcu)
* [cite_start]**Sefer Arama ve Listeleme:** Kalkış ve varış noktasına göre sefer arama ve listeleme[cite: 16].
* **Sefer Detayları Görüntüleme:** Seçilen seferin tüm detaylarını (firma, saat, fiyat, koltuk durumu) görme.
* [cite_start]**Kullanıcı Kayıt ve Giriş:** Yolcuların sisteme kayıt olup giriş yapabilmesi[cite: 19].
* **Bilet Satın Alma:**
    * [cite_start]Giriş yapmış kullanıcılar için sanal bakiye ile bilet satın alma[cite: 20].
    * İnteraktif koltuk seçimi (dolu koltuklar seçilemez).
    * Çoklu koltuk seçebilme.
    * [cite_start]Kupon kodu kullanarak indirim uygulama[cite: 45].
* **Hesabım Alanı:**
    * Profil bilgilerini ve bakiyeyi görüntüleme.
    * [cite_start]Satın alınmış biletleri listeleme[cite: 21].
* [cite_start]**Bilet İptali:** Kalkış saatine 1 saatten fazla süre varsa bileti iptal etme ve ücret iadesi alma[cite: 23, 24].
* [cite_start]**PDF Bilet:** Satın alınan biletleri PDF formatında indirme[cite: 21, 22].
* [cite_start]**Uyarılar:** Giriş yapmadan bilet almaya çalışınca uyarı ve yönlendirme[cite: 17].

### Firma Admin (Firma Yetkilisi)
* [cite_start]**Sefer Yönetimi (CRUD):** Sadece kendi firmasına ait seferleri oluşturma, düzenleme ve silme[cite: 26, 28].
* [cite_start]**Kupon Yönetimi (CRUD):** Sadece kendi firmasında geçerli indirim kuponları oluşturma, düzenleme ve silme[cite: 29].
* **Özel Panel:** Kendine ait yönetim paneline erişim.

### Admin
* [cite_start]**Firma Yönetimi (CRUD):** Yeni otobüs firmaları oluşturma, düzenleme ve silme[cite: 32].
* [cite_start]**Firma Admin Yönetimi (CRUD):** Yeni "Firma Admin" kullanıcıları oluşturma ve firmalara atama[cite: 32].
* [cite_start]**Global Kupon Yönetimi (CRUD):** Tüm firmalarda geçerli indirim kuponları oluşturma, düzenleme ve silme[cite: 33].
* **Özel Panel:** Sistemin tüm özelliklerine erişim sağlayan ana yönetim paneli.

## Kullanılan Teknolojiler

* [cite_start]**Backend:** PHP 8.2 [cite: 9]
* [cite_start]**Veritabanı:** SQLite [cite: 11]
* [cite_start]**Frontend:** HTML, CSS, JavaScript [cite: 10]
* [cite_start]**CSS Framework:** Bootstrap 5.3 [cite: 10]
* **PDF Kütüphanesi:** tFPDF
* **Web Sunucusu:** Apache (Docker imajı üzerinden)
* [cite_start]**Paketleme:** Docker & Docker Compose [cite: 50]

## Güvenlik Önlemleri

* **SQL Injection:** Tüm veritabanı sorgularında Prepared Statements kullanıldı.
* **Cross-Site Scripting (XSS):** Kullanıcıdan gelen tüm veriler `htmlspecialchars()` ile filtrelenerek ekrana basıldı.
* **Cross-Site Request Forgery (CSRF):** Veri değiştiren tüm formlara (kayıt, giriş, ekleme, silme, güncelleme, bilet alma) CSRF token koruması eklendi.
* **Password Hashing:** Kullanıcı şifreleri `password_hash()` ile güvenli bir şekilde hash'lenerek saklandı ve `password_verify()` ile doğrulandı.
* **Session Güvenliği:**
    * Oturum çerezleri için `HttpOnly` ve `SameSite=Lax` bayrakları ayarlandı.
    * Başarılı giriş sonrası `session_regenerate_id(true)` ile session fixation saldırılarına karşı önlem alındı.
* **Yetkilendirme:** Tüm yönetim paneli sayfalarında (`admin`, `firma_admin`) role dayalı erişim kontrolü (`auth_check.php`) uygulandı.
* **Hata Yönetimi:** Canlı ortamda detaylı hata mesajlarının kullanıcıya gösterilmesi engellendi (bkz. `config/init.php`).

## Kurulum ve Çalıştırma (Docker ile)

1.  Bilgisayarınızda [Docker Desktop](https://www.docker.com/products/docker-desktop/)'ın kurulu ve çalışır olduğundan emin olun.
2.  Proje dosyalarını klonlayın veya indirin.
3.  Komut istemcisini (terminal) projenin ana dizininde açın.
4.  Aşağıdaki komutu çalıştırın:
    ```bash
    docker-compose up --build
    ```
5.  Tarayıcınızdan `http://localhost:8080/` adresine giderek projeye erişin. (Eğer `docker-compose.yml` dosyasında portu değiştirdiyseniz ilgili port numarasını kullanın, örn: `http://localhost:7777/`).

## Veritabanı Şeması

Projede kullanılan SQLite veritabanı şeması aşağıdaki tablolardan oluşmaktadır:
* `users`: Kullanıcı bilgilerini (yolcu, firma admin, admin) tutar.
* `companies`: Otobüs firmalarının bilgilerini tutar.
* `trips`: Otobüs seferlerinin detaylarını (güzergah, zaman, fiyat vb.) tutar.
* `tickets`: Satın alınan biletlerin bilgilerini (kimin, hangi sefere, hangi koltuğu, ne kadara aldığı vb.) tutar.
* `coupons`: İndirim kuponlarının bilgilerini (kod, oran, limit, tarih, ait olduğu firma) tutar.

[cite_start]Detaylı şema için proje dökümanına [cite: 53] veya `database.sqlite` dosyasına bakılabilir.

---
