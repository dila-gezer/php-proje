# EduTask - Öğrenci Görev ve Proje Takip Sistemi
**İnternet Programcılığı I — 3 Haftalık Bireysel PHP Proje Ödevi (MVP Sürümü)**

---

## 1. Projenin Adı ve Amacı

### Proje Adı
**EduTask — Öğrenci Görev ve Proje Takip Sistemi**

### Projenin Amacı
Üniversite öğrencilerinin dönem boyunca aldıkları derslere ait ödev, proje, sunum, rapor ve sınav hazırlığı gibi akademik görevlerini tek bir merkezden planlamalarını, takip etmelerini ve durumlarını yönetmelerini sağlayan dinamik bir PHP web uygulamasıdır.

Bu ilk aşamada (MVP - Minimum Viable Product); saf PHP, HTML5 ve CSS kullanılarak; veritabanı gereksinimi olmadan `$_SESSION` (oturum) mekanizması üzerinde çalışan dinamik, güvenli ve kullanıcı dostu bir kayıt-takip platformu inşa edilmiştir.

---

## 2. Projede Bulunan Özellikler

Ödev yönergesinde belirtilen tüm zorunlu teknik kriterler eksiksiz olarak karşılanmıştır:

| Kriter / Özellik | Açıklama / Nasıl Gerçekleştirildi? |
| :--- | :--- |
| **3.1. Ana Sayfa & Kolay Gezinti** | Uygulama adı, açıklaması, istatistik paneli, arama/filtre çubuğu ve görev tablosunu içeren modern arayüz (`index.php`). |
| **3.2. Kayıt Listeleme** | PHP `foreach` döngüsü kullanılarak her kayıt için **en az 6 bilgi alanı** (ID, Başlık, Kategori/Ders, Açıklama, Öncelik Rozeti, Durum Rozeti, Teslim Tarihi) listelenmektedir. Başlangıçta tanımlı **6 örnek kayıt** mevcuttur. |
| **3.3. Yeni Kayıt Ekleme** | `ekle.php` üzerinde HTML formu hazırlanmış, veriler güvenli bir şekilde **POST yöntemi** ile alınıp oturuma eklenmektedir. Eklenen kayıt anında listenin başında görüntülenir (PRG Deseni). |
| **3.4. Veri Doğrulama (Validation)** | Formdan gelen veriler PHP tarafında denetlenir: Boş alan kontrolü, minimum karakter uzunlukları (başlık min 3, açıklama min 5 karakter) ve değer aralığı denetimi yapılır. Hatalar kullanıcıya görsel uyarı kutusuyla aktarılır. |
| **3.5. Kayıt Silme** | Güvenlik kuralı gereğince silme işlemi doğrudan linkle (GET) değil, **POST yöntemi** ve gizli ID alanı ile `sil.php` üzerinden gerçekleştirilir. Silme öncesinde kullanıcıdan JavaScript onay penceresi (`confirm`) alınır. |
| **3.6. Kayıt Filtreleme** | **GET yöntemi** kullanılarak ders/kategori ve görev durumuna göre dinamik filtreleme yapılır. İstenildiğinde "Temizle" butonu ile filtreler sıfırlanabilir. |
| **3.7. Özet Bilgiler & Sayaçlar** | Sayfanın üst kısmında dinamik sayaç kartları yer alır: Toplam Görev, Bekleyen, Devam Eden, Tamamlanan ve Yüksek Öncelikli (Acil) görev sayıları anlık hesaplanır. |
| **3.8. Güvenli Çıktı (XSS Koruması)** | Kullanıcıdan gelen tüm girdiler ekrana yazdırılmadan önce `htmlspecialchars()` fonksiyonundan geçirilerek script enjeksiyonu ve XSS saldırılarına karşı korunmuştur. |
| **Kullanıcı Tanımlı Fonksiyonlar** | `functions.php` içerisinde 9 adet modüler ve anlamlı fonksiyon (`sistemiBaslat`, `guvenli`, `gorevleriGetir`, `gorevEkle`, `gorevSil`, `istatistikleriHesapla`, `kategorileriGetir`, `durumRozeti`, `tarihFormati`) yazılmıştır. |

---

## 3. Klasör ve Dosya Yapısı

Proje, ödev kılavuzunda önerilen modüler ve sade yapıya sadık kalınarak geliştirilmiştir:

```text
ogrenci_gorev_takip/
│
├── index.php             # Ana kontrol paneli: sayaçlar, filtreleme formu ve görev listesi
├── ekle.php              # Yeni görev ekleme formu ve veri doğrulama mantığı
├── sil.php               # POST yöntemiyle güvenli kayıt silme işlemini yapan dosya
├── functions.php         # Veri yapısı, oturum yönetimi, doğrulama ve yardımcı fonksiyonlar
│
├── assets/
│   └── style.css         # Harici framework gerektirmeyen, %100 saf ve mobil uyumlu modern CSS
│
├── screenshots/          # Ödev teslimi için uygulamanın ekran görüntüleri
│   ├── 01_ana_sayfa_ve_listeleme.png
│   ├── 02_yeni_kayit_ekleme.png
│   └── 03_filtreleme_ve_silme.png
│
├── MULAKAT_REHBERI.md    # Canlı gösterim ve hoca soru-cevap mülakatı hazırlık rehberi
└── README.md             # Proje tanıtım, kurulum ve yapay zekâ kullanım belgesi
```

---

## 4. XAMPP Üzerinde Projeyi Çalıştırma Adımları

Projeyi yerel bilgisayarınızda çalıştırmak için aşağıdaki adımları sırasıyla uygulayınız:

