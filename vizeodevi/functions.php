<?php
/**
 * ============================================================================
 * PROJE: EduTask - Öğrenci Görev ve Proje Takip Sistemi
 * DOSYA: functions.php
 * AÇIKLAMA: Bu dosya projenin beynidir. Oturum yönetimi, veri depolama,
 *           veri doğrulama, güvenli çıktı ve yardımcı fonksiyonları içerir.
 * ============================================================================
 */

// 1. OTURUM BAŞLATMA VE VERİ HAZIRLIĞI
// PHP'de $_SESSION kullanabilmek için her sayfanın başında session_start() çağrılmalıdır.
if (session_status() === PHP_SESSION_NONE) {
    // Oturum dizini yazılabilir değilse sistem temp dizinini kullan (Windows/XAMPP uyumluluğu için)
    $mevcutDizin = session_save_path();
    if (empty($mevcutDizin) || !@is_writable($mevcutDizin)) {
        @ini_set('session.save_path', sys_get_temp_dir());
    }
    session_start();
}

/**
 * 1.1. YEREL VERİ DOSYASI (data/gorevler.json) VE VARSAYILAN VERİLER
 * Tarayıcı kapatılsa bile verilerin kaybolmaması için kayıtlar hem $_SESSION
 * hem de yerel "data/gorevler.json" dosyasında senkronize olarak saklanır.
 */
function veriDosyasiYolu() {
    $klasor = __DIR__ . '/data';
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0777, true);
    }
    return $klasor . '/gorevler.json';
}

function varsayilanGorevler() {
    return [
        [
            'id'            => 1,
            'baslik'        => 'PHP 3 Haftalık Proje Ödevi',
            'kategori'      => 'İnternet Programcılığı I',
            'oncelik'       => 'Yüksek',
            'durum'         => 'Devam Ediyor',
            'teslim_tarihi' => '2026-11-08',
            'aciklama'      => 'Kayıt ve takip sistemi ilk sürümünün (MVP) hazırlanması, form ve oturum testleri.'
        ],
        [
            'id'            => 2,
            'baslik'        => 'Veritabanı Normalizasyon Raporu',
            'kategori'      => 'Veritabanı Yönetimi',
            'oncelik'       => 'Orta',
            'durum'         => 'Bekliyor',
            'teslim_tarihi' => '2026-11-12',
            'aciklama'      => '3NF ve BCNF kurallarına göre örnek kütüphane şemasının çözümlenmesi ve raporlanması.'
        ],
        [
            'id'            => 3,
            'baslik'        => 'Algoritma Karmaşıklığı Analizi (Big-O)',
            'kategori'      => 'Veri Yapıları',
            'oncelik'       => 'Düşük',
            'durum'         => 'Tamamlandı',
            'teslim_tarihi' => '2026-10-25',
            'aciklama'      => 'Arama ve sıralama algoritmalarının zaman ve bellek karmaşıklığı tablosu çıkarıldı.'
        ],
        [
            'id'            => 4,
            'baslik'        => 'Responsive Portfolyo Web Sitesi',
            'kategori'      => 'Web Tasarımı',
            'oncelik'       => 'Orta',
            'durum'         => 'Tamamlandı',
            'teslim_tarihi' => '2026-10-28',
            'aciklama'      => 'CSS Flexbox ve CSS Grid kullanılarak mobil uyumlu kişisel tanıtım sitesi yapıldı.'
        ],
        [
            'id'            => 5,
            'baslik'        => 'Yapay Zekâ Proje Fikri Sunumu',
            'kategori'      => 'Yapay Zekâya Giriş',
            'oncelik'       => 'Yüksek',
            'durum'         => 'Bekliyor',
            'teslim_tarihi' => '2026-11-15',
            'aciklama'      => 'Görüntü işleme tabanlı plaka tanıma sistemi üzerine 10 slaytlık hazırlık sunumu.'
        ],
        [
            'id'            => 6,
            'baslik'        => 'İngilizce Teknik Makale Özeti',
            'kategori'      => 'Mesleki Yabancı Dil',
            'oncelik'       => 'Düşük',
            'durum'         => 'Devam Ediyor',
            'teslim_tarihi' => '2026-11-20',
            'aciklama'      => 'Cloud computing (Bulut bilişim) üzerine IEEE makalesinin 2 sayfalık Türkçe özeti.'
        ]
    ];
}

/**
 * Görev dizisini yerel JSON dosyasına (data/gorevler.json) ve oturuma kaydeder.
 *
 * @param array $gorevler Kaydedilecek görev listesi
 * @return bool Kayıt başarılıysa true
 */
