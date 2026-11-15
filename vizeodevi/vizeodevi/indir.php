<?php
/**
 * ============================================================================
 * PROJE: EduTask Pro - Öğrenci Görev ve Proje Yönetim Sistemi
 * DOSYA: indir.php
 * AÇIKLAMA: Görev listesini Excel uyumlu CSV (.csv) veya JSON (.json)
 *           formatında yerel bilgisayara dışa aktaran (export) modül.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
oturumKontrol();

$format         = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'csv';
$seciliKategori = isset($_GET['kategori']) ? guvenli($_GET['kategori']) : '';
$seciliDurum    = isset($_GET['durum']) ? guvenli($_GET['durum']) : '';
$seciliOncelik  = isset($_GET['oncelik']) ? guvenli($_GET['oncelik']) : '';
$aramaMetni     = isset($_GET['arama']) ? guvenli($_GET['arama']) : '';
$siralama       = isset($_GET['siralama']) ? guvenli($_GET['siralama']) : '';

// Mevcut filtrelere göre görevleri alalım
$gorevler = gorevleriGetir($seciliKategori, $seciliDurum, $seciliOncelik, $aramaMetni, $siralama);
$tarihDamgasi = date('Y-m-d_H-i');

// Önceden açılmış çıktı tamponlarını temizle (boş/bozuk dosya oluşmasını önler)
while (ob_get_level() > 0) {
    ob_end_clean();
}

/**
 * Dosyayı tarayıcıya eksiksiz gönderir (Content-Length ile).
 */
function dosyaGonder($icerik, $mimeTuru, $dosyaAdi) {
    header('Content-Type: ' . $mimeTuru);
    header('Content-Disposition: attachment; filename="' . $dosyaAdi . '"');
    header('Content-Length: ' . strlen($icerik));
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    echo $icerik;
    exit;
}

if ($format === 'json') {
    $json = json_encode($gorevler, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    dosyaGonder($json, 'application/json; charset=utf-8', 'edutask_gorevler_' . $tarihDamgasi . '.json');
}

// Varsayılan: Türkçe Microsoft Excel uyumlu CSV (UTF-8 BOM + Noktalı Virgül Ayırıcı)
// CSV önce bellekte oluşturulur, sonra tek seferde gönderilir.
$bellek = fopen('php://temp', 'w+');

// Türkçe karakterlerin Excel'de bozulmaması için UTF-8 BOM ekliyoruz
fwrite($bellek, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Sütun Başlıkları
fputcsv($bellek, [
    'ID',
    'Görev Başlığı',
    'Ders / Kategori',
    'Öncelik',
    'Durum',
    'Teslim Tarihi',
    'Açıklama'
], ';');

// Satır Verileri
foreach ($gorevler as $g) {
    fputcsv($bellek, [
        $g['id'] ?? '',
        $g['baslik'] ?? '',
        $g['kategori'] ?? '',
        $g['oncelik'] ?? '',
        $g['durum'] ?? '',
        tarihFormati($g['teslim_tarihi'] ?? ''),
        $g['aciklama'] ?? ''
    ], ';');
}

rewind($bellek);
$csv = stream_get_contents($bellek);
fclose($bellek);

dosyaGonder($csv, 'text/csv; charset=utf-8', 'edutask_gorevler_' . $tarihDamgasi . '.csv');
