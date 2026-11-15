<?php
/**
 * ============================================================================
 * PROJE: EduTask Workspace - Öğrenci Görev ve Proje Yönetim Sistemi
 * DOSYA: index.php
 * AÇIKLAMA: "Pro Workspace" esintili kontrol paneli. Tıklanabilir KPI kartları,
 *           gün bazlı teslim grafiği, haftalık zaman çizelgesi, filtreleme,
 *           görev listesi ve görev detay modalı.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/arayuz.php';

// Oturum kontrolü (Giriş yapılmamışsa login.php'ye yönlendirir)
oturumKontrol();
$kullanici = aktifKullanici();

// GET yöntemiyle gelen filtreleme, arama ve sıralama parametrelerini alıyoruz
$seciliKategori = isset($_GET['kategori']) ? guvenli($_GET['kategori']) : '';
$seciliDurum    = isset($_GET['durum']) ? guvenli($_GET['durum']) : '';
$seciliOncelik  = isset($_GET['oncelik']) ? guvenli($_GET['oncelik']) : '';
$aramaMetni     = isset($_GET['arama']) ? guvenli($_GET['arama']) : '';
$siralama       = isset($_GET['siralama']) ? guvenli($_GET['siralama']) : '';
$bildirimMesaj  = isset($_GET['mesaj']) ? guvenli($_GET['mesaj']) : '';
$haftaOfset     = isset($_GET['hafta']) ? max(-52, min(52, (int)$_GET['hafta'])) : 0;

// Demo verilerini sıfırlamak istenirse (hem $_SESSION hem data/gorevler.json sıfırlanır)
if (isset($_GET['sifirla']) && $_GET['sifirla'] === '1') {
    verileriSifirla();
    header("Location: index.php?mesaj=sifirlandi");
    exit;
}

// İstatistik sayaçlarını hesaplayan fonksiyonu çağırıyoruz
$istatistik = istatistikleriHesapla();

// Filtrelere, arama metnine ve sıralamaya göre görev listesini getiriyoruz
$gorevler = gorevleriGetir($seciliKategori, $seciliDurum, $seciliOncelik, $aramaMetni, $siralama);

// Filtre seçenekleri için mevcut kategorileri alıyoruz
$kategoriler = kategorileriGetir();

// Yardımcı link oluşturma fonksiyonu (mevcut filtreleri korur)
function urlOlustur($kategori = null, $durum = null, $oncelik = null, $arama = null, $sira = null, $hafta = null) {
    global $seciliKategori, $seciliDurum, $seciliOncelik, $aramaMetni, $siralama, $haftaOfset;

    $params = [];
    $kat = ($kategori !== null) ? $kategori : $seciliKategori;
    $dur = ($durum !== null) ? $durum : $seciliDurum;
    $onc = ($oncelik !== null) ? $oncelik : $seciliOncelik;
    $ara = ($arama !== null) ? $arama : $aramaMetni;
    $sir = ($sira !== null) ? $sira : $siralama;
    $hft = ($hafta !== null) ? $hafta : $haftaOfset;

    if (!empty($kat) && $kat !== 'Tümü') $params['kategori'] = $kat;
    if (!empty($dur) && $dur !== 'Tümü') $params['durum'] = $dur;
    if (!empty($onc) && $onc !== 'Tümü') $params['oncelik'] = $onc;
    if (!empty($ara)) $params['arama'] = $ara;
    if (!empty($sir) && $sir !== 'varsayilan') $params['siralama'] = $sir;
    if (!empty($hft)) $params['hafta'] = (int)$hft;

    return empty($params) ? 'index.php' : 'index.php?' . http_build_query($params);
}

// İndirme (Export) URL'i için mevcut filtreleri koruyan yardımcı fonksiyon
function indirUrlOlustur($format = 'csv') {
    global $seciliKategori, $seciliDurum, $seciliOncelik, $aramaMetni, $siralama;
    $params = ['format' => $format];
    if (!empty($seciliKategori) && $seciliKategori !== 'Tümü') $params['kategori'] = $seciliKategori;
    if (!empty($seciliDurum) && $seciliDurum !== 'Tümü') $params['durum'] = $seciliDurum;
    if (!empty($seciliOncelik) && $seciliOncelik !== 'Tümü') $params['oncelik'] = $seciliOncelik;
    if (!empty($aramaMetni)) $params['arama'] = $aramaMetni;
    if (!empty($siralama) && $siralama !== 'varsayilan') $params['siralama'] = $siralama;
    return 'indir.php?' . http_build_query($params);
}

// Aktif filtre başlığını belirleyelim
$filtreBasligi = 'Tüm Görevler';
if (!empty($seciliDurum) && $seciliDurum !== 'Tümü') {
    $filtreBasligi = $seciliDurum . ' Görevler';
} elseif (!empty($seciliOncelik) && $seciliOncelik !== 'Tümü') {
    $filtreBasligi = $seciliOncelik . ' Öncelikli Görevler';
}
if (!empty($seciliKategori) && $seciliKategori !== 'Tümü') {
    $filtreBasligi .= ' · ' . $seciliKategori;
}
if (!empty($aramaMetni)) {
    $filtreBasligi .= ' · "' . $aramaMetni . '"';
}

$filtreAktifMi = (!empty($seciliKategori) && $seciliKategori !== 'Tümü') ||
                 (!empty($seciliDurum) && $seciliDurum !== 'Tümü') ||
                 (!empty($seciliOncelik) && $seciliOncelik !== 'Tümü') ||
                 (!empty($aramaMetni)) ||
                 (!empty($siralama) && $siralama !== 'varsayilan');

/* ----------------------------------------------------------------------------
 * HAFTALIK ZAMAN ÇİZELGESİ VE GRAFİK VERİLERİ
 * -------------------------------------------------------------------------- */
