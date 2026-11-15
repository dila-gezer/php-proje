# EduTask — Canlı Sunum, Mülakat ve Kod Açıklama Rehberi
**Öğrenciye Özel Hazırlık Notları ve Hoca Soru-Cevap Kılavuzu**

> Bu rehber, ödev yönergesinin **7. Maddesi (Kural 1 & 2)** ve **8. Maddesi (Canlı Gösterim ve Soru-Cevap - 20 Puan)** için özel olarak hazırlanmıştır. Hocanız projeyi incelerken veya sözlü mülakatta bu soruları soracaktır. Aşağıdaki açıklamaları bir kez dikkatlice okumanız tam puan almanız için yeterlidir.

---

## BÖLÜM 1: HOCANIN SORABİLECEĞİ 10 KRİTİK SORU VE CEVAPLARI

### Soru 1: "Projenizde PHP hangi işlemleri gerçekleştiriyor?"
**Cevabınız:**
> *"Hocam, projemizde PHP dinamik arka plan işlemlerinin tamamını yönetmektedir:*
> 1. *`session_start()` ile oturum yönetimini sağlar, sayfa yenilense bile verilerin kaybolmamasını temin eder.*
> 2. *`$_POST` ile formdan gelen verileri yakalar, boşluk ve karakter denetimlerini (validasyon) yapar.*
> 3. *`$_GET` ile URL'den gelen kategori ve durum filtre parametrelerini okuyup listeyi süzer.*
> 4. *`foreach` döngüleriyle dizilerdeki görevleri HTML tablo yapısına döker.*
> 5. *`htmlspecialchars()` ile kullanıcı girdilerini XSS saldırılarına karşı güvenli hale getirir.*
> 6. *İstatistik sayaçlarını dinamik olarak hesaplar ve `sil.php` üzerinden ID'ye göre kayıt silme işlemini icra eder."*

---

### Soru 2: "Bu formdan gelen veriler hangi yöntemle gönderiliyor? GET ve POST arasındaki fark nedir?"
**Cevabınız:**
> *"Hocam, kayıt ekleme (`ekle.php`) ve silme (`sil.php`) işlemlerinde kesinlikle **POST** yöntemi kullanılmıştır. Filtreleme aramalarında ise **GET** yöntemi tercih edilmiştir.*
> 
> ***Farkları şunlardır:***
> - ***GET Yöntemi:*** *Verileri URL adres çubuğu üzerinden (`index.php?kategori=Web&durum=Bekliyor`) taşır. Veri boyutu kısıtlıdır, tarayıcı geçmişinde saklanır ve yer imlerine eklenebilir. Bu nedenle arama ve filtreleme gibi yalnızca veri talep edilen işlemlerde kullanılır.*
> - ***POST Yöntemi:*** *Verileri HTTP isteğinin gövdesinde (body) gizli olarak taşır. URL'de görünmez, veri boyutu kısıtlaması yoktur ve daha güvenlidir. Yeni bir veri eklerken veya veritabanından bir şey silerken mutlaka POST kullanılmalıdır. Aksi halde tarayıcı önbelleğe alabilir veya linke her tıklandığında istemeden silme işlemi gerçekleşebilir (CSRF riski)."*

---

### Soru 3: "Neden $_SESSION (Oturum) kullandınız? Tarayıcıyı kapatırsak ne olur?"
**Cevabınız:**
> *"Hocam, ödevin bu ilk sürümünde (MVP) veritabanı kullanımı zorunlu tutulmadığı için verileri sunucu belleğinde geçici olarak saklamak amacıyla `$_SESSION` kullandık.*
> *Normalde PHP'de bir değişken sayfa çalıştığı anda yaşar ve sayfa bittiğinde bellekten silinir. `$_SESSION` ise kullanıcının oturumu boyunca (farklı sayfalar arasında dolaşırken veya sayfayı yenilediğinde) verilerin sunucuda korunmasını sağlar.*
> *Kullanıcı tarayıcısını tamamen kapattığında veya oturum süresi dolduğunda session sonlanır; sonraki haftalarda bunu MySQL veritabanına bağlayarak verilerin kalıcı olmasını sağlayacağız."*

---

