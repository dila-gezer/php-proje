<?php
/**
 * ============================================================================
 * PROJE: EduTask - Öğrenci Görev ve Proje Takip Sistemi
 * DOSYA: sil.php
 * AÇIKLAMA: Görev silme işlemini yürüten arka plan PHP dosyası.
 * ============================================================================
 * TEKNİK KURAL (Madde 5):
 * "Kayıt ekleme ve silme işlemleri POST yöntemiyle yapılmalıdır."
 * Güvenlik gerekçesiyle GET ile yapılan silme talepleri reddedilir (CSRF koruması).
 */

require_once __DIR__ . '/functions.php';
oturumKontrol();

// İstek yöntemi POST mu kontrol ediyoruz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // POST ile gelen ID bilgisini tam sayı (int) olarak alalım
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        // functions.php içerisindeki silme fonksiyonunu çalıştırıyoruz
        $basarili = gorevSil($id);

        if ($basarili) {
            // Başarıyla silindi, ana sayfaya başarı mesajıyla yönlendir
            header("Location: index.php?mesaj=silindi");
            exit;
        }
    }

    // ID bulunamadıysa veya silinemezse hata mesajı ile dön
    header("Location: index.php?mesaj=hata");
    exit;

} else {
    // POST dışında bir yöntemle (örneğin doğrudan URL'den GET ile) gelinirse
    // güvenlik için işlem yapmadan ana sayfaya yönlendiriyoruz.
    header("Location: index.php?mesaj=hata");
    exit;
}