function verileriDosyayaKaydet($gorevler) {
    $_SESSION['gorevler'] = array_values($gorevler);
    $dosya = veriDosyasiYolu();
    $jsonVeri = json_encode($_SESSION['gorevler'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return (@file_put_contents($dosya, $jsonVeri) !== false);
}

/**
 * Oturumu ve yerel JSON verilerini hazırlar.
 * Önce data/gorevler.json dosyasını kontrol eder; dosya varsa oradan yükler,
 * yoksa hocanın istediği 6 örnek kaydı oluşturup hem JSON dosyasına hem $_SESSION'a yazar.
 */
function sistemiBaslat() {
    $dosya = veriDosyasiYolu();

    if (file_exists($dosya)) {
        $icerik = @file_get_contents($dosya);
        $cozulenVeri = !empty($icerik) ? json_decode($icerik, true) : null;
        if (is_array($cozulenVeri)) {
            $_SESSION['gorevler'] = $cozulenVeri;
            return;
        }
    }

    if (!isset($_SESSION['gorevler']) || !is_array($_SESSION['gorevler'])) {
        $varsayilan = varsayilanGorevler();
        verileriDosyayaKaydet($varsayilan);
    } else {
        verileriDosyayaKaydet($_SESSION['gorevler']);
    }
}

/**
 * Verileri varsayılan 6 örnek kayda sıfırlar ve yerel JSON dosyasını günceller.
 */
function verileriSifirla() {
    $varsayilan = varsayilanGorevler();
    verileriDosyayaKaydet($varsayilan);
}

// Sistemi hemen başlatalım
sistemiBaslat();

/**
 * 2. GÜVENLİK FONKSİYONU: htmlspecialchars()
 * Kullanıcı girdilerini ekrana basarken XSS (Cross-Site Scripting) saldırılarını
 * engellemek için tüm özel HTML karakterlerini zararsız HTML varlıklarına dönüştürür.
 *
 * @param string|null $veri Kullanıcıdan gelen metin
 * @return string Güvenli hale getirilmiş metin
 */
function guvenli($veri) {
    if ($veri === null) {
        return '';
    }
    return htmlspecialchars(trim((string)$veri), ENT_QUOTES, 'UTF-8');
}

/**
 * 3. KAYITLARI LİSTELEME, ARAMA, FİLTRELEME VE SIRALAMA FONKSİYONU
 * GET parametreleri ile gelen kategori, durum, öncelik, arama metni ve sıralama
 * ölçütüne göre görev listesini filtreler ve sıralar.
 *
 * @param string $seciliKategori Filtrelenecek ders/kategori
 * @param string $seciliDurum    Filtrelenecek durum (Bekliyor, Devam Ediyor, Tamamlandı)
 * @param string $seciliOncelik  Filtrelenecek öncelik (Düşük, Orta, Yüksek)
 * @param string $aramaMetni     Başlık, açıklama veya kategoride aranacak kelime
 * @param string $siralama       Sıralama ölçütü (tarih_artan, tarih_azalan, oncelik, baslik_az)
 * @return array Filtrelenmiş ve sıralanmış görev listesi
 */
function gorevleriGetir($seciliKategori = '', $seciliDurum = '', $seciliOncelik = '', $aramaMetni = '', $siralama = '') {
    $tumGorevler = $_SESSION['gorevler'] ?? [];
    $sonuclar = [];
    $aramaMetni = trim((string)$aramaMetni);

    foreach ($tumGorevler as $gorev) {
        $kategoriUygun = true;
        $durumUygun    = true;
        $oncelikUygun  = true;
        $aramaUygun    = true;

        // Kategori filtresi kontrolü
        if (!empty($seciliKategori) && $seciliKategori !== 'Tümü') {
            if ($gorev['kategori'] !== $seciliKategori) {
                $kategoriUygun = false;
            }
        }

        // Durum filtresi kontrolü
        if (!empty($seciliDurum) && $seciliDurum !== 'Tümü') {
            if ($gorev['durum'] !== $seciliDurum) {
                $durumUygun = false;
            }
        }

        // Öncelik filtresi kontrolü
        if (!empty($seciliOncelik) && $seciliOncelik !== 'Tümü') {
            if ($gorev['oncelik'] !== $seciliOncelik) {
                $oncelikUygun = false;
            }
        }

        // Metin arama kontrolü (Başlık, Açıklama veya Kategori içinde arar)
        if ($aramaMetni !== '') {
            $birlestirilmisMetin = ($gorev['baslik'] ?? '') . ' ' . ($gorev['aciklama'] ?? '') . ' ' . ($gorev['kategori'] ?? '');
            if (function_exists('mb_stripos')) {
                $bulundu = (mb_stripos($birlestirilmisMetin, $aramaMetni, 0, 'UTF-8') !== false);
            } else {
                $bulundu = (stripos($birlestirilmisMetin, $aramaMetni) !== false);
            }
            if (!$bulundu) {
                $aramaUygun = false;
            }
        }

        // Tüm filtrelere uyuyorsa sonuç listesine ekle
        if ($kategoriUygun && $durumUygun && $oncelikUygun && $aramaUygun) {
            $sonuclar[] = $gorev;
        }
    }

    // Sıralama (Sorting) işlemi
    if (!empty($siralama) && count($sonuclar) > 1) {
        $oncelikAgirlik = ['Yüksek' => 3, 'Orta' => 2, 'Düşük' => 1];

        usort($sonuclar, function ($a, $b) use ($siralama, $oncelikAgirlik) {
            switch ($siralama) {
                case 'tarih_artan':
                    return strcmp($a['teslim_tarihi'] ?? '', $b['teslim_tarihi'] ?? '');
                case 'tarih_azalan':
                    return strcmp($b['teslim_tarihi'] ?? '', $a['teslim_tarihi'] ?? '');
                case 'oncelik':
                    $agirlikA = $oncelikAgirlik[$a['oncelik'] ?? 'Düşük'] ?? 1;
                    $agirlikB = $oncelikAgirlik[$b['oncelik'] ?? 'Düşük'] ?? 1;
                    if ($agirlikA === $agirlikB) {
                        return strcmp($a['teslim_tarihi'] ?? '', $b['teslim_tarihi'] ?? '');
                    }
                    return $agirlikB <=> $agirlikA;
                case 'baslik_az':
                    return strcasecmp($a['baslik'] ?? '', $b['baslik'] ?? '');
                default:
                    return 0;
            }
        });
    }

    return $sonuclar;
}

/**
 * 3.1. ID İLE TEKİL GÖREV BULMA FONKSİYONU
 * Düzenleme sayfasında ilgili görevin mevcut bilgilerini getirmek için kullanılır.
 *
 * @param int $id Görev kimlik numarası
 * @return array|null Görev dizisi veya bulunamazsa null
 */
function gorevBul($id) {
    $id = (int)$id;
    $tumGorevler = $_SESSION['gorevler'] ?? [];
    foreach ($tumGorevler as $gorev) {
        if ((int)$gorev['id'] === $id) {
            return $gorev;
        }
    }
    return null;
}

/**
 * 4. YENİ KAYIT EKLEME VE VERİ DOĞRULAMA (VALIDATION) FONKSİYONU
 * Kullanıcıdan gelen verileri kontrol eder.
 * Zorunlu alanlar boşsa hata üretir. Hata yoksa yeni görevi $_SESSION ve JSON dosyasına kaydeder.
 *
 * @param array $veri Formdan gelen $_POST verileri
 * @return array [ 'basarili' => bool, 'hatalar' => array ]
 */
function gorevEkle($veri) {
    $hatalar = [];

    // Verileri temizleyelim ve alalım
    $baslik       = trim($veri['baslik'] ?? '');
    $kategori     = trim($veri['kategori'] ?? '');
    $oncelik      = trim($veri['oncelik'] ?? 'Orta');
    $durum        = trim($veri['durum'] ?? 'Bekliyor');
    $teslimTarihi = trim($veri['teslim_tarihi'] ?? '');
    $aciklama     = trim($veri['aciklama'] ?? '');

    // KURAL 1: Görev Başlığı boş olamaz ve en az 3 karakter olmalı
    if (empty($baslik)) {
        $hatalar[] = 'Görev başlığı alanı boş bırakılamaz.';
    } elseif (mb_strlen($baslik, 'UTF-8') < 3) {
        $hatalar[] = 'Görev başlığı en az 3 karakterden oluşmalıdır.';
    }

    // KURAL 2: Kategori / Ders boş olamaz
    if (empty($kategori)) {
        $hatalar[] = 'Lütfen bir ders veya kategori seçiniz/giriniz.';
    }

    // KURAL 3: Öncelik geçerli değerlerden biri olmalı
    $gecerliOncelikler = ['Düşük', 'Orta', 'Yüksek'];
    if (!in_array($oncelik, $gecerliOncelikler)) {
        $hatalar[] = 'Geçersiz öncelik seviyesi seçildi.';
    }

    // KURAL 4: Durum geçerli değerlerden biri olmalı
    $gecerliDurumlar = ['Bekliyor', 'Devam Ediyor', 'Tamamlandı'];
    if (!in_array($durum, $gecerliDurumlar)) {
        $hatalar[] = 'Geçersiz görev durumu seçildi.';
    }

    // KURAL 5: Açıklama boş olamaz ve en az 5 karakter olmalı
    if (empty($aciklama)) {
        $hatalar[] = 'Açıklama alanı boş bırakılamaz.';
    } elseif (mb_strlen($aciklama, 'UTF-8') < 5) {
        $hatalar[] = 'Açıklama alanı en az 5 karakter olmalıdır.';
    }

    // Eğer hata varsa işlemi durdur ve hataları döndür
    if (!empty($hatalar)) {
        return [
            'basarili' => false,
            'hatalar'  => $hatalar
        ];
    }

    // Yeni benzersiz ID üretme (Mevcut en büyük ID + 1)
    $yeniId = 1;
    if (!empty($_SESSION['gorevler'])) {
        $mevcutIdler = array_column($_SESSION['gorevler'], 'id');
        $yeniId = max($mevcutIdler) + 1;
    }

    // Yeni kayıt dizisini oluştur
    $yeniGorev = [
        'id'            => $yeniId,
        'baslik'        => $baslik,
        'kategori'      => $kategori,
        'oncelik'       => $oncelik,
        'durum'         => $durum,
        'teslim_tarihi' => !empty($teslimTarihi) ? $teslimTarihi : date('Y-m-d'),
        'aciklama'      => $aciklama
    ];

    // Yeni kaydı oturumdaki listenin en başına ekle ve yerel JSON dosyasına kaydet
    array_unshift($_SESSION['gorevler'], $yeniGorev);
    verileriDosyayaKaydet($_SESSION['gorevler']);

    return [
        'basarili' => true,
        'hatalar'  => []
    ];
}

/**
 * 4.1. MEVCUT KAYDI GÜNCELLEME VE DOĞRULAMA FONKSİYONU (CRUD - Update)
 * Verilen ID numaralı görevin bilgilerini doğrulayarak günceller.
 *
 * @param int   $id   Güncellenecek görevin kimlik numarası
 * @param array $veri Formdan gelen $_POST verileri
 * @return array [ 'basarili' => bool, 'hatalar' => array ]
 */
function gorevGuncelle($id, $veri) {
    $id = (int)$id;
    $hatalar = [];

    $baslik       = trim($veri['baslik'] ?? '');
    $kategori     = trim($veri['kategori'] ?? '');
    $oncelik      = trim($veri['oncelik'] ?? 'Orta');
    $durum        = trim($veri['durum'] ?? 'Bekliyor');
    $teslimTarihi = trim($veri['teslim_tarihi'] ?? '');
    $aciklama     = trim($veri['aciklama'] ?? '');

    if (empty($baslik)) {
        $hatalar[] = 'Görev başlığı alanı boş bırakılamaz.';
    } elseif (mb_strlen($baslik, 'UTF-8') < 3) {
        $hatalar[] = 'Görev başlığı en az 3 karakterden oluşmalıdır.';
    }

    if (empty($kategori)) {
        $hatalar[] = 'Lütfen bir ders veya kategori seçiniz/giriniz.';
    }

    $gecerliOncelikler = ['Düşük', 'Orta', 'Yüksek'];
    if (!in_array($oncelik, $gecerliOncelikler)) {
        $hatalar[] = 'Geçersiz öncelik seviyesi seçildi.';
    }

    $gecerliDurumlar = ['Bekliyor', 'Devam Ediyor', 'Tamamlandı'];
    if (!in_array($durum, $gecerliDurumlar)) {
        $hatalar[] = 'Geçersiz görev durumu seçildi.';
    }

    if (empty($aciklama)) {
        $hatalar[] = 'Açıklama alanı boş bırakılamaz.';
    } elseif (mb_strlen($aciklama, 'UTF-8') < 5) {
        $hatalar[] = 'Açıklama alanı en az 5 karakter olmalıdır.';
    }

    if (!empty($hatalar)) {
        return [
            'basarili' => false,
            'hatalar'  => $hatalar
        ];
    }

    $bulundu = false;
    if (!empty($_SESSION['gorevler']) && is_array($_SESSION['gorevler'])) {
        foreach ($_SESSION['gorevler'] as $indeks => $gorev) {
            if ((int)$gorev['id'] === $id) {
                $_SESSION['gorevler'][$indeks] = [
                    'id'            => $id,
                    'baslik'        => $baslik,
                    'kategori'      => $kategori,
                    'oncelik'       => $oncelik,
                    'durum'         => $durum,
                    'teslim_tarihi' => !empty($teslimTarihi) ? $teslimTarihi : date('Y-m-d'),
                    'aciklama'      => $aciklama
                ];
                $bulundu = true;
                break;
            }
        }
    }

    if (!$bulundu) {
        return [
            'basarili' => false,
            'hatalar'  => ['Güncellenmek istenen görev sistemde bulunamadı.']
        ];
    }

    verileriDosyayaKaydet($_SESSION['gorevler']);

    return [
        'basarili' => true,
        'hatalar'  => []
    ];
}

/**
 * 4.2. TEK TIKLA HIZLI DURUM DEĞİŞTİRME FONKSİYONU
 * Görevin durumunu doğrudan verilen değere veya sıradaki aşamaya geçirir:
 * Bekliyor -> Devam Ediyor -> Tamamlandı -> Bekliyor
 *
 * @param int    $id        Görev ID
 * @param string $yeniDurum İsteğe bağlı hedef durum
 * @return bool Başarılıysa true
 */
function gorevDurumDegistir($id, $yeniDurum = '') {
    if (!isset($_SESSION['gorevler']) || !is_array($_SESSION['gorevler'])) {
        return false;
    }

    $id = (int)$id;
    $gecerliDurumlar = ['Bekliyor', 'Devam Ediyor', 'Tamamlandı'];

    foreach ($_SESSION['gorevler'] as $indeks => $gorev) {
        if ((int)$gorev['id'] === $id) {
            if (!empty($yeniDurum) && in_array($yeniDurum, $gecerliDurumlar, true)) {
                $_SESSION['gorevler'][$indeks]['durum'] = $yeniDurum;
            } else {
                // Otomatik döngüsel geçiş
                $mevcut = $gorev['durum'] ?? 'Bekliyor';
                if ($mevcut === 'Bekliyor') {
                    $_SESSION['gorevler'][$indeks]['durum'] = 'Devam Ediyor';
                } elseif ($mevcut === 'Devam Ediyor') {
                    $_SESSION['gorevler'][$indeks]['durum'] = 'Tamamlandı';
                } else {
                    $_SESSION['gorevler'][$indeks]['durum'] = 'Bekliyor';
                }
            }
            verileriDosyayaKaydet($_SESSION['gorevler']);
            return true;
        }
    }

    return false;
}

/**
 * 5. KAYIT SİLME FONKSİYONU
 * Verilen ID numarasına sahip görevi listeden ve yerel JSON dosyasından siler.
 *
 * @param int $id Silinecek görevin kimlik numarası
 * @return bool Silme başarılıysa true, bulunamadıysa false
 */
function gorevSil($id) {
    if (!isset($_SESSION['gorevler']) || !is_array($_SESSION['gorevler'])) {
        return false;
    }

    $id = (int)$id;
    $silindiMi = false;

    foreach ($_SESSION['gorevler'] as $indeks => $gorev) {
        if ((int)$gorev['id'] === $id) {
            unset($_SESSION['gorevler'][$indeks]);
            $silindiMi = true;
            break;
        }
    }

    // Dizi indekslerini yeniden sırala ve JSON dosyasını güncelle
    if ($silindiMi) {
        verileriDosyayaKaydet($_SESSION['gorevler']);
    }

    return $silindiMi;
}

/**
 * 6. ÖZET BİLGİ, SAYAÇ VE İLERLEME YÜZDESİ HESAPLAMA FONKSİYONU
 * Toplam, bekleyen, devam eden, tamamlanan, acil, geciken görev sayılarını
 * ve genel tamamlanma yüzdesini hesaplar.
 *
 * @return array İstatistik sayaçları
 */
function istatistikleriHesapla() {
    $gorevler = $_SESSION['gorevler'] ?? [];
    $toplam   = count($gorevler);
    $bugun    = strtotime(date('Y-m-d'));

    $istatistik = [
        'toplam'           => $toplam,
        'bekleyen'         => 0,
        'devam_eden'       => 0,
        'tamamlanan'       => 0,
        'yuksek_oncelik'   => 0,
        'geciken'          => 0,
        'tamamlanma_orani' => 0
    ];

    foreach ($gorevler as $g) {
        if ($g['durum'] === 'Bekliyor') {
            $istatistik['bekleyen']++;
        } elseif ($g['durum'] === 'Devam Ediyor') {
            $istatistik['devam_eden']++;
        } elseif ($g['durum'] === 'Tamamlandı') {
            $istatistik['tamamlanan']++;
        }

        if ($g['oncelik'] === 'Yüksek') {
            $istatistik['yuksek_oncelik']++;
        }

        if ($g['durum'] !== 'Tamamlandı' && !empty($g['teslim_tarihi'])) {
            $hedef = strtotime(date('Y-m-d', strtotime($g['teslim_tarihi'])));
            if ($hedef && $hedef < $bugun) {
                $istatistik['geciken']++;
            }
        }
    }

    if ($toplam > 0) {
        $istatistik['tamamlanma_orani'] = (int)round(($istatistik['tamamlanan'] / $toplam) * 100);
    }

    return $istatistik;
}

/**
 * 7. MEVCUT KATEGORİLERİ DÖNDÜREN YARDIMCI FONKSİYON
 * Filtreleme menüsünde ve form seçiminde kullanmak için kategorileri listeler.
 *
 * @return array Benzersiz kategori listesi
 */
function kategorileriGetir() {
    $varsayilanKategoriler = [
        'İnternet Programcılığı I',
        'Veritabanı Yönetimi',
        'Veri Yapıları',
        'Web Tasarımı',
        'Yapay Zekâya Giriş',
        'Mesleki Yabancı Dil',
        'Genel / Kişisel'
    ];

    if (!empty($_SESSION['gorevler'])) {
        $gorevKategorileri = array_column($_SESSION['gorevler'], 'kategori');
        $birlestirilmis = array_unique(array_merge($varsayilanKategoriler, $gorevKategorileri));
        sort($birlestirilmis);
        return $birlestirilmis;
    }

    return $varsayilanKategoriler;
}

/**
 * 8. GÖRSEL ROZET (BADGE) FONKSİYONLARI
 * Görevin durumuna ve önceliğine göre renkli CSS rozeti üretir.
 */
function durumRozeti($durum) {
    switch ($durum) {
        case 'Tamamlandı':
            return '<span class="badge badge-success"><span class="badge-dot"></span>Tamamlandı</span>';
        case 'Devam Ediyor':
            return '<span class="badge badge-warning"><span class="badge-dot"></span>Devam Ediyor</span>';
        case 'Bekliyor':
        default:
            return '<span class="badge badge-secondary"><span class="badge-dot"></span>Bekliyor</span>';
    }
}

function oncelikRozeti($oncelik) {
    switch ($oncelik) {
        case 'Yüksek':
            return '<span class="badge badge-danger">Yüksek Öncelik</span>';
        case 'Orta':
            return '<span class="badge badge-info">Orta Öncelik</span>';
        case 'Düşük':
        default:
            return '<span class="badge badge-muted">Düşük Öncelik</span>';
    }
}

/**
 * 9. TARİH FORMATLAMA FONKSİYONU
 * YYYY-AA-GG formatındaki tarihi GG.AA.YYYY formatına dönüştürür.
 */
function tarihFormati($tarih) {
    if (empty($tarih)) return '-';
    $zaman = strtotime($tarih);
    return $zaman ? date('d.m.Y', $zaman) : guvenli($tarih);
}

/**
 * 10. TESLİM TARİHİ GERİ SAYIM VE GECİKME UYARI ROZETİ
 * Görevin teslim tarihine kalan gün sayısını hesaplar ve durumuna göre
 * dinamik renkli bir uyarı/bilgi rozeti üretir.
 *
 * @param string $tarih YYYY-MM-DD formatında teslim tarihi
 * @param string $durum Görevin mevcut durumu
 * @return string HTML rozet çıktısı
 */
function kalanGunRozeti($tarih, $durum = '') {
    if ($durum === 'Tamamlandı') {
        return '<span class="deadline-chip deadline-done">Tamamlandı</span>';
    }

    if (empty($tarih)) {
        return '';
    }

    $bugun = strtotime(date('Y-m-d'));
    $hedef = strtotime(date('Y-m-d', strtotime($tarih)));

    if (!$hedef) {
        return '';
    }

    $farkSaniye = $hedef - $bugun;
    $farkGun    = (int)round($farkSaniye / 86400);

    if ($farkGun < 0) {
        $gecikenGun = abs($farkGun);
        return '<span class="deadline-chip deadline-overdue">' . $gecikenGun . ' gün gecikti</span>';
    } elseif ($farkGun === 0) {
        return '<span class="deadline-chip deadline-today">Bugün son gün</span>';
    } elseif ($farkGun === 1) {
        return '<span class="deadline-chip deadline-soon">Yarın teslim</span>';
    } elseif ($farkGun <= 3) {
        return '<span class="deadline-chip deadline-soon">Son ' . $farkGun . ' gün</span>';
    } else {
        return '<span class="deadline-chip deadline-normal">' . $farkGun . ' gün kaldı</span>';
    }
}

/**
 * ============================================================================
 * 11. KULLANICI GİRİŞ, KAYIT VE YETKİLENDİRME (AUTH) FONKSİYONLARI
 * Kullanıcı hesapları yerel "data/kullanicilar.json" dosyasında şifreleri
 * password_hash() ile güvenli şekilde şifrelenmiş olarak saklanır.
 * ============================================================================
 */
function kullaniciDosyasiYolu() {
    $klasor = __DIR__ . '/data';
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0777, true);
    }
    return $klasor . '/kullanicilar.json';
}

