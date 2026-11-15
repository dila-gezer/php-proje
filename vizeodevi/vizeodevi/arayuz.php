<?php
/**
 * ============================================================================
 * DOSYA: arayuz.php
 * AÇIKLAMA: Tüm sayfalarda ortak kullanılan arayüz parçaları
 *           (head etiketleri, hap menülü üst bar, mobil alt menü, tema betiği).
 *           "Pro Workspace" Dribbble tasarımından esinlenilmiştir.
 * ============================================================================
 */

/** SVG ikon kütüphanesi (Lucide tarzı, emoji yerine) */
function ikon($ad, $boyut = 18) {
    $yollar = [
        'plus'     => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
        'moon'     => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
        'arrow'    => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>',
        'left'     => '<polyline points="15 18 9 12 15 6"/>',
        'right'    => '<polyline points="9 18 15 12 9 6"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'trash'    => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'check'    => '<polyline points="20 6 9 17 4 12"/>',
        'info'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'alert'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'refresh'  => '<polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'x'        => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'folder'   => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'logo'     => '<path d="M4 12l5 5L20 6"/>',
    ];
    $yol = $yollar[$ad] ?? '';
    return '<svg width="' . (int)$boyut . '" height="' . (int)$boyut . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $yol . '</svg>';
}

/** Ad soyaddan baş harfleri üretir: "Demo Öğrenci" -> "DÖ" */
function basHarfler($metin) {
    $parcalar = preg_split('/\s+/u', trim((string)$metin));
    $harfler = '';
    foreach (array_slice($parcalar, 0, 2) as $p) {
        $harfler .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
    }
    return $harfler !== '' ? $harfler : 'Ö';
}

