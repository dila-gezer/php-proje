<?php
/**
 * ============================================================================
 * PROJE: EduTask AI Studio - Akademik Görev ve Proje Yönetim Platformu
 * DOSYA: kayit.php
 * AÇIKLAMA: Yeni Öğrenci Kayıt (Register) Sayfası. POST yöntemiyle
 *           yeni kullanıcı oluşturur ve şifreyi password_hash() ile saklar.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/arayuz.php';

if (isset($_SESSION['kullanici']) && is_array($_SESSION['kullanici'])) {
    header("Location: index.php");
    exit;
}

$hatalar = [];
$form = [
    'ad_soyad' => '',
    'eposta'   => '',
    'bolum'    => 'Bilgisayar Programcılığı'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['ad_soyad'] = $_POST['ad_soyad'] ?? '';
    $form['eposta']   = $_POST['eposta'] ?? '';
    $form['bolum']    = $_POST['bolum'] ?? '';

    $sonuc = kullaniciKayitOl([
        'ad_soyad'     => $form['ad_soyad'],
        'eposta'       => $form['eposta'],
        'bolum'        => $form['bolum'],
        'sifre'        => $_POST['sifre'] ?? '',
        'sifre_tekrar' => $_POST['sifre_tekrar'] ?? ''
    ]);

    if ($sonuc['basarili']) {
        header("Location: index.php?mesaj=hosgeldin");
        exit;
    } else {
        $hatalar = $sonuc['hatalar'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php ortakHead('Hesap Oluştur'); ?>
</head>
<body>

    <?php ustBar('kayit', null); ?>

    <main class="container" style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 28px 24px;">

        <div style="width: 100%; max-width: 500px;">

            <div style="text-align: center; margin-bottom: 22px;">
                <div class="ai-pill-tag" style="margin-bottom: 10px;">
                    <span>Yeni Öğrenci Hesabı</span>
                </div>
                <h2 class="hero-title" style="font-size: 1.8rem;">EduTask Studio'ya Katılın</h2>
                <p style="color: var(--text-muted); font-size: 0.92rem; margin-top: 6px;">
                    Kendi öğrenci hesabınızı oluşturarak ders ve projelerinizi yönetmeye başlayın.
                </p>
            </div>

            <?php if (!empty($hatalar)): ?>
                <div class="alert alert-danger">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <strong>Lütfen Aşağıdaki Alanları Kontrol Ediniz:</strong>
                        <ul>
                            <?php foreach ($hatalar as $h): ?>
                                <li><?php echo guvenli($h); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="content-card" style="padding: 32px;">
                <form method="POST" action="kayit.php" novalidate>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="ad_soyad">Ad Soyad <span class="required-star">*</span></label>
                        <input type="text"
                               name="ad_soyad"
                               id="ad_soyad"
                               class="form-control"
                               placeholder="Örn: Ahmet Yılmaz"
                               value="<?php echo guvenli($form['ad_soyad']); ?>"
                               required>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="bolum">Bölüm / Program</label>
                        <input type="text"
                               name="bolum"
                               id="bolum"
                               class="form-control"
                               placeholder="Örn: Bilgisayar Programcılığı"
                               value="<?php echo guvenli($form['bolum']); ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="eposta">E-Posta Adresi <span class="required-star">*</span></label>
                        <input type="email"
                               name="eposta"
                               id="eposta"
                               class="form-control"
                               placeholder="ornek@universite.edu.tr"
                               value="<?php echo guvenli($form['eposta']); ?>"
                               required>
                    </div>

                    <div class="form-grid-2" style="margin-bottom: 24px;">
                        <div class="form-group">
                            <label for="sifre">Şifre (En az 6 karakter) <span class="required-star">*</span></label>
                            <input type="password"
                                   name="sifre"
                                   id="sifre"
                                   class="form-control"
                                   placeholder="••••••"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="sifre_tekrar">Şifre Tekrar <span class="required-star">*</span></label>
                            <input type="password"
                                   name="sifre_tekrar"
                                   id="sifre_tekrar"
                                   class="form-control"
                                   placeholder="••••••"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 13px; font-size: 0.95rem;">
                        <span>Hesabı Oluştur ve Başla</span>
                    </button>
                </form>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-subtle); text-align: center; font-size: 0.88rem; color: var(--text-muted);">
                    Zaten hesabınız var mı?
                    <a href="login.php" style="color: var(--brand-400); font-weight: 700; text-decoration: none; margin-left: 4px;">
                        Giriş Yapın →
                    </a>
                </div>
            </div>

        </div>

    </main>

    <script>
    </script>
    <?php temaBetigi(); ?>
</body>
</html>
