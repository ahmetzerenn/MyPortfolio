# Kişisel portföy sitesi (PHP + MySQL)

Bu depo, **çift dil (İngilizce / Türkçe)** destekleyen, **MySQL** ile veri tutan ve **yönetim paneli** içeren bir portföy uygulamasıdır. Halka açık sayfalar, iletişim formu, projelerin AJAX ile listelenmesi ve admin tarafında proje yönetimi tek bir PHP projesinde toplanmıştır.

**Bu belge**, kurulum, mimari ve dosya düzenini tek yerden görmek için özetlenmiştir.

---

## İçindekiler

1. [Özet: ne var, nasıl çalışıyor?](#1-özet-ne-var-nasıl-çalışıyor)
2. [Teknik gereksinimler](#2-teknik-gereksinimler)
3. [Mimari](#3-mimari)
4. [Dosya / klasör haritası](#4-dosya--klasör-haritası)
5. [Veritabanı: hangi SQL dosyası ne zaman?](#5-veritabanı-hangi-sql-dosyası-ne-zaman)
6. [Çoklu dil (EN / TR)](#6-çoklu-dil-en--tr)
7. [Güvenlik](#7-güvenlik)
8. [Yerel kurulum (kısa)](#8-yerel-kurulum-kısa)
9. [Canlıya alma notları](#9-canlıya-alma-notları)
10. [Kontrol listesi](#10-kontrol-listesi)
11. [Lisans](#11-lisans)

---

## 1. Özet: ne var, nasıl çalışıyor?

| Katman | İçerik |
|--------|--------|
| **Sunum (front)** | **Ana sayfa:** hero, öne çıkan proje vitrini (ör. video), veritabanından SSR ile diğer yayınlı projeler (vitrindeki proje hariç), kısa hikâye / yığın özeti, alt CTA. **Diğer:** hakkında, proje listesi (filtre + iskelet + `fetch`), proje detayı, iletişim (tercihen AJAX). |
| **Veri** | `projects` ve `messages` tabloları; PDO ile **hazırlanmış ifadeler** (prepared statements). |
| **API** | Projeler için JSON (`api/projects.php`), dil tercihi (`api/set-language.php`), isteğe bağlı sağlık kontrolü (`api/health.php`). |
| **Admin** | Oturum tabanlı giriş, proje ekleme/düzenleme/silme, iletişim mesajlarını listeleme; formlarda CSRF. |
| **Arayüz** | Vanilla JS (framework yok), açık/koyu tema, `prefers-reduced-motion` desteği. |

Akış özeti: tarayıcı genelde **PHP sayfaları** üzerinden HTML alır. **Ana sayfadaki** ek proje kartları sunucuda `portfolio_repository` ile doldurulur. **Proje listesi** sayfası kartları **`fetch` ile `api/projects.php`** JSON’undan üretir.

---

## 2. Teknik gereksinimler

- **PHP 8.1+** — uzantılar: `pdo_mysql`, `json`, `mbstring`, `session`
- **MySQL 8.0+** (veya JSON sütunları destekleyen MariaDB 10.6+ — `projects.tech_stack` vb.)
- **Apache** (`mod_rewrite` isteğe bağlı) **veya** nginx + PHP-FPM

---

## 3. Mimari

```text
[Ziyaretçi] → Web sunucusu → index.php, about.php, … (includes/bootstrap.php: oturum, CSRF, dil)
                                    ↓
                            includes/database.php (PDO)
                                    ↓
                         portfolio_repository.php  →  halka açık okuma (projeler, iletişim ekleme)
                         admin_*_repository.php    →  yönetim yazma/okuma
```

- **Ortak başlangıç:** `includes/bootstrap.php` oturumu açar, `config.php` ve çeviri yükleyiciyi bağlar, CSRF üretir, dili çözer.
- **Ayrım:** Halka açık veri erişimi `includes/portfolio_repository.php` içinde; admin tarafı `includes/admin_projects_repository.php` ve `includes/admin_messages_repository.php` ile yönetilir; kimlik doğrulama `includes/admin_auth.php`.

---

## 4. Dosya / klasör haritası

| Yol | Rol |
|-----|-----|
| `index.php`, `about.php`, `projects.php`, `contact.php`, `project.php` | Kamuya açık sayfalar |
| `includes/contact_handler.php` | `contact.php` doğrulama ve kayıt; istek JSON ise JSON yanıt |
| `api/projects.php` | Yayınlanmış projeleri JSON döndürür (liste + filtre için) |
| `api/set-language.php` | POST ile dil + çerez/oturum (CSRF ile) |
| `api/health.php` | JSON sağlık kontrolü (ör. `db: true` bağlantı testi); arayüzde menü linki yoktur |
| `admin/` | Giriş, proje CRUD, mesajlar, çıkış |
| `includes/bootstrap.php` | Oturum, CSRF, dil çözümü |
| `includes/config.php` | `APP_BASE_URL`, veritabanı sabitleri, varsayılan dil |
| `includes/lang.php`, `includes/lang/en.php`, `includes/lang/tr.php` | Çeviri anahtarları (`__()` ile) |
| `includes/portfolio_repository.php` | Projeleri okuma, iletişim kaydı, Türkçe için DB üstüne yerelleştirme katmanı |
| `assets/css/main.css` | Genel arayüz stilleri |
| `assets/js/main.js` | Tema, scroll reveal, proje listesi AJAX, iletişim formu AJAX, proje detay TOC |
| `assets/js/i18n.js` | Dil seçimi → `POST /api/set-language.php` |
| `sql/` | Şema, tam dışa aktarım, paylaşımlı hosting importu, migration dosyaları |
| `.htaccess` | Apache: sıkıştırma, önbellek başlıkları, `*.sql` dosyalarına doğrudan erişimi engelleme |

---

## 5. Veritabanı: hangi SQL dosyası ne zaman?

Aşağıdaki tablo, **sıfır kurulum** ile **mevcut veritabanını güncelleme** arasındaki farkı netleştirir.

| Dosya | Ne zaman kullanılır? |
|-------|----------------------|
| **`sql/portfolio_export.sql`** | En pratik yol: veritabanı + tablolar + örnek projeler + (genelde) admin kullanıcı tek seferde. Yerel veya sunucuda “projeyi ayağa kaldır” demek için uygundur. |
| **`sql/schema.sql`** + **`sql/seed_admin.sql`** | Önce boş şema, sonra sadece admin kullanıcı; proje verisini kendin ekleyeceksen veya export yerine parça parça kuracaksan. |
| **`sql/portfolio_import_shared_hosting.sql`** | Paylaşımlı hosting: `CREATE DATABASE` yetkisi yoksa; phpMyAdmin vb. ile mevcut veritabanına içe aktarım için yorumlarda yönlendirilir. |
| **`sql/migration_002_project_dynamic.sql`**, **`sql/migration_003_beyond_reach_colins_projects.sql`**, **`sql/migration_004_bta_dijital_project.sql`**, **`sql/migration_005_project_detail_sections.sql`** | Şema veya örnek veriyi **zaman içinde** güncellemek için. **Sıfırdan yalnızca `portfolio_export.sql` kullanıyorsan çalıştırmana gerek yoktur**; eski bir kurulumu güncelliyorsan tarih / içerik sırasına göre uygulanır. |

**Kısa özet:** Tam kurulum → `portfolio_export.sql`. Parça parça → `schema.sql` + `seed_admin.sql`. Paylaşımlı hosting → `portfolio_import_shared_hosting.sql`. Migration dosyaları → mevcut veritabanı geçişleri.

---

## 6. Çoklu dil (EN / TR)

- Metinler **`includes/lang/en.php`** ve **`includes/lang/tr.php`** içinde anahtar-değer çiftleri olarak tutulur; PHP tarafında `__()` ile okunur (sunucuda oluşturulan HTML = SSR metinleri).
- Dil sırası kabaca: `GET ?lang=` → oturum → çerez → varsayılan (`includes/bootstrap.php` içinde `resolve_language()`).
- Dil düğmesi: `assets/js/i18n.js` önce **`POST /api/set-language.php`** (CSRF + oturum/çerez), ardından sayfayı **`?lang=`** ile yeniler; böylece hem oturum hem sunucu tarafı çevirileri tutarlı kalır.
- **Projeler:** İngilizce metinler veritabanından gelir. Türkçe seçildiğinde, bilinen proje kimlikleri için `includes/portfolio_repository.php` içindeki **`portfolio_apply_locale_project_overlay()`** fonksiyonu, `tr.php` içindeki `proj_a_*`, `proj_b_*`, `proj_c_*` gibi anahtarlarla **DB alanlarının üzerine yazılır** (seed İngilizce kaldığı için bu katman kullanılır).

---

## 7. Güvenlik

- Üretimde **HTTPS**; Apache kullanıyorsan `.htaccess` içindeki HTTPS yönlendirmesini TLS hazır olunca aç.
- Varsayılan admin şifresini değiştir (`changeme`); üretimde `admin_users.password_hash` güncellenmeli.
- İletişim ve admin formlarında **CSRF** (oturum + meta etiket).
- Apache’de `.htaccess`, `*.sql` dosyalarının doğrudan indirilmesini engeller; nginx için örnek aşağıda `deny` kuralı var.

---

## 8. Yerel kurulum (kısa)

1. Projeyi web köküne veya sanal host **document root**’una kopyala ( `index.php`’nin olduğu klasör kök olsun).

2. Veritabanını oluştur ve içe aktar:

   ```bash
   mysql -u root -p < sql/portfolio_export.sql
   ```

   Alternatif: önce `portfolio_db` oluştur, `sql/schema.sql` uygula, sonra `sql/seed_admin.sql`.

3. `includes/config.php` dosyasını düzenle:

   - **`APP_BASE_URL`:** Site kökteyse `''`; alt klasördeyse `'/alt-klasor'` (sonunda `/` yok).
   - **`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`:** MySQL bilgileri.

4. Tarayıcıda siteyi aç.

**Varsayılan admin** (`portfolio_export.sql` veya `seed_admin.sql` kullandıysan):

- Adres: `/admin/login.php`
- Kullanıcı: `admin`
- Şifre: `changeme` → **Canlıya almadan önce mutlaka değiştir.**

---

## 9. Canlıya alma notları

### Sunucu

- PHP-FPM veya `mod_php`, MySQL, web sunucusu (Apache veya nginx).
- `php -m | grep pdo_mysql` ile PDO MySQL’in açık olduğunu doğrula.

### Veritabanı (örnek)

```bash
mysql -u YOUR_USER -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u YOUR_USER -p portfolio_db < sql/portfolio_export.sql
```

Gerçek şifreleri repoya koyma; `includes/config.php` sunucuda düzenlenir.

### Apache

- Document root = proje kökü.
- `.htaccess` için `AllowOverride All` (sıkıştırma / başlıklar / SQL koruması için).

### nginx (özet)

```nginx
root /var/www/portfolio;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;  # Ortamdaki PHP sürümüne göre yolu güncelle
}

location ~* \.(sql)$ {
    deny all;
}
```

### İzinler

- Dizinler genelde `755`, dosyalar `644`; web kullanıcısı tüm ağacı okuyabilmeli.
- `includes/config.php` dünya genelinde yazılabilir olmamalı.

### İsteğe bağlı performans

- Statik dosyalar için CDN; OPcache açık olsun.

---

## 10. Kontrol listesi

Dağıtım veya güncellemeden sonra kabaca şu sırayla doğrulayabilirsin:

1. **`/` (ana sayfa)** — Hero, vitrin, en az iki yayınlı proje varsa vitrin dışı kartların gelmesi, hikâye / yığın / alt CTA.
2. **`/api/health.php`** — JSON’da `"ok": true` ve veritabanı bağlıysa `"db": true`.
3. **`/projects.php`** — Önce iskelet, sonra kartlar; geliştirici araçlarında **`/api/projects.php`** isteği.
4. **`/contact.php`** — Gönderim ve başarı geri bildirimi (tercihen sayfa yenilenmeden).
5. **Dil** — EN ↔ TR; sabit metinler ve (bilinen id’ler için) proje özetlerinin TR yerelleştirmesi.
6. **`/admin/login.php`** — Giriş, proje ekle/düzenle/sil, public listede güncel içerik.

---

## 11. Lisans

Kendi portföyün için kullanıp değiştirebilirsin; garanti verilmez.