$gunAdlari   = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
$gunKisa     = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];
$ayKisa      = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];

$bugun        = strtotime('today');
$buPazartesi  = strtotime('-' . (date('N', $bugun) - 1) . ' days', $bugun);
$haftaBasi    = strtotime(($haftaOfset >= 0 ? '+' : '') . $haftaOfset . ' week', $buPazartesi);
$haftaSonu    = strtotime('+6 days', $haftaBasi);

$haftaGunleri = [];
for ($i = 0; $i < 7; $i++) {
    $haftaGunleri[] = strtotime("+$i days", $haftaBasi);
}

// Seçili haftaya düşen görevler (filtrelenmiş listeden)
$haftaGorevleri = [];
foreach ($gorevler as $g) {
    $t = strtotime($g['teslim_tarihi'] ?? '');
    if ($t && $t >= $haftaBasi && $t <= $haftaSonu) {
        $g['_gun'] = (int)date('N', $t); // 1..7
        $haftaGorevleri[] = $g;
    }
}
usort($haftaGorevleri, function ($a, $b) { return $a['_gun'] <=> $b['_gun']; });

// Grafik: gün bazlı teslim yükü (tüm görevler) ve seçili hafta
$grafikTum   = ['toplam' => array_fill(1, 7, 0), 'biten' => array_fill(1, 7, 0)];
$grafikHafta = ['toplam' => array_fill(1, 7, 0), 'biten' => array_fill(1, 7, 0)];
foreach ($_SESSION['gorevler'] ?? [] as $g) {
    $t = strtotime($g['teslim_tarihi'] ?? '');
    if (!$t) continue;
    $gun = (int)date('N', $t);
    $grafikTum['toplam'][$gun]++;
    if ($g['durum'] === 'Tamamlandı') $grafikTum['biten'][$gun]++;
    if ($t >= $haftaBasi && $t <= $haftaSonu) {
        $grafikHafta['toplam'][$gun]++;
        if ($g['durum'] === 'Tamamlandı') $grafikHafta['biten'][$gun]++;
    }
}

// Ders bazlı görev sayıları (avatar sırası için)
$dersSayilari = [];
foreach ($_SESSION['gorevler'] ?? [] as $g) {
    $k = $g['kategori'] ?? '';
    if ($k === '') continue;
    $dersSayilari[$k] = ($dersSayilari[$k] ?? 0) + 1;
}
arsort($dersSayilari);

function tarihKisa($ts) {
    global $ayKisa;
    return date('j', $ts) . ' ' . $ayKisa[(int)date('n', $ts) - 1];
}

$durumSinifi = ['Bekliyor' => 's-pending', 'Devam Ediyor' => 's-progress', 'Tamamlandı' => 's-done'];
$oncelikKisa = ['Yüksek' => 'Yüksek', 'Orta' => 'Orta', 'Düşük' => 'Düşük'];

