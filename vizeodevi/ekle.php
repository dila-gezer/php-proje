<?php
/**
 * ============================================================================
 * PROJE: EduTask Pro - Kurumsal Görev ve Proje Yönetim Sistemi
 * DOSYA: ekle.php
 * AÇIKLAMA: 2. HAFTA — Yeni görev kayıt formu ve PHP veri doğrulama.
 *           Form POST yöntemiyle gönderilir, veriler PHP tarafında denetlenir
 *           ve geçerli veriler $_SESSION yapısına eklenir.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/arayuz.php';

oturumKontrol();
$kullanici = aktifKullanici();

$hatalar = [];
$formGonderildi = false;

// Form değerlerini varsayılan olarak boş tanımlayalım
$formVerileri = [
    'baslik'        => '',
    'kategori'      => '',
    'ozel_kategori' => '',
    'oncelik'       => 'Orta',
    'durum'         => 'Bekliyor',
    'teslim_tarihi' => date('Y-m-d', strtotime('+7 days')),
    'aciklama'      => ''
];

// ZORUNLU KURAL: Veri gönderiminde POST yöntemi kullanılmalıdır.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formGonderildi = true;

    // Kullanıcının formdan gönderdiği değerleri alalım
    $formVerileri['baslik']        = $_POST['baslik'] ?? '';
    $formVerileri['kategori']      = $_POST['kategori'] ?? '';
    $formVerileri['ozel_kategori'] = $_POST['ozel_kategori'] ?? '';
    $formVerileri['oncelik']       = $_POST['oncelik'] ?? 'Orta';
    $formVerileri['durum']         = $_POST['durum'] ?? 'Bekliyor';
    $formVerileri['teslim_tarihi'] = $_POST['teslim_tarihi'] ?? '';
    $formVerileri['aciklama']      = $_POST['aciklama'] ?? '';

    // Eğer "Yeni / Diğer Ders" seçildiyse ve özel kategori yazıldıysa onu kullanalım
    $kullanilacakKategori = $formVerileri['kategori'];
    if ($formVerileri['kategori'] === 'DİĞER' && !empty(trim($formVerileri['ozel_kategori']))) {
        $kullanilacakKategori = trim($formVerileri['ozel_kategori']);
    }

    // Doğrulama ve ekleme için veri paketini hazırlayalım
    $eklenecekVeri = [
        'baslik'        => $formVerileri['baslik'],
        'kategori'      => $kullanilacakKategori,
        'oncelik'       => $formVerileri['oncelik'],
        'durum'         => $formVerileri['durum'],
        'teslim_tarihi' => $formVerileri['teslim_tarihi'],
        'aciklama'      => $formVerileri['aciklama']
    ];

    // Doğrulama ve ekleme fonksiyonunu çağırıyoruz
    $sonuc = gorevEkle($eklenecekVeri);

    if ($sonuc['basarili']) {
        // Başarılı ise ana sayfaya yönlendir (PRG - Post/Redirect/Get deseni)
        header("Location: index.php?mesaj=eklendi");
        exit;
    } else {
        // Hatalar varsa formun üstünde kullanıcıya gösterilecek
        $hatalar = $sonuc['hatalar'];
    }
}

// Kategori seçeneklerini alalım
$mevcutKategoriler = kategorileriGetir();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php ortakHead('Yeni Görev'); ?>
</head>
<body class="has-mobile-nav">

    <?php ustBar('ekle', $kullanici); ?>

    <main class="container" style="flex: 1; padding-top: 24px; padding-bottom: 36px;">

        <div class="form-container">

            <!-- Sayfa Başlığı ve 2. Hafta Bilgilendirme Rozeti -->
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <h2 style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                        Yeni Görev & Proje Kaydı
                    </h2>
                    <span class="badge badge-info" style="font-size: 0.82rem; padding: 6px 14px;">
                        2. Hafta: Form & POST Doğrulama
                    </span>
                </div>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 6px;">
                    Bu form <strong>POST yöntemi</strong> ile güvenli çalışır. Girdiler PHP tarafında denetlenir (Validasyon); doğrulamadan geçen kayıtlar <strong>$_SESSION</strong> oturumuna eklenir.
                </p>
            </div>

            <!-- ============================================================
                 ZORUNLU ÖZELLİK 3.4: VERİ DOĞRULAMA VE HATA MESAJLARI (PHP)
                 ============================================================ -->
            <?php if (!empty($hatalar)): ?>
                <div class="alert alert-danger">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong>Lütfen Form Hatalarını Düzeltiniz (PHP Doğrulama):</strong>
                        <ul>
                            <?php foreach ($hatalar as $hata): ?>
                                <li><?php echo guvenli($hata); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Hızlı Test Araç Çubuğu (Öğrenci Kolaylığı) -->
            <div style="display: flex; gap: 10px; margin-bottom: 16px; justify-content: flex-end; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="ornekVeriDoldur()" title="Hızlıca geçerli örnek bilgiler doldurur">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    Geçerli Örnek Doldur
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="formuTemizle()" title="Formu boşaltır (PHP hata testini çekmek için)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Formu Temizle (Boş Test)
                </button>
            </div>

            <!-- ============================================================
                 ZORUNLU ÖZELLİK 3.3: YENİ KAYIT EKLEME FORMU (POST YÖNTEMİ)
                 ============================================================ -->
            <div class="content-card" style="padding: 32px;">
                <form id="gorevFormu" method="POST" action="ekle.php" novalidate>

                    <!-- Görev Başlığı -->
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label for="baslik">
                            Görev / Ödev Başlığı <span class="required-star">*</span>
                        </label>
                        <input type="text" 
                               name="baslik" 
                               id="baslik" 
                               class="form-control" 
                               placeholder="Örn: PHP Proje Ödevi 2. Hafta Form İşlemleri" 
                               value="<?php echo guvenli($formVerileri['baslik']); ?>">
                        <div class="form-help">PHP Kontrolü: Bu alan boş bırakılamaz ve en az 3 karakter olmalıdır.</div>
                    </div>

                    <!-- 2 Sütunlu Alan: Ders/Kategori & Özel Kategori -->
                    <div class="form-grid-2" style="margin-bottom: 22px;">
                        <div class="form-group">
                            <label for="kategori">
                                Ders / Kategori <span class="required-star">*</span>
                            </label>
                            <select name="kategori" id="kategori" class="form-control" onchange="kategoriDegisti(this.value)">
                                <option value="">-- Lütfen Ders Seçiniz --</option>
                                <?php foreach ($mevcutKategoriler as $kat): ?>
                                    <option value="<?php echo guvenli($kat); ?>" <?php echo ($formVerileri['kategori'] === $kat) ? 'selected' : ''; ?>>
                                        <?php echo guvenli($kat); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="DİĞER" <?php echo ($formVerileri['kategori'] === 'DİĞER') ? 'selected' : ''; ?>>+ Farklı Bir Ders / Kategori Ekle</option>
                            </select>
                        </div>

                        <div class="form-group" id="ozelKategoriAlani" style="<?php echo ($formVerileri['kategori'] === 'DİĞER') ? 'display:block;' : 'display:none;'; ?>">
                            <label for="ozel_kategori">
                                Yeni Ders Adı Yazınız: <span class="required-star">*</span>
                            </label>
                            <input type="text" 
                                   name="ozel_kategori" 
                                   id="ozel_kategori" 
                                   class="form-control" 
                                   placeholder="Örn: Mobil Programlama"
                                   value="<?php echo guvenli($formVerileri['ozel_kategori']); ?>">
                        </div>
                    </div>

                    <!-- 3 Sütunlu Alan: Öncelik, Durum, Teslim Tarihi -->
                    <div class="form-grid-2" style="margin-bottom: 22px;">
                        <div class="form-group">
                            <label for="oncelik">Öncelik Derecesi <span class="required-star">*</span></label>
                            <select name="oncelik" id="oncelik" class="form-control">
                                <option value="Düşük" <?php echo ($formVerileri['oncelik'] === 'Düşük') ? 'selected' : ''; ?>>Düşük</option>
                                <option value="Orta" <?php echo ($formVerileri['oncelik'] === 'Orta') ? 'selected' : ''; ?>>Orta (Normal)</option>
                                <option value="Yüksek" <?php echo ($formVerileri['oncelik'] === 'Yüksek') ? 'selected' : ''; ?>>Yüksek (Acil)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="durum">Başlangıç Durumu <span class="required-star">*</span></label>
                            <select name="durum" id="durum" class="form-control">
                                <option value="Bekliyor" <?php echo ($formVerileri['durum'] === 'Bekliyor') ? 'selected' : ''; ?>>Bekliyor</option>
                                <option value="Devam Ediyor" <?php echo ($formVerileri['durum'] === 'Devam Ediyor') ? 'selected' : ''; ?>>Devam Ediyor</option>
                                <option value="Tamamlandı" <?php echo ($formVerileri['durum'] === 'Tamamlandı') ? 'selected' : ''; ?>>Tamamlandı</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 22px;">
                        <label for="teslim_tarihi">Teslim / Bitiş Tarihi</label>
                        <input type="date" 
                               name="teslim_tarihi" 
                               id="teslim_tarihi" 
                               class="form-control" 
                               value="<?php echo guvenli($formVerileri['teslim_tarihi']); ?>">
                    </div>

                    <!-- Açıklama Alanı -->
                    <div class="form-group" style="margin-bottom: 26px;">
                        <label for="aciklama">
                            Detaylı Açıklama / Görev Notları <span class="required-star">*</span>
                        </label>
                        <textarea name="aciklama" 
                                  id="aciklama" 
                                  class="form-control" 
                                  placeholder="Görevin detayları, teslim koşulları veya yapılması gereken adımlar..." 
                                  rows="4"><?php echo guvenli($formVerileri['aciklama']); ?></textarea>
                        <div class="form-help">PHP Kontrolü: Bu alan boş bırakılamaz ve en az 5 karakter girilmelidir.</div>
                    </div>

                    <!-- Form Aksiyon Butonları -->
                    <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end; padding-top: 14px; border-top: 1px solid var(--border-default);">
                        <a href="index.php" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Görevi Kaydet (POST)
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div>İnternet Programcılığı I — 2. Hafta: Form İşlemleri ve Veri Ekleme</div>
            <div><a href="index.php" style="color: var(--brand-600); font-weight: 600;">← Görev Paneline Dön</a></div>
        </div>
    </footer>

    <!-- JavaScript Yardımcıları -->
    <script>
        function kategoriDegisti(deger) {
            var ozelAlan = document.getElementById('ozelKategoriAlani');
            if (deger === 'DİĞER') {
                ozelAlan.style.display = 'block';
                document.getElementById('ozel_kategori').focus();
            } else {
                ozelAlan.style.display = 'none';
            }
        }

        function ornekVeriDoldur() {
            document.getElementById('baslik').value = 'İnternet Programcılığı I - 2. Hafta Form Ödevi';
            document.getElementById('kategori').value = 'İnternet Programcılığı I';
            document.getElementById('oncelik').value = 'Yüksek';
            document.getElementById('durum').value = 'Devam Ediyor';
            document.getElementById('aciklama').value = 'POST yöntemiyle veriler PHP tarafına iletildi, zorunlu alanlar doğrulandı ve $_SESSION ile oturum belleğine kaydedildi.';
            kategoriDegisti('İnternet Programcılığı I');
        }

        function formuTemizle() {
            document.getElementById('baslik').value = '';
            document.getElementById('kategori').value = '';
            document.getElementById('aciklama').value = '';
            kategoriDegisti('');
        }
    </script>
    <?php temaBetigi(); ?>
</body>
</html>