function kullanicilariGetir() {
    $dosya = kullaniciDosyasiYolu();
    if (file_exists($dosya)) {
        $icerik = @file_get_contents($dosya);
        $liste  = !empty($icerik) ? json_decode($icerik, true) : null;
        if (is_array($liste) && !empty($liste)) {
            return $liste;
        }
    }

    // Varsayılan Demo Öğrenci Hesabı (ogrenci@edutask.com / 123456)
    $varsayilanKullanicilar = [
        [
            'id'         => 1,
            'ad_soyad'   => 'Demo Öğrenci',
            'eposta'     => 'ogrenci@edutask.com',
            'bolum'      => 'Bilgisayar Programcılığı',
            'sifre_hash' => password_hash('123456', PASSWORD_DEFAULT),
            'kayit_tarih'=> date('Y-m-d')
        ]
    ];

    kullanicilariKaydet($varsayilanKullanicilar);
    return $varsayilanKullanicilar;
}

function kullanicilariKaydet($kullanicilar) {
    $dosya = kullaniciDosyasiYolu();
    $json  = json_encode(array_values($kullanicilar), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return (@file_put_contents($dosya, $json) !== false);
}

/**
 * Kullanıcı giriş doğrulaması yapar (POST).
 */
function kullaniciGirisYap($eposta, $sifre, $hatirla = true) {
    $eposta = strtolower(trim((string)$eposta));
    $sifre  = (string)$sifre;

    if (empty($eposta) || empty($sifre)) {
        return [
            'basarili' => false,
            'hata'     => 'Lütfen e-posta adresinizi ve şifrenizi giriniz.'
        ];
    }

    $kullanicilar = kullanicilariGetir();
    foreach ($kullanicilar as $k) {
        if (strtolower($k['eposta']) === $eposta && password_verify($sifre, $k['sifre_hash'])) {
            session_regenerate_id(true);
            $_SESSION['kullanici'] = [
                'id'       => $k['id'],
                'ad_soyad' => $k['ad_soyad'],
                'eposta'   => $k['eposta'],
                'bolum'    => $k['bolum'] ?? 'Öğrenci'
            ];
            if ($hatirla) {
                hatirlaTokenOlustur($k['id']); // Tarayıcı kapansa da oturum açık kalsın
            }
            return [
                'basarili' => true,
                'hata'     => ''
            ];
        }
    }

    return [
        'basarili' => false,
        'hata'     => 'E-posta adresi veya şifre hatalı. Lütfen kontrol edip tekrar deneyiniz.'
    ];
}

/**
 * Yeni öğrenci hesabı oluşturur ve otomatik oturum açar (POST).
 */
function kullaniciKayitOl($veri) {
    $hatalar     = [];
    $adSoyad     = trim($veri['ad_soyad'] ?? '');
    $eposta      = strtolower(trim($veri['eposta'] ?? ''));
    $bolum       = trim($veri['bolum'] ?? 'Bilgisayar Programcılığı');
    $sifre       = (string)($veri['sifre'] ?? '');
    $sifreTekrar = (string)($veri['sifre_tekrar'] ?? '');

    if (empty($adSoyad) || mb_strlen($adSoyad, 'UTF-8') < 3) {
        $hatalar[] = 'Ad Soyad alanı en az 3 karakter olmalıdır.';
    }

    if (empty($eposta) || !filter_var($eposta, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Lütfen geçerli bir e-posta adresi giriniz.';
    }

    if (empty($sifre) || strlen($sifre) < 6) {
        $hatalar[] = 'Şifreniz en az 6 karakterden oluşmalıdır.';
    } elseif ($sifre !== $sifreTekrar) {
        $hatalar[] = 'Girdiğiniz şifreler birbiriyle eşleşmiyor.';
    }

    $kullanicilar = kullanicilariGetir();
    foreach ($kullanicilar as $k) {
        if (strtolower($k['eposta']) === $eposta) {
            $hatalar[] = 'Bu e-posta adresiyle kayıtlı bir hesap zaten mevcut.';
            break;
        }
    }

    if (!empty($hatalar)) {
        return [
            'basarili' => false,
            'hatalar'  => $hatalar
        ];
    }

    $yeniId = 1;
    if (!empty($kullanicilar)) {
        $idler  = array_column($kullanicilar, 'id');
        $yeniId = max($idler) + 1;
    }

    $yeniKullanici = [
        'id'          => $yeniId,
        'ad_soyad'    => $adSoyad,
        'eposta'      => $eposta,
        'bolum'       => !empty($bolum) ? $bolum : 'Öğrenci',
        'sifre_hash'  => password_hash($sifre, PASSWORD_DEFAULT),
        'kayit_tarih' => date('Y-m-d')
    ];

    $kullanicilar[] = $yeniKullanici;
    kullanicilariKaydet($kullanicilar);

    // Kayıt sonrası otomatik giriş yap
    session_regenerate_id(true);
    $_SESSION['kullanici'] = [
        'id'       => $yeniKullanici['id'],
        'ad_soyad' => $yeniKullanici['ad_soyad'],
        'eposta'   => $yeniKullanici['eposta'],
        'bolum'    => $yeniKullanici['bolum']
    ];

    // Yeni hesap kalıcı olarak hatırlansın (site kapatılıp açılınca tekrar kayıt gerekmez)
    hatirlaTokenOlustur($yeniKullanici['id']);

    return [
        'basarili' => true,
        'hatalar'  => []
    ];
}

/**
 * Oturum açılmamışsa kullanıcıyı login.php sayfasına yönlendirir.
 */
function oturumKontrol() {
    if (!isset($_SESSION['kullanici']) || !is_array($_SESSION['kullanici'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Aktif oturumdaki kullanıcı bilgilerini döndürür.
 */
function aktifKullanici() {
    return $_SESSION['kullanici'] ?? null;
}

/**
 * ============================================================================
 * 12. "BENİ HATIRLA" — KALICI OTURUM
 * PHP oturum çerezi tarayıcı kapanınca silinir. Kullanıcının siteyi kapatıp
 * açtığında tekrar giriş/kayıt yapmak zorunda kalmaması için 30 günlük güvenli
 * bir "hatırla" çerezi kullanılır. Çerezde yalnızca rastgele bir anahtar bulunur;
 * sunucu tarafında (data/oturumlar.json) bu anahtarın SHA-256 özeti saklanır.
 * ============================================================================
 */
define('HATIRLA_CEREZ', 'edutask_hatirla');
define('HATIRLA_SURE', 60 * 60 * 24 * 30); // 30 gün

function hatirlaDosyasiYolu() {
    $klasor = __DIR__ . '/data';
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0777, true);
    }
    return $klasor . '/oturumlar.json';
}

function hatirlaKayitlariGetir() {
    $dosya = hatirlaDosyasiYolu();
    if (!file_exists($dosya)) return [];
    $liste = json_decode((string)@file_get_contents($dosya), true);
    if (!is_array($liste)) return [];
    // Süresi dolmuş kayıtları ayıkla
    return array_values(array_filter($liste, function ($k) {
        return isset($k['son_kullanma']) && $k['son_kullanma'] > time();
    }));
}

function hatirlaKayitlariKaydet($liste) {
    @file_put_contents(hatirlaDosyasiYolu(), json_encode(array_values($liste), JSON_PRETTY_PRINT), LOCK_EX);
}

/** Çerezin geçerli olacağı yol (ör. /vizeodevi/) */
function hatirlaCerezYolu() {
    $yol = str_replace(chr(92), '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')); // chr(92) = "\"
    return rtrim($yol, '/') . '/';
}

function hatirlaCerezYaz($deger, $sonKullanma) {
    setcookie(HATIRLA_CEREZ, $deger, [
        'expires'  => $sonKullanma,
        'path'     => hatirlaCerezYolu(),
        'httponly' => true,           // JavaScript erişemez
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}

/** Giriş/kayıt sonrası kalıcı oturum anahtarı oluşturur. */
function hatirlaTokenOlustur($kullaniciId) {
    $secici      = bin2hex(random_bytes(9));
    $dogrulayici = bin2hex(random_bytes(32));
    $sonKullanma = time() + HATIRLA_SURE;

    $liste   = hatirlaKayitlariGetir();
    $liste[] = [
        'secici'           => $secici,
        'dogrulayici_hash' => hash('sha256', $dogrulayici),
        'kullanici_id'     => (int)$kullaniciId,
        'son_kullanma'     => $sonKullanma,
    ];
    hatirlaKayitlariKaydet($liste);
    hatirlaCerezYaz($secici . ':' . $dogrulayici, $sonKullanma);
}

/** Çıkışta anahtarı hem sunucudan hem tarayıcıdan siler. */
function hatirlaSil() {
    $cerez = $_COOKIE[HATIRLA_CEREZ] ?? '';
    if ($cerez !== '' && strpos($cerez, ':') !== false) {
        [$secici] = explode(':', $cerez, 2);
        $liste = array_filter(hatirlaKayitlariGetir(), function ($k) use ($secici) {
            return $k['secici'] !== $secici;
        });
        hatirlaKayitlariKaydet($liste);
    }
    hatirlaCerezYaz('', time() - 3600);
    unset($_COOKIE[HATIRLA_CEREZ]);
}

/**
 * Oturum yoksa ama geçerli bir "hatırla" çerezi varsa kullanıcıyı otomatik
 * olarak giriş yaptırır. Güvenlik için anahtar her kullanımda yenilenir.
 */
function hatirlaIleGiris() {
    if (!empty($_SESSION['kullanici']) || empty($_COOKIE[HATIRLA_CEREZ])) {
        return;
    }

    $parcalar = explode(':', (string)$_COOKIE[HATIRLA_CEREZ], 2);
    if (count($parcalar) !== 2) {
        hatirlaCerezYaz('', time() - 3600);
        return;
    }
    [$secici, $dogrulayici] = $parcalar;

    $liste = hatirlaKayitlariGetir();
    foreach ($liste as $i => $kayit) {
        if (!hash_equals($kayit['secici'], $secici)) continue;

        // Anahtar tek kullanımlık: eşleşse de eşleşmese de eskisini kaldır
        unset($liste[$i]);
        hatirlaKayitlariKaydet($liste);

        if (!hash_equals($kayit['dogrulayici_hash'], hash('sha256', $dogrulayici))) {
            break; // Sahte/çalıntı anahtar
        }

        foreach (kullanicilariGetir() as $k) {
            if ((int)$k['id'] === (int)$kayit['kullanici_id']) {
                session_regenerate_id(true);
                $_SESSION['kullanici'] = [
                    'id'       => $k['id'],
                    'ad_soyad' => $k['ad_soyad'],
                    'eposta'   => $k['eposta'],
                    'bolum'    => $k['bolum'] ?? 'Öğrenci'
                ];
                hatirlaTokenOlustur($k['id']);
                return;
            }
        }
        break;
    }

    hatirlaCerezYaz('', time() - 3600);
}

// Her sayfa açılışında kalıcı oturumu kontrol et
hatirlaIleGiris();
