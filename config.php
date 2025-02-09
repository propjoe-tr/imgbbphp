<?php
// Hata raporlamayı devre dışı bırak (canlı ortam için)
error_reporting(0);

// API Yapılandırması
define('IMGBB_API_KEY', 'API_ANAHTARINIZ');

// Uygulama Yapılandırması
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Site Yapılandırması
define('SITE_NAME', 'Resim Yükleme Servisi');
define('SITE_DESCRIPTION', 'Hızlı ve güvenli resim yükleme servisi'); 