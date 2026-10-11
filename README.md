# Güzellik Salonu / Kuaför PHP Web Script

MySQL tabanlı, PHP (PDO) ile yazılmış, erişilebilirlik (WCAG 2.1 AA odaklı) ve SEO uyumlu kuaför / güzellik salonu scripti.

## Özellikler
- Ana sayfa, hizmetler ve fiyatlar, kampanyalar, ekip, online randevu, iletişim formu
- WhatsApp ile iletişim ve WhatsApp ile randevu al butonları
- Sosyal medya hesapları (Instagram, Facebook, X, YouTube, TikTok) – yönetim panelinden eklenir
- Yönetim paneli: randevular, hizmetler, personel, kampanyalar, mesajlar, ayarlar
- Yönetim paneli adresi Ayarlar sayfasından değiştirilebilir (varsayılan `/yonetim/`, Apache `mod_rewrite` gerekir)
- SEO: meta description, canonical, Open Graph, JSON-LD (BeautySalon), `sitemap.xml`, `robots.txt`; yönetim sayfaları `noindex`
- Erişilebilirlik: `lang="tr"`, “ana içeriğe geç” bağlantısı, anlamsal HTML, etiketli formlar, hata özeti (`role="alert"`), `aria-current`, görünür odak, yüksek kontrast, 44px dokunma alanı, azaltılmış hareket desteği
- Güvenlik: PDO hazır sorgular, CSRF jetonu, `password_hash`, çıktı kaçışlama

## Kurulum
1. MySQL'de veritabanı oluşturun ve `database/schema.sql` dosyasını **bir kez** içe aktarın.
2. `config/config.php` içindeki bağlantı bilgilerini düzenleyin.
3. Yönetici oluşturun: `php create_admin.php kullanici parola`
4. Siteyi Apache + PHP 8 ile yayınlayın; `https://alanadi/yonetim/` adresinden giriş yapın.
5. Ayarlar sayfasından site bilgileri, WhatsApp numarası, sosyal medya ve site adresini girin.