### Soru 4: "`foreach` döngüsü ne işe yarıyor, kodunuzda nerede kullandınız?"
**Cevabınız:**
> *"Hocam, `foreach` PHP'de dizilerin (array) her bir elemanını sırayla tek tek gezmek için kullanılan en optimize döngüdür.*
> *Projemizde üç yerde kullandık:*
> 1. *`index.php` içerisinde: `$_SESSION['gorevler']` dizisindeki her bir görevi gezip tablo satırlarına (`<tr>...</tr>`) yazdırmak için.*
> 2. *`functions.php` -> `gorevleriGetir()` içinde: Kullanıcının seçtiği ders veya duruma uyan görevleri süzmek için.*
> 3. *`functions.php` -> `istatistikleriHesapla()` içinde: Kaç tane bekleyen, kaç tane tamamlanan görev olduğunu saymak için."*

---

### Soru 5: "Kayıt ekleme işlemi nasıl çalışıyor? Hatalı veri girilirse sistem ne yapıyor?"
**Cevabınız:**
> *"Hocam, `ekle.php` sayfasında kullanıcı formu doldurup Gönder'e bastığında:*
> 1. *İlk olarak `$_SERVER['REQUEST_METHOD'] === 'POST'` kontrolü yapılır.*
> 2. *Formdan gelen veriler `functions.php` içindeki `gorevEkle()` fonksiyonuna iletilir.*
> 3. *Fonksiyon; başlığın boş olup olmadığını ve en az 3 karakter girilip girilmediğini, açıklamanın en az 5 karakter olup olmadığını denetler.*
> 4. *Eğer bir kural ihlali varsa `$hatalar` dizisine eklenir ve formun üstünde kırmızı uyarı kutusuyla kullanıcıya gösterilir. Kullanıcının daha önce girdiği metinler kaybolmaz.*
> 5. *Hata yoksa, dizideki en büyük ID'nin bir fazlası alınarak yeni bir görev dizisi oluşturulur, `array_unshift()` ile session listesinin en başına eklenir ve `header("Location: index.php?mesaj=eklendi")` ile kullanıcı ana sayfaya yönlendirilir (PRG - Post/Redirect/Get deseni)."*

---

### Soru 6: "Kayıt silme işlemi nasıl gerçekleştiriliyor? Neden doğrudan link vermediniz?"
**Cevabınız:**
> *"Hocam, yönergemizin 5. maddesinde 'Kayıt ekleme ve silme işlemleri POST yöntemiyle yapılmalıdır' kuralı yer almaktadır.*
> *Doğrudan `<a href="sil.php?id=3">Sil</a>` şeklinde GET ile silme yapmak çok ciddi bir güvenlik açığıdır (Arama motoru botları veya önbellek mekanizmaları o linke tıkladığında kayıt silinir).*
> *Bu sebeple her görevin yanına mini bir HTML formu koyduk: `<form method="POST" action="sil.php">`.*
> *Form içinde `<input type="hidden" name="id" value="...">` ile silinecek kaydın ID'si gönderilir.*
> *Kullanıcı butona bastığında önce JavaScript `confirm()` kutusu ile onay sorulur. Onaylarsa `sil.php` gelen POST isteğini alır, `unset()` fonksiyonu ile oturum dizisinden o elemanı çıkartır ve `array_values()` ile dizi indekslerini yeniden düzenler."*

---

### Soru 7: "`htmlspecialchars()` fonksiyonunun görevi nedir? Kullanmazsak ne olur?"
**Cevabınız:**
> *"Hocam, `htmlspecialchars()` XSS (Cross-Site Scripting) adı verilen kod enjeksiyonu saldırılarını engellemek için kullanılır.*
> *Kullanıcı formdaki başlık veya açıklama alanına kötü niyetli bir `<script>alert('hack');</script>` kodu yazarsa, bu fonksiyon `<` işaretini `&lt;` sembolüne, `>` işaretini `&gt;` sembolüne dönüştürür.*
> *Böylece tarayıcı bunu çalıştırılabilir bir JavaScript kodu olarak değil, masum düz bir metin olarak algılar ve ekrana olduğu gibi yansıtır. Sistemimizin güvenliği için tüm kullanıcı çıktılarını `guvenli()` yardımcı fonksiyonumuz üzerinden `htmlspecialchars()` ile ekrana basıyoruz."*

---

