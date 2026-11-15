<?php
/**
 * ============================================================================
 * PROJE: EduTask AI Studio - Akademik Görev ve Proje Yönetim Platformu
 * DOSYA: cikis.php
 * AÇIKLAMA: Aktif kullanıcı oturumunu sonlandırır ve login sayfasına yönlendirir.
 * ============================================================================
 */

require_once __DIR__ . '/functions.php';

hatirlaSil(); // Kalıcı "beni hatırla" oturumunu da kapat
unset($_SESSION['kullanici']);

header("Location: login.php?mesaj=cikis_yapildi");
exit;