/** <head> içi ortak etiketler + tema betiği (FOUC önlemek için erken çalışır) */
function ortakHead($baslik) {
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo guvenli($baslik); ?> · EduTask Workspace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/style.css?v=<?php echo @filemtime(__DIR__ . '/assets/style.css'); ?>">
    <script>
        (function () {
            try {
                if (localStorage.getItem('edutask_theme') === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            } catch (e) {}
        })();
    </script>
    <?php
}

/**
 * Hap menülü üst bar.
 * @param string     $aktif     'panel' | 'ekle' | 'duzenle' | 'giris' | 'kayit'
 * @param array|null $kullanici Oturumdaki kullanıcı (yoksa misafir menüsü)
 * @param string     $csvUrl    CSV indirme bağlantısı
 */
function ustBar($aktif, $kullanici = null, $csvUrl = 'indir.php?format=csv') {
    $girisli = !empty($kullanici);
    ?>
    <header class="site-header">
        <div class="nav-wrap">
            <a href="<?php echo $girisli ? 'index.php' : 'login.php'; ?>" class="brand" aria-label="EduTask Workspace ana sayfa">
                <div class="brand-icon"><?php echo ikon('logo', 20); ?></div>
                <div class="brand-text">
                    <h1>EduTask <span class="brand-badge">WORKSPACE</span></h1>
                </div>
            </a>

            <?php if ($girisli): ?>
                <nav class="nav-pills" aria-label="Ana menü">
                    <a href="index.php" class="nav-pill-link <?php echo $aktif === 'panel' ? 'active' : ''; ?>">Panel</a>
                    <a href="index.php#gorevler" class="nav-pill-link">Görevler</a>
                    <a href="index.php#zaman-cizelgesi" class="nav-pill-link">Takvim</a>
                    <a href="<?php echo guvenli($csvUrl); ?>" class="nav-pill-link">Dışa Aktar</a>
                    <a href="ekle.php" class="nav-pill-link <?php echo $aktif === 'ekle' ? 'active' : ''; ?>">Yeni Görev</a>
                </nav>
            <?php endif; ?>

            <div class="header-actions">
                <?php if ($girisli): ?>
                    <a href="ekle.php" class="btn btn-primary btn-sm hide-sm"><?php echo ikon('plus', 16); ?><span>Görev Ekle</span></a>
                <?php endif; ?>

                <button type="button" class="theme-toggle-btn" onclick="temaDegistir()" aria-label="Aydınlık / karanlık temayı değiştir" title="Tema değiştir">
                    <span class="tema-ikon-gunes"><?php echo ikon('sun', 18); ?></span>
                    <span class="tema-ikon-ay" hidden><?php echo ikon('moon', 18); ?></span>
                </button>

                <?php if ($girisli): ?>
                    <a href="cikis.php" class="icon-btn" aria-label="Oturumu kapat" title="Oturumu kapat"><?php echo ikon('logout', 18); ?></a>
                    <span class="avatar" title="<?php echo guvenli(($kullanici['ad_soyad'] ?? '') . ' — ' . ($kullanici['bolum'] ?? '')); ?>">
                        <?php echo guvenli(basHarfler($kullanici['ad_soyad'] ?? 'Öğrenci')); ?>
                    </span>
                <?php elseif ($aktif === 'giris'): ?>
                    <a href="kayit.php" class="btn btn-secondary btn-sm">Hesap Oluştur</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary btn-sm">Giriş Yap</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($girisli): ?>
        <nav class="mobile-nav" aria-label="Mobil menü">
            <a href="index.php" class="<?php echo $aktif === 'panel' ? 'active' : ''; ?>"><?php echo ikon('grid', 18); ?><span>Panel</span></a>
            <a href="ekle.php" class="<?php echo $aktif === 'ekle' ? 'active' : ''; ?>"><?php echo ikon('plus', 18); ?><span>Ekle</span></a>
            <a href="<?php echo guvenli($csvUrl); ?>" aria-label="CSV dışa aktar"><?php echo ikon('download', 18); ?></a>
        </nav>
    <?php endif;
}

/** Sayfa sonu: tema değiştirici betiği */
function temaBetigi() {
    ?>
    <script>
        function temaIkonuGuncelle() {
            var acik = document.documentElement.getAttribute('data-theme') === 'light';
            document.querySelectorAll('.tema-ikon-gunes').forEach(function (e) { e.hidden = acik; });
            document.querySelectorAll('.tema-ikon-ay').forEach(function (e) { e.hidden = !acik; });
        }
        function temaDegistir() {
            var acik = document.documentElement.getAttribute('data-theme') === 'light';
            if (acik) {
                document.documentElement.removeAttribute('data-theme');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
            try { localStorage.setItem('edutask_theme', acik ? 'dark' : 'light'); } catch (e) {}
            temaIkonuGuncelle();
        }
        temaIkonuGuncelle();

        /*
         * Dışa aktarma (CSV/JSON): Bazı tarayıcılar / gömülü önizlemeler
         * "Content-Disposition: attachment" ile gelen bağlantı gezinmesini iptal
         * edip boş dosya bırakabiliyor. Bu yüzden dosyayı fetch ile alıp Blob
         * olarak indiriyoruz; bir sorun olursa klasik bağlantıya geri dönüyoruz.
         */
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="indir.php"]');
            if (!link || e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            fetch(link.href, { credentials: 'same-origin', cache: 'no-store' })
                .then(function (yanit) {
                    var tur = yanit.headers.get('content-type') || '';
                    if (!yanit.ok) throw new Error('HTTP ' + yanit.status);
                    if (tur.indexOf('text/html') !== -1) { // oturum düşmüş → giriş sayfası
                        window.location.href = 'login.php';
                        return null;
                    }
                    var cd = yanit.headers.get('content-disposition') || '';
                    var eslesme = cd.match(/filename="?([^";]+)"?/i);
                    var ad = eslesme ? eslesme[1] : 'edutask_gorevler.csv';
                    return yanit.blob().then(function (veri) { return { veri: veri, ad: ad }; });
                })
                .then(function (dosya) {
                    if (!dosya) return;
                    if (!dosya.veri.size) throw new Error('Boş yanıt');
                    var url = URL.createObjectURL(dosya.veri);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = dosya.ad;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(function () { URL.revokeObjectURL(url); }, 5000);
                })
                .catch(function () {
                    window.location.href = link.href; // klasik indirme yedeği
                });
        });
    </script>
    <?php
}

/** Ders adı için sabit, ayırt edilebilir pastel renk */
function dersRengi($kategori) {
    $palet = ['#d4f34a', '#ffffff', '#7dd3fc', '#fbbf24', '#ff9a8b', '#c4b5fd', '#86efac', '#fda4af'];
    return $palet[abs(crc32((string)$kategori)) % count($palet)];
}
