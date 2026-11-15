<?php
/**
 * ============================================================================
 * PROJE: EduTask AI Studio - Akademik Görev ve Proje Yönetim Platformu
 * DOSYA: login.php
 * AÇIKLAMA: Kullanıcı Giriş (Authentication) Sayfası. POST yöntemiyle
 *           e-posta ve şifre doğrulaması yapar.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/arayuz.php';

// Zaten giriş yapmışsa doğrudan ana sayfaya yönlendir
if (isset($_SESSION['kullanici']) && is_array($_SESSION['kullanici'])) {
    header("Location: index.php");
    exit;
}

$hataMesaji    = '';
$bildirimMesaj = isset($_GET['mesaj']) ? guvenli($_GET['mesaj']) : '';
$epostaDegeri  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $epostaDegeri = $_POST['eposta'] ?? '';
    $sifreDegeri  = $_POST['sifre'] ?? '';

    $sonuc = kullaniciGirisYap($epostaDegeri, $sifreDegeri, isset($_POST['hatirla']));

    if ($sonuc['basarili']) {
        header("Location: index.php?mesaj=giris_basarili");
        exit;
    } else {
        $hataMesaji = $sonuc['hata'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php ortakHead('Giriş Yap'); ?>
</head>
<body>

    <?php ustBar('giris', null); ?>

    <main class="container" style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 32px 24px;">

        <div style="width: 100%; max-width: 460px;">

            <!-- Başlık Alanı -->
            <div style="text-align: center; margin-bottom: 24px;">
                <div class="ai-pill-tag" style="margin-bottom: 10px;">
                    <span>Öğrenci Oturum Yönetimi</span>
                </div>
                <h2 class="hero-title" style="font-size: 1.85rem;">Tekrar Hoş Geldiniz</h2>
                <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 6px;">
                    Akademik görev ve proje çalışma alanınıza erişmek için giriş yapın.
                </p>
            </div>

            <!-- Çıkış Bildirimi -->
            <?php if ($bildirimMesaj === 'cikis_yapildi'): ?>
                <div class="alert alert-info">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <div>Oturumunuz güvenli bir şekilde kapatıldı.</div>
                </div>
            <?php endif; ?>

            <!-- Hata Bildirimi -->
            <?php if (!empty($hataMesaji)): ?>
                <div class="alert alert-danger">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div><strong>Giriş Hatası:</strong> <?php echo guvenli($hataMesaji); ?></div>
                </div>
            <?php endif; ?>

            <!-- Giriş Kartı -->
            <div class="content-card" style="padding: 32px; margin-bottom: 18px;">

                <!-- Hızlı Demo Giriş Kutusu -->
                <div style="background: var(--brand-50); border: 1px dashed var(--brand-200); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">
                        <strong style="color: var(--text-primary); display: block;">Hazır Demo Öğrenci Hesabı:</strong>
                        <code>ogrenci@edutask.com</code> / Şifre: <code>123456</code>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="demoDoldur()">
                        Tek Tıkla Doldur
                    </button>
                </div>

                <form method="POST" action="login.php" novalidate>
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="eposta">Öğrenci E-Posta Adresi <span class="required-star">*</span></label>
                        <input type="email"
                               name="eposta"
                               id="eposta"
                               class="form-control"
                               placeholder="ogrenci@edutask.com"
                               value="<?php echo guvenli($epostaDegeri); ?>"
                               required>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label for="sifre">Şifre <span class="required-star">*</span></label>
                        <input type="password"
                               name="sifre"
                               id="sifre"
                               class="form-control"
                               placeholder="••••••••"
                               required>
                    </div>

                    <label class="check-row" for="hatirla">
                        <input type="checkbox" name="hatirla" id="hatirla" value="1" checked>
                        <span>Beni hatırla <small>(30 gün boyunca tekrar giriş istemez)</small></span>
                    </label>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 13px; font-size: 0.95rem;">
                        <span>Çalışma Alanına Giriş Yap</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>

                <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--border-subtle); text-align: center; font-size: 0.88rem; color: var(--text-muted);">
                    Henüz hesabınız yok mu?
                    <a href="kayit.php" style="color: var(--brand-400); font-weight: 700; text-decoration: none; margin-left: 4px;">
                        Yeni Öğrenci Kaydı Oluşturun →
                    </a>
                </div>
            </div>

        </div>

    </main>

    <script>
        function demoDoldur() {
            document.getElementById('eposta').value = 'ogrenci@edutask.com';
            document.getElementById('sifre').value = '123456';
        }
    </script>
    <?php temaBetigi(); ?>
</body>
</html>