1. **XAMPP Kurulumu:** Bilgisayarınızda XAMPP kurulu değilse [apachefriends.org](https://www.apachefriends.org/) adresinden indirip kurunuz.
2. **Proje Klasörünün Kopyalanması:**
   - İndirdiğiniz veya hazırlanan `ogrenci_gorev_takip` klasörünü XAMPP'ın web kök dizini olan `C:\xampp\htdocs\` klasörünün içine kopyalayınız.
   - Dosya yolu şu şekilde olmalıdır: `C:\xampp\htdocs\ogrenci_gorev_takip\`
3. **Apache Sunucusunu Başlatma:**
   - **XAMPP Control Panel** uygulamasını açınız.
   - **Apache** modülünün yanındaki **"Start"** butonuna tıklayınız (Yeşil yandığında sunucu hazırdır).
   - *(Not: Bu aşamada veritabanı oturum üzerinden çalıştığı için MySQL modülünü başlatmak zorunlu değildir).*
4. **Tarayıcıda Çalıştırma:**
   - Web tarayıcınızı (Chrome, Edge, Firefox vb.) açınız.
   - Adres çubuğuna şu adresi yazıp Enter tuşuna basınız:
     ```text
     http://localhost/ogrenci_gorev_takip/
     ```
5. **Alternatif Hızlı Çalıştırma (PHP Built-in Server):**
   - Komut satırından (Terminal/PowerShell) proje klasörüne gidip şu komutu vererek de çalıştırabilirsiniz:
     ```bash
     php -S localhost:8000
     ```
   - Ardından tarayıcınızdan `http://localhost:8000` adresine gidebilirsiniz.

---

## 5. Yapay Zekâ Kullanım Açıklaması

Ödev kılavuzunun **7. Maddesi (Yapay Zekâ Kullanımı ve Akademik Sorumluluk)** gereğince hazırlanan beyandır:

* **Kullanılan Yapay Zekâ Araçları:** Google Gemini (Antigravity AI Asistanı).
* **Kullanım Amaçları:**
  - Modern, modüler ve temiz bir PHP dosya mimarisi planlama.
  - XSS güvenliği (`htmlspecialchars`), POST tabanlı veri silme ve PRG (Post/Redirect/Get) tasarım kalıbının entegrasyonu.
  - Harici CSS kütüphanelerine (Bootstrap/Tailwind) ihtiyaç duymayan, tamamen saf CSS ile responsive ve estetik bir gösterge paneli (dashboard) arayüz tasarımı hazırlama.
  - Kod bloklarının Türkçe açıklamalarla dokümante edilmesi ve sözlü mülakat hazırlık sorularının derlenmesi.
* **Kullanılan Örnek İstem (Prompt):**
  > *"İnternet Programcılığı I dersi için PHP ile oturum (session) tabanlı, güvenli, POST ile kayıt ekleyip silebilen, GET ile filtreleme yapabilen, sayaçları olan ve saf CSS ile çalışan Öğrenci Görev Takip Sistemi projesi hazırlayalım. Kodlar anlaşılır açıklamalar içermeli ve hoca canlı gösterimde soru sorduğunda rahatça açıklayabileceğim bir yapıda olmalı."*
* **Öğrencinin Bireysel Katkısı:**
  - Proje konusunun ve veri modeli alanlarının (Ödev Başlığı, Ders Adı, Öncelik, Durum, Teslim Tarihi, Açıklama) belirlenmesi.
  - Form alanlarının mantıksal sıralamasının ve kullanıcı deneyimi akışının düzenlenmesi.
  - Canlı sunum öncesi tüm kod bloklarının satır satır incelenmesi, test edilmesi ve mülakat sorularına hazırlanılması.

---

## 6. Projede Varsa Bilinen Eksikler ve İlerleyen Haftalarda Eklenecek Özellikler

Yönergenin **11. Maddesinde** belirtildiği üzere bu proje ilk sürümdür (MVP). Sonraki haftalarda eklenecek yol haritası:

1. **MySQL Veritabanı ve PDO Entegrasyonu:** Şu an `$_SESSION` ile tutulan görev kayıtları, sonraki haftalarda MySQL `gorevler` tablosuna taşınacak ve PDO ile kalıcı hale getirilecektir.
2. **Kayıt Güncelleme / Düzenleme (Edit):** Mevcut görevlerin başlık, durum ve açıklamalarını sonradan düzenleyebilmek için `duzenle.php` sayfası eklenecektir.
3. **Kullanıcı Girişi ve Yetkilendirme (Auth):** Öğrenci giriş ve kayıt sistemi (login/register) ile her öğrencinin yalnızca kendi görevlerini görmesi sağlanacaktır.
4. **Metin Arama Çubuğu (Search):** Görev başlığı veya açıklamasında anahtar kelime ile canlı arama özelliği entegre edilecektir.

---

## 7. Ekran Görüntüleri Alanı

Ödev tesliminde ZIP dosyasına eklenecek ekran görüntüleri:
1. `screenshots/01_ana_sayfa_ve_listeleme.png` — Sayaçlar, filtreleme alanı ve görev tablosunun genel görünümü.
2. `screenshots/02_yeni_kayit_ekleme.png` — Yeni görev ekleme formu ve doğrulama hata mesajı örneği.
3. `screenshots/03_filtreleme_ve_silme.png` — Kategoriye göre filtrelenmiş liste ve kayıt silme onayı.

---
**Geliştirici:** Öğrenci Adı Soyadı  
**Öğrenci Numarası:** XXXXXXXXXX  
**Ders:** İnternet Programcılığı I  
**Tarih:** Kasım 2026