### Soru 8: "Projenizde hangi kullanıcı tanımlı fonksiyonlar var?"
**Cevabınız:**
> *"Hocam, ödevde en az 2 fonksiyon isteniyordu ancak kodun temiz ve modüler olması için `functions.php` içerisinde 9 adet fonksiyon geliştirdim:*
> 1. *`sistemiBaslat()`: İlk girişte 6 adet örnek ders kaydını session'a yükler.*
> 2. *`guvenli($veri)`: XSS koruması için `htmlspecialchars` süzgecinden geçirir.*
> 3. *`gorevleriGetir($kategori, $durum)`: Filtrelere göre görev listesini döndürür.*
> 4. *`gorevEkle($veri)`: Form verilerini doğrular (validasyon) ve oturuma kaydeder.*
> 5. *`gorevSil($id)`: İlgili ID'li görevi diziden siler.*
> 6. *`istatistikleriHesapla()`: Toplam, bekleyen, tamamlanan ve acil sayaçlarını çıkarır.*
> 7. *`kategorileriGetir()`: Mevcut dersleri benzersiz olarak sıralar.*
> 8. *`durumRozeti($durum)` & `oncelikRozeti($oncelik)`: Duruma uygun renkli rozet HTML'i basar.*
> 9. *`tarihFormati($tarih)`: Tarihleri GG.AA.YYYY formatına çevirir."*

---

## BÖLÜM 2: HOCA DERSTE "KODDA ŞU DEĞİŞİKLİĞİ YAP" DERSE NE YAPACAKSINIZ?

Ödev yönergesinin **7. Maddesi Kural 2** gereğince hoca canlıda sizden küçük bir değişiklik isteyebilir. İşte en muhtemel senaryolar ve çözümleri:

### Senaryo A: "Göreve yeni bir alan ekle (Örn: 'Puan/Ağırlık' veya 'Öğretim Görevlisi')"
Diyelim ki hoca: *"Her göreve bir de 'Öğretim Görevlisi' alanı ekle"* dedi.
Yapılacak 3 basit adım:
1. **`ekle.php` açın:** Formun içine yeni bir `<div class="form-group">` ekleyin:
   ```html
   <div class="form-group">
       <label for="hoca">Öğretim Görevlisi:</label>
       <input type="text" name="hoca" id="hoca" class="form-control" required>
   </div>
   ```
2. **`functions.php` açın:** `gorevEkle()` fonksiyonunda gelen POST verisini diziye dahil edin:
   ```php
   $yeniGorev = [
       // ... diğer alanlar ...
       'hoca' => trim($veri['hoca'] ?? 'Belirtilmedi'),
   ];
   ```
3. **`index.php` açın:** Tablonun `<thead>` kısmına `<th>Öğretim Görevlisi</th>`, `<tbody>` kısmına da `<td><?php echo guvenli($gorev['hoca']); ?></td>` ekleyin. Kaydedip sayfayı yenileyin. Bitti!

---

### Senaryo B: "Yeni bir durum ekle (Örn: 'İptal Edildi')"
Hoca: *"Bekliyor, Devam Ediyor, Tamamlandı'nın yanına bir de 'İptal Edildi' ekle"* derse:
1. **`functions.php` açın:**
   - `gorevEkle()` içindeki `$gecerliDurumlar` dizisine `'İptal Edildi'` elemanını ekleyin.
   - `durumRozeti()` fonksiyonundaki switch-case yapısına şunu ekleyin:
     ```php
     case 'İptal Edildi':
         return '<span class="badge badge-danger">İptal Edildi</span>';
     ```
2. **`ekle.php` ve `index.php` açın:** Durum `<select>` menülerine bir `<option value="İptal Edildi">İptal Edildi</option>` ekleyin. Bitti!

---

### Senaryo C: "Kayıtların sıralamasını tersine çevir (En eski en üstte olsun)"
Hoca: *"Yeni eklenenler en altta çıksın"* derse:
- **`functions.php` açın:** `gorevEkle()` fonksiyonundaki `array_unshift($_SESSION['gorevler'], $yeniGorev);` satırını `$_SESSION['gorevler'][] = $yeniGorev;` (veya `array_push`) olarak değiştirin.

---

### Senaryo D: "Açıklama alanının minimum karakter kuralını 10 yap"
- **`functions.php` açın:** `gorevEkle()` fonksiyonunda `mb_strlen($aciklama, 'UTF-8') < 5` yazan yeri `10` yapın ve hata mesajındaki metni "en az 10 karakter" olarak güncelleyin.
