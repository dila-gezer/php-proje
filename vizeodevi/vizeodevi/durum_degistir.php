<?php
/**
 * ============================================================================
 * PROJE: EduTask Pro - Öğrenci Görev ve Proje Yönetim Sistemi
 * DOSYA: durum_degistir.php
 * AÇIKLAMA: Tek tıkla hızlı görev durumu değiştirme işlemini POST yöntemiyle
 *           güvenli şekilde gerçekleştiren arka plan dosyası.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
oturumKontrol();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $yeniDurum = isset($_POST['yeni_durum']) ? trim($_POST['yeni_durum']) : '';

    if ($id > 0) {
        $basarili = gorevDurumDegistir($id, $yeniDurum);

        if ($basarili) {
            header("Location: index.php?mesaj=durum_guncellendi");
            exit;
        }
    }

    header("Location: index.php?mesaj=hata");
    exit;
} else {
    header("Location: index.php?mesaj=hata");
    exit;
}