$bildirimler = [
    'giris_basarili'    => ['success', 'check', '<strong>Hoş geldin, ' . guvenli($kullanici['ad_soyad'] ?? '') . '!</strong> Çalışma alanın hazır.'],
    'hosgeldin'         => ['success', 'check', '<strong>Hoş geldin, ' . guvenli($kullanici['ad_soyad'] ?? '') . '!</strong> Çalışma alanın hazır.'],
    'eklendi'           => ['success', 'check', '<strong>Görev eklendi.</strong> Yeni görev listene kaydedildi.'],
    'guncellendi'       => ['success', 'check', '<strong>Güncellendi.</strong> Görev bilgileri kaydedildi.'],
    'durum_guncellendi' => ['info', 'refresh', '<strong>Durum değişti.</strong> Görev bir sonraki aşamaya geçirildi.'],
    'silindi'           => ['success', 'check', '<strong>Silindi.</strong> Görev listeden kaldırıldı.'],
    'sifirlandi'        => ['info', 'info', '<strong>Sıfırlandı.</strong> Örnek görevler yeniden yüklendi.'],
    'hata'              => ['danger', 'alert', '<strong>Hata:</strong> İşlem gerçekleştirilemedi veya geçersiz bir istek yapıldı.'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <?php ortakHead('Kontrol Paneli'); ?>
</head>
<body class="has-mobile-nav">

    <?php ustBar('panel', $kullanici, indirUrlOlustur('csv')); ?>

    <main class="container" style="flex: 1; padding-bottom: 32px;">

        <div class="welcome">
            <h2>Tekrar hoş geldin, <strong><?php echo guvenli($kullanici['ad_soyad'] ?? 'Öğrenci'); ?></strong>. Bugün <?php echo tarihKisa($bugun) . ' ' . date('Y', $bugun) . ', ' . $gunAdlari[(int)date('N', $bugun) - 1]; ?>.</h2>
        </div>

        <?php if (isset($bildirimler[$bildirimMesaj])): [$tur, $ik, $metin] = $bildirimler[$bildirimMesaj]; ?>
            <div class="alert alert-<?php echo $tur; ?>" role="status">
                <?php echo ikon($ik, 20); ?>
                <div><?php echo $metin; ?></div>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             1. KPI KARTLARI (tıklanabilir filtreler)
             ============================================================ -->
        <?php
            $toplamAktif     = empty($seciliDurum) && empty($seciliOncelik);
            $bekleyenAktif   = ($seciliDurum === 'Bekliyor');
            $devamAktif      = ($seciliDurum === 'Devam Ediyor');
            $tamamlananAktif = ($seciliDurum === 'Tamamlandı');
            $oncelikAktif    = ($seciliOncelik === 'Yüksek');

            $kartlar = [
                ['Tamamlanan<br>Görev', $istatistik['tamamlanan'], $tamamlananAktif, $tamamlananAktif ? urlOlustur(null, '', null) : urlOlustur(null, 'Tamamlandı', ''), '%' . $istatistik['tamamlanma_orani'] . ' tamamlandı'],
                ['Devam Eden<br>Görev', $istatistik['devam_eden'], $devamAktif, $devamAktif ? urlOlustur(null, '', null) : urlOlustur(null, 'Devam Ediyor', ''), 'Üzerinde çalışılıyor'],
                ['Bekleyen<br>Görev', $istatistik['bekleyen'], $bekleyenAktif, $bekleyenAktif ? urlOlustur(null, '', null) : urlOlustur(null, 'Bekliyor', ''), '<strong>' . $istatistik['geciken'] . '</strong> geciken görev'],
                ['Yüksek<br>Öncelikli', $istatistik['yuksek_oncelik'], $oncelikAktif, $oncelikAktif ? urlOlustur(null, null, '') : urlOlustur(null, '', 'Yüksek'), 'Önce bunlara odaklan'],
            ];
        ?>
        <section class="kpi-grid" aria-label="Görev özetleri">
            <a href="<?php echo urlOlustur(null, '', ''); ?>" class="kpi-card hero-kpi <?php echo ($toplamAktif && !$filtreAktifMi) ? 'active' : ''; ?>" aria-label="Toplam <?php echo $istatistik['toplam']; ?> görev, tümünü listele">
                <div class="kpi-value"><?php echo $istatistik['toplam']; ?></div>
                <span class="kpi-chip">Toplam Görev</span>
            </a>

            <?php foreach ($kartlar as [$etiket, $deger, $aktifMi, $url, $not]): ?>
                <a href="<?php echo $url; ?>" class="kpi-card <?php echo $aktifMi ? 'active' : ''; ?>" aria-pressed="<?php echo $aktifMi ? 'true' : 'false'; ?>">
                    <div class="kpi-top">
                        <div class="kpi-label"><?php echo $etiket; ?></div>
                        <span class="arrow-circle" aria-hidden="true"><?php echo ikon($aktifMi ? 'x' : 'arrow', 18); ?></span>
                    </div>
                    <div class="kpi-bottom">
                        <div class="kpi-value"><?php echo $deger; ?></div>
                        <div class="kpi-note"><?php echo $not; ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>

        <!-- ============================================================
             2. BENTO: Grafik + İlerleme + Dersler | Zaman Çizelgesi
             ============================================================ -->
        <section class="bento">
            <div class="bento-col side">
                <!-- Gün bazlı teslim grafiği -->
                <div class="panel chart">
                    <div class="segment" role="tablist" aria-label="Grafik kapsamı">
                        <button type="button" class="on" role="tab" aria-selected="true" data-kapsam="tum">Tüm Görevler</button>
                        <button type="button" role="tab" aria-selected="false" data-kapsam="hafta">Seçili Hafta</button>
                    </div>
                    <div class="bar-chart" id="barChart" role="img" aria-label="Haftanın günlerine göre teslim edilecek ve tamamlanan görev sayıları">
                        <?php for ($d = 1; $d <= 7; $d++): ?>
                            <div class="bar-group">
                                <div class="bar-pair">
                                    <span class="bar total" data-gun="<?php echo $d; ?>"></span>
                                    <span class="bar done" data-gun="<?php echo $d; ?>"></span>
                                </div>
                                <span class="bar-day <?php echo ((int)date('N', $bugun) === $d) ? 'today' : ''; ?>"><?php echo $gunKisa[$d - 1]; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="legend">
                        <span><i style="background: var(--pill-white);"></i>Teslim edilecek</span>
                        <span><i style="background: var(--accent);"></i>Tamamlanan</span>
                    </div>
                </div>

                <!-- Dönem ilerlemesi -->
                <div class="panel">
                    <div class="progress-ring">
                        <div class="ring" style="--p: <?php echo (int)$istatistik['tamamlanma_orani']; ?>;" role="img" aria-label="Yüzde <?php echo (int)$istatistik['tamamlanma_orani']; ?> tamamlandı">
                            <span>%<?php echo (int)$istatistik['tamamlanma_orani']; ?></span>
                        </div>
                        <div class="progress-meta">
                            <strong>Dönem ilerlemesi</strong>
                            <?php echo $istatistik['tamamlanan']; ?> / <?php echo $istatistik['toplam']; ?> görev tamamlandı
                            <?php if ($istatistik['geciken'] > 0): ?>
                                <div style="margin-top: 8px;"><span class="deadline-chip deadline-overdue"><?php echo $istatistik['geciken']; ?> geciken görev</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Dersler -->
                <div class="panel">
                    <div class="course-row">
                        <div>
                            <div class="panel-title">Dersler</div>
                            <div class="panel-sub"><?php echo count($dersSayilari); ?> aktif ders</div>
                        </div>
                        <div class="avatar-stack">
                            <?php foreach (array_slice($dersSayilari, 0, 5, true) as $ders => $adet): ?>
                                <a href="<?php echo $seciliKategori === $ders ? urlOlustur('', null, null) : urlOlustur($ders, null, null); ?>"
                                   class="<?php echo $seciliKategori === $ders ? 'on' : ''; ?>"
                                   style="background: <?php echo dersRengi($ders); ?>;"
                                   title="<?php echo guvenli($ders . ' — ' . $adet . ' görev'); ?>"
                                   aria-label="<?php echo guvenli($ders); ?> dersine göre filtrele">
                                    <?php echo guvenli(basHarfler($ders)); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Haftalık zaman çizelgesi -->
            <div class="panel" id="zaman-cizelgesi">
                <div class="panel-head" style="margin-bottom: 0;">
                    <div class="chip-row">
                        <a href="<?php echo urlOlustur(null, '', null); ?>" class="chip <?php echo empty($seciliDurum) ? 'on' : ''; ?>">Tümü</a>
                        <a href="<?php echo urlOlustur(null, 'Bekliyor', null); ?>" class="chip <?php echo $bekleyenAktif ? 'on' : ''; ?>">Bekliyor</a>
                        <a href="<?php echo urlOlustur(null, 'Devam Ediyor', null); ?>" class="chip <?php echo $devamAktif ? 'on' : ''; ?>">Devam Ediyor</a>
                        <a href="<?php echo urlOlustur(null, 'Tamamlandı', null); ?>" class="chip <?php echo $tamamlananAktif ? 'on' : ''; ?>">Tamamlandı</a>
                    </div>
                    <div class="week-nav">
                        <a href="<?php echo urlOlustur(null, null, null, null, null, $haftaOfset - 1); ?>#zaman-cizelgesi" class="icon-btn" aria-label="Önceki hafta"><?php echo ikon('left', 16); ?></a>
                        <a href="<?php echo urlOlustur(null, null, null, null, null, 0); ?>#zaman-cizelgesi" class="label" title="Bu haftaya dön"><?php echo tarihKisa($haftaBasi) . ' – ' . tarihKisa($haftaSonu); ?></a>
                        <a href="<?php echo urlOlustur(null, null, null, null, null, $haftaOfset + 1); ?>#zaman-cizelgesi" class="icon-btn" aria-label="Sonraki hafta"><?php echo ikon('right', 16); ?></a>
                    </div>
                </div>

                <div class="timeline">
                    <div class="timeline-inner">
                        <div class="tl-days">
                            <?php foreach ($haftaGunleri as $i => $ts): ?>
                                <div class="tl-day <?php echo $ts === $bugun ? 'today' : ''; ?>">
                                    <small><?php echo tarihKisa($ts); ?></small>
                                    <b><?php echo $gunAdlari[$i]; ?></b>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="tl-body">
                            <?php if (empty($haftaGorevleri)): ?>
                                <div class="tl-empty">
                                    <div>
                                        Bu hafta teslim tarihi olan görev yok.<br>
                                        <a href="ekle.php" class="btn btn-secondary btn-sm" style="margin-top: 12px;"><?php echo ikon('plus', 14); ?>Görev ekle</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($haftaGorevleri as $g):
                                    $gun   = $g['_gun'];
                                    $bas   = max(1, $gun - 1);
                                    $son   = $gun + 1;
                                    $gec   = ($g['durum'] !== 'Tamamlandı' && strtotime($g['teslim_tarihi']) < $bugun);
                                    $json  = htmlspecialchars(json_encode([
                                        'id' => $g['id'], 'baslik' => $g['baslik'], 'kategori' => $g['kategori'],
                                        'oncelik' => $g['oncelik'], 'durum' => $g['durum'],
                                        'teslim_tarihi' => tarihFormati($g['teslim_tarihi']),
                                        'kalan_gun' => kalanGunRozeti($g['teslim_tarihi'], $g['durum']),
                                        'aciklama' => $g['aciklama'],
                                    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <div class="tl-lane">
                                        <button type="button"
                                                class="tl-pill <?php echo $durumSinifi[$g['durum']] ?? 's-pending'; ?> <?php echo $gec ? 'late' : ''; ?>"
                                                style="grid-column: <?php echo $bas; ?> / <?php echo $son; ?>;"
                                                onclick="gorevDetayAc(<?php echo $json; ?>)"
                                                title="<?php echo guvenli($g['baslik'] . ' — ' . $g['durum'] . ', teslim ' . tarihFormati($g['teslim_tarihi'])); ?>">
                                            <span class="t"><?php echo guvenli($g['baslik']); ?></span>
                                            <span class="tag"><?php echo guvenli($oncelikKisa[$g['oncelik']] ?? $g['oncelik']); ?></span>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             3. ARAMA, FİLTRELEME VE SIRALAMA (GET)
             ============================================================ -->
        <section class="panel filter-card" id="gorevler" aria-label="Görev arama ve filtreleme">
            <form method="GET" action="index.php" class="filter-form">
                <div class="form-group grow">
                    <label for="arama">Ara</label>
                    <input type="search" name="arama" id="arama" class="form-control"
                           placeholder="Başlık, açıklama veya ders…" value="<?php echo guvenli($aramaMetni); ?>">
                </div>
                <div class="form-group">
                    <label for="kategori">Ders</label>
                    <select name="kategori" id="kategori" class="form-control">
                        <option value="Tümü">Tüm dersler</option>
                        <?php foreach ($kategoriler as $kat): ?>
                            <option value="<?php echo guvenli($kat); ?>" <?php echo ($seciliKategori === $kat) ? 'selected' : ''; ?>><?php echo guvenli($kat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="durum">Durum</label>
                    <select name="durum" id="durum" class="form-control">
                        <option value="Tümü">Tüm durumlar</option>
                        <?php foreach (['Bekliyor', 'Devam Ediyor', 'Tamamlandı'] as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo ($seciliDurum === $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="oncelik">Öncelik</label>
                    <select name="oncelik" id="oncelik" class="form-control">
                        <option value="Tümü">Tüm öncelikler</option>
                        <?php foreach (['Yüksek', 'Orta', 'Düşük'] as $o): ?>
                            <option value="<?php echo $o; ?>" <?php echo ($seciliOncelik === $o) ? 'selected' : ''; ?>><?php echo $o; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="siralama">Sıralama</label>
                    <select name="siralama" id="siralama" class="form-control">
                        <option value="varsayilan" <?php echo ($siralama === '' || $siralama === 'varsayilan') ? 'selected' : ''; ?>>Varsayılan</option>
                        <option value="tarih_artan" <?php echo ($siralama === 'tarih_artan') ? 'selected' : ''; ?>>Teslim (en yakın)</option>
                        <option value="tarih_azalan" <?php echo ($siralama === 'tarih_azalan') ? 'selected' : ''; ?>>Teslim (en uzak)</option>
                        <option value="oncelik" <?php echo ($siralama === 'oncelik') ? 'selected' : ''; ?>>Öncelik (yüksek → düşük)</option>
                        <option value="baslik_az" <?php echo ($siralama === 'baslik_az') ? 'selected' : ''; ?>>Ad (A → Z)</option>
                    </select>
                </div>
                <?php if ($haftaOfset !== 0): ?><input type="hidden" name="hafta" value="<?php echo (int)$haftaOfset; ?>"><?php endif; ?>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary"><?php echo ikon('search', 16); ?>Uygula</button>
                    <?php if ($filtreAktifMi): ?>
                        <a href="index.php" class="btn btn-secondary">Temizle</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($filtreAktifMi): ?>
            <div class="active-filter-banner">
                <div class="active-filter-chips">
                    <?php
                        $aktifCipler = [];
                        if (!empty($aramaMetni)) $aktifCipler[] = ['Arama', '"' . $aramaMetni . '"', urlOlustur(null, null, null, '')];
                        if (!empty($seciliDurum) && $seciliDurum !== 'Tümü') $aktifCipler[] = ['Durum', $seciliDurum, urlOlustur(null, '', null)];
                        if (!empty($seciliOncelik) && $seciliOncelik !== 'Tümü') $aktifCipler[] = ['Öncelik', $seciliOncelik, urlOlustur(null, null, '')];
                        if (!empty($seciliKategori) && $seciliKategori !== 'Tümü') $aktifCipler[] = ['Ders', $seciliKategori, urlOlustur('', null, null)];
                        if (!empty($siralama) && $siralama !== 'varsayilan') $aktifCipler[] = ['Sıralama', $siralama, urlOlustur(null, null, null, null, '')];
                    ?>
                    <?php foreach ($aktifCipler as [$ad, $deger, $url]): ?>
                        <span class="filter-chip">
                            <?php echo $ad; ?>: <strong><?php echo guvenli($deger); ?></strong>
                            <a href="<?php echo $url; ?>" class="filter-chip-remove" aria-label="<?php echo $ad; ?> filtresini kaldır"><?php echo ikon('x', 12); ?></a>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================================
             4. GÖREV LİSTESİ
             ============================================================ -->
        <section class="content-card">
            <div class="content-header" style="flex-wrap: wrap;">
                <div>
                    <h2><?php echo guvenli($filtreBasligi); ?></h2>
                    <div class="panel-sub"><?php echo count($gorevler); ?> kayıt · Durum rozetine tıklayarak sonraki aşamaya geçir</div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="<?php echo guvenli(indirUrlOlustur('csv')); ?>" class="btn btn-secondary btn-sm"><?php echo ikon('download', 14); ?>CSV indir</a>
                    <a href="ekle.php" class="btn btn-primary btn-sm"><?php echo ikon('plus', 14); ?>Yeni görev</a>
                </div>
            </div>

            <?php if (empty($gorevler)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><?php echo ikon('folder', 30); ?></div>
                    <h3>Eşleşen görev bulunamadı</h3>
                    <p>Bu ölçütlere uyan kayıt yok. Filtreleri temizleyebilir ya da yeni bir görev ekleyebilirsin.</p>
                    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <a href="index.php" class="btn btn-secondary btn-sm">Tüm görevleri göster</a>
                        <a href="ekle.php" class="btn btn-primary btn-sm"><?php echo ikon('plus', 14); ?>Yeni görev</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 56px;">#</th>
                                <th>Görev</th>
                                <th>Ders</th>
                                <th>Öncelik</th>
                                <th>Durum</th>
                                <th>Teslim</th>
                                <th style="text-align: right;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gorevler as $gorev): ?>
                                <?php
                                    $kalanGunHtml = kalanGunRozeti($gorev['teslim_tarihi'], $gorev['durum']);
                                    $gorevJson = htmlspecialchars(json_encode([
                                        'id'            => $gorev['id'],
                                        'baslik'        => $gorev['baslik'],
                                        'kategori'      => $gorev['kategori'],
                                        'oncelik'       => $gorev['oncelik'],
                                        'durum'         => $gorev['durum'],
                                        'teslim_tarihi' => tarihFormati($gorev['teslim_tarihi']),
                                        'kalan_gun'     => $kalanGunHtml,
                                        'aciklama'      => $gorev['aciklama']
                                    ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr class="clickable-row" onclick="satirTiklandi(event, this)" data-gorev='<?php echo $gorevJson; ?>'>
                                    <td data-label="#ID" style="color: var(--text-faint); font-weight: 700;">#<?php echo guvenli($gorev['id']); ?></td>
                                    <td data-label="Görev">
                                        <div class="task-cell">
                                            <span class="task-dot" style="background: <?php echo dersRengi($gorev['kategori']); ?>;" aria-hidden="true"><?php echo guvenli(basHarfler($gorev['kategori'])); ?></span>
                                            <div style="min-width: 0;">
                                                <div class="task-title"><?php echo guvenli($gorev['baslik']); ?></div>
                                                <div class="task-desc"><?php echo guvenli($gorev['aciklama']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Ders"><span class="category-tag"><?php echo guvenli($gorev['kategori']); ?></span></td>
                                    <td data-label="Öncelik"><?php echo oncelikRozeti($gorev['oncelik']); ?></td>
                                    <td data-label="Durum" onclick="event.stopPropagation();">
                                        <form method="POST" action="durum_degistir.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo guvenli($gorev['id']); ?>">
                                            <button type="submit" class="status-toggle-btn" title="Sonraki aşamaya geçir (Bekliyor → Devam Ediyor → Tamamlandı)">
                                                <?php echo durumRozeti($gorev['durum']); ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td data-label="Teslim">
                                        <div class="deadline-wrap">
                                            <span class="deadline-date"><?php echo tarihFormati($gorev['teslim_tarihi']); ?></span>
                                            <?php echo $kalanGunHtml; ?>
                                        </div>
                                    </td>
                                    <td data-label="İşlemler" style="text-align: right;" onclick="event.stopPropagation();">
                                        <div class="action-group">
                                            <button type="button" class="btn-icon-edit" onclick="gorevDetayAc(<?php echo $gorevJson; ?>)" aria-label="Görevi incele"><?php echo ikon('eye', 14); ?><span>İncele</span></button>
                                            <a href="duzenle.php?id=<?php echo guvenli($gorev['id']); ?>" class="btn-icon-edit" aria-label="Görevi düzenle"><?php echo ikon('edit', 14); ?><span>Düzenle</span></a>
                                            <form method="POST" action="sil.php" style="display: inline;" onsubmit="return confirm('Bu görevi silmek istediğinize emin misiniz?');">
                                                <input type="hidden" name="id" value="<?php echo guvenli($gorev['id']); ?>">
                                                <button type="submit" class="btn-icon-delete" aria-label="Görevi sil"><?php echo ikon('trash', 14); ?><span>Sil</span></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- ================================================================
         GÖREV DETAY MODALI
         ================================================================ -->
    <div id="gorevModal" class="modal-overlay" onclick="modalArkaPlanTiklandi(event)">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalBaslik">
            <div class="modal-header">
                <div>
                    <span id="modalIdRozet" class="category-tag" style="margin-bottom: 8px;">#ID</span>
                    <h3 id="modalBaslik">Görev Detayı</h3>
                </div>
                <button type="button" class="modal-close-btn" onclick="modalKapat()" aria-label="Kapat"><?php echo ikon('x', 18); ?></button>
            </div>

            <div class="modal-body">
                <div class="modal-field">
                    <div class="modal-field-label">Ders</div>
                    <div id="modalKategori" class="modal-field-value" style="font-weight: 700;">-</div>
                </div>
                <div class="modal-field">
                    <div class="modal-field-label">Durum ve öncelik</div>
                    <div class="modal-badges"><span id="modalDurumRozet"></span><span id="modalOncelikRozet"></span></div>
                </div>
                <div class="modal-field">
                    <div class="modal-field-label">Durumu değiştir</div>
                    <div class="segment" style="margin-top: 4px;">
                        <?php foreach (['Bekliyor', 'Devam Ediyor', 'Tamamlandı'] as $d): ?>
                            <form method="POST" action="durum_degistir.php" style="flex: 1; display: flex;">
                                <input type="hidden" name="id" class="modal-durum-id" value="">
                                <input type="hidden" name="yeni_durum" value="<?php echo $d; ?>">
                                <button type="submit" class="modal-durum-btn" data-durum="<?php echo $d; ?>"><?php echo $d; ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-field">
                    <div class="modal-field-label">Teslim tarihi</div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span id="modalTarih" class="modal-field-value" style="font-weight: 700;">-</span>
                        <span id="modalKalanGun"></span>
                    </div>
                </div>
                <div class="modal-field" style="margin-bottom: 0;">
                    <div class="modal-field-label">Açıklama</div>
                    <div id="modalAciklama" class="modal-note">-</div>
                </div>
            </div>

            <div class="modal-footer">
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <a id="modalDuzenleLink" href="duzenle.php" class="btn btn-primary btn-sm"><?php echo ikon('edit', 14); ?>Düzenle</a>
                    <form id="modalSilForm" method="POST" action="sil.php" onsubmit="return confirm('Bu görevi silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="id" id="modalSilId" value="">
                        <button type="submit" class="btn-icon-delete"><?php echo ikon('trash', 14); ?>Sil</button>
                    </form>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="modalKapat()">Kapat</button>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div><strong>EduTask Workspace</strong> — İnternet Programcılığı I</div>
            <div>
                PHP <?php echo PHP_VERSION; ?> ·
                <a href="index.php?sifirla=1" onclick="return confirm('Varsayılan örnek verileri yeniden yüklemek istiyor musunuz?');">Örnek verileri sıfırla</a>
            </div>
        </div>
    </footer>

    <?php temaBetigi(); ?>
    <script>
        /* ---------- Gün bazlı teslim grafiği ---------- */
        var grafikVerisi = {
            tum:   <?php echo json_encode(['toplam' => array_values($grafikTum['toplam']), 'biten' => array_values($grafikTum['biten'])]); ?>,
            hafta: <?php echo json_encode(['toplam' => array_values($grafikHafta['toplam']), 'biten' => array_values($grafikHafta['biten'])]); ?>
        };
        var gunAdlari = <?php echo json_encode($gunAdlari, JSON_UNESCAPED_UNICODE); ?>;

        function grafigiCiz(kapsam) {
            var v = grafikVerisi[kapsam];
            var enBuyuk = Math.max(1, Math.max.apply(null, v.toplam));
            document.querySelectorAll('#barChart .bar-group').forEach(function (grup, i) {
                var t = grup.querySelector('.bar.total');
                var b = grup.querySelector('.bar.done');
                t.style.height = (v.toplam[i] / enBuyuk * 100) + '%';
                b.style.height = (v.biten[i] / enBuyuk * 100) + '%';
                grup.title = gunAdlari[i] + ': ' + v.toplam[i] + ' teslim, ' + v.biten[i] + ' tamamlandı';
            });
        }
        document.querySelectorAll('.segment [data-kapsam]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.segment [data-kapsam]').forEach(function (b) {
                    b.classList.toggle('on', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                grafigiCiz(btn.dataset.kapsam);
            });
        });
        grafigiCiz('tum');

        /* ---------- Görev detay modalı ---------- */
        var modal = document.getElementById('gorevModal');
        var sonOdak = null;

        function satirTiklandi(event, satir) {
            if (event.target.closest('button, form, a')) return;
            try { gorevDetayAc(JSON.parse(satir.getAttribute('data-gorev'))); } catch (e) { console.error(e); }
        }

        function gorevDetayAc(gorev) {
            sonOdak = document.activeElement;
            document.getElementById('modalIdRozet').textContent = '#' + gorev.id;
            document.getElementById('modalBaslik').textContent = gorev.baslik;
            document.getElementById('modalKategori').textContent = gorev.kategori;
            document.getElementById('modalTarih').textContent = gorev.teslim_tarihi;
            document.getElementById('modalKalanGun').innerHTML = gorev.kalan_gun || '';
            document.getElementById('modalAciklama').textContent = gorev.aciklama;
            document.getElementById('modalSilId').value = gorev.id;
            document.getElementById('modalDuzenleLink').href = 'duzenle.php?id=' + encodeURIComponent(gorev.id);
            document.querySelectorAll('.modal-durum-id').forEach(function (i) { i.value = gorev.id; });
            document.querySelectorAll('.modal-durum-btn').forEach(function (b) {
                b.classList.toggle('on', b.dataset.durum === gorev.durum);
            });

            var durum = { 'Tamamlandı': 'badge-success', 'Devam Ediyor': 'badge-warning' }[gorev.durum] || 'badge-secondary';
            document.getElementById('modalDurumRozet').innerHTML = '<span class="badge ' + durum + '"><span class="badge-dot"></span>' + gorev.durum + '</span>';
            var onc = { 'Yüksek': 'badge-danger', 'Orta': 'badge-info' }[gorev.oncelik] || 'badge-muted';
            document.getElementById('modalOncelikRozet').innerHTML = '<span class="badge ' + onc + '">' + gorev.oncelik + ' öncelik</span>';

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            modal.querySelector('.modal-close-btn').focus();
        }

        function modalKapat() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            if (sonOdak) sonOdak.focus();
        }
        function modalArkaPlanTiklandi(e) { if (e.target === modal) modalKapat(); }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) modalKapat();
        });
    </script>
</body>
</html>
