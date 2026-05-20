<?php
// =============================================
//  upload.php - Pengelola Berkas Foto
// =============================================

$upload_dir = "uploads/";

// Buat folder uploads jika belum ada
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'upload';

// ─── Handle Actions via GET ─────────────────────────────────────────────────

// Daftar berkas
if ($action === 'list') {
    header('Content-Type: application/json');
    $files = [];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    if (is_dir($upload_dir)) {
        foreach (scandir($upload_dir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) continue;
            $filepath = $upload_dir . $file;
            $files[] = [
                'name' => $file,
                'size' => filesize($filepath),
                'modified' => filemtime($filepath),
            ];
        }
        // Urutkan: terbaru dulu
        usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
    }
    echo json_encode(['files' => $files]);
    exit;
}

// Unduh berkas
if ($action === 'download' && isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = $upload_dir . $filename;
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext) || !file_exists($filepath)) {
        http_response_code(404);
        echo "Berkas tidak ditemukan.";
        exit;
    }

    $mime_types = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'bmp'  => 'image/bmp',
    ];

    header('Content-Type: ' . ($mime_types[$ext] ?? 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache');
    readfile($filepath);
    exit;
}

// Hapus berkas
if ($action === 'delete' && isset($_GET['file'])) {
    header('Content-Type: application/json');
    $filename = basename($_GET['file']);
    $filepath = $upload_dir . $filename;
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Tipe berkas tidak diizinkan.']);
        exit;
    }

    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'message' => 'Berkas tidak ditemukan.']);
        exit;
    }

    if (unlink($filepath)) {
        echo json_encode(['success' => true, 'message' => 'Berkas berhasil dihapus.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus berkas.']);
    }
    exit;
}

// ─── Handle Upload (POST) ────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {

    $uploadOk = 1;
    $allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    $original_name = basename($_FILES['fileToUpload']['name']);
    // Sanitasi nama berkas
    $safe_name   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
    $target_file = $upload_dir . $safe_name;
    $fileExt     = strtolower(pathinfo($safe_name, PATHINFO_EXTENSION));

    // 1. Validasi ekstensi
    if (!in_array($fileExt, $allowed_ext)) {
        echo "Maaf, hanya berkas foto (JPG, JPEG, PNG, GIF, WEBP, BMP) yang diperbolehkan.";
        $uploadOk = 0;
    }

    // 2. Validasi MIME type nyata (bukan hanya ekstensi)
    if ($uploadOk) {
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $_FILES['fileToUpload']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($realMime, $allowed_mime)) {
            echo "Maaf, berkas bukan foto yang valid.";
            $uploadOk = 0;
        }
    }

    // 3. Validasi getimagesize (pastikan benar-benar gambar)
    if ($uploadOk) {
        $imgInfo = @getimagesize($_FILES['fileToUpload']['tmp_name']);
        if ($imgInfo === false) {
            echo "Maaf, berkas bukan gambar yang dapat dibaca.";
            $uploadOk = 0;
        }
    }

    // 4. Periksa ukuran
    if ($uploadOk && $_FILES['fileToUpload']['size'] > $max_size) {
        echo "Maaf, ukuran berkas terlalu besar (maks. 5MB).";
        $uploadOk = 0;
    }

    // 5. Periksa jika berkas sudah ada — tambahkan suffix
    if ($uploadOk && file_exists($target_file)) {
        $name_no_ext = pathinfo($safe_name, PATHINFO_FILENAME);
        $counter = 1;
        do {
            $safe_name   = $name_no_ext . '_' . $counter . '.' . $fileExt;
            $target_file = $upload_dir . $safe_name;
            $counter++;
        } while (file_exists($target_file));
    }

    // 6. Proses upload
    if ($uploadOk) {
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
            echo "Berkas " . htmlspecialchars($safe_name) . " telah diunggah.";
        } else {
            echo "Maaf, terjadi kesalahan saat mengunggah berkas Anda.";
        }
    } else {
        echo "Maaf, berkas Anda tidak dapat diunggah.";
    }
    exit;
}

// Fallback – tampilkan index
header('Location: index.html');
exit;
?>