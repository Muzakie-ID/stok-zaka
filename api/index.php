<?php

/**
 * Entrypoint serverless Vercel (PHP runtime).
 *
 * Vercel menjalankan file ini untuk semua request dinamis.
 * Semua request yang tidak cocok dengan file statis di `public/`
 * akan diteruskan ke sini dan diserahkan ke front controller Laravel.
 */

declare(strict_types=1);

// Vercel PHP runtime mengharapkan path relatif terhadap api/
require __DIR__.'/../public/index.php';
