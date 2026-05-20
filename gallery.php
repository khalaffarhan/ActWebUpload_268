<?php
// gallery.php — Halaman Daftar Berkas yang Sudah Diunggah

$upload_dir  = "uploads/";
$allowed_ext = ['jpg','jpeg','png','gif','webp','bmp'];
$files = [];

if (is_dir($upload_dir)) {
    foreach (scandir($upload_dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) continue;
        $filepath = $upload_dir . $file;
        $files[] = [
            'name'     => $file,
            'size'     => filesize($filepath),
            'modified' => filemtime($filepath),
        ];
    }
    usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
}

$total_files = count($files);
$total_size  = array_sum(array_column($files, 'size'));

function format_size($bytes) {
    if ($bytes < 1024)       return $bytes . ' B';
    if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Berkas — Galeri Foto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-badge">🗂️ Daftar Berkas</div>
        <h1>Koleksi <span>Foto</span> Saya</h1>
        <p>Kelola semua foto yang sudah diunggah</p>
    </header>

    <nav class="nav-tabs">
        <a class="nav-link" href="index.html">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Unggah Foto
        </a>
        <a class="nav-link active" href="gallery.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Daftar Berkas
            <span class="count-badge"><?= $total_files ?></span>
        </a>
    </nav>

    <div class="container">

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-num"><?= $total_files ?></span>
                <div class="stat-label">Total Foto</div>
            </div>
            <div class="stat-item">
                <span class="stat-num"><?= format_size($total_size) ?></span>
                <div class="stat-label">Total Ukuran</div>
            </div>
        </div>

        <!-- List Header -->
        <div class="list-header">
            <h2>Semua Foto</h2>
            <a href="gallery.php" class="refresh-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Perbarui
            </a>
        </div>

        <!-- Notifikasi dari aksi delete -->
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div class="alert alert-success visible" style="margin-bottom:1.2rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Foto berhasil dihapus.</span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error visible" style="margin-bottom:1.2rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12" y2="16"/></svg>
            <span>Gagal menghapus berkas.</span>
        </div>
        <?php endif; ?>

        <!-- File Grid -->
        <?php if (empty($files)): ?>
        <div class="empty-state">
            <div class="empty-icon">🖼️</div>
            <h3>Belum ada foto</h3>
            <p>Unggah foto pertama Anda dari halaman <a href="index.html" style="color:var(--accent)">Unggah Foto</a>.</p>
        </div>
        <?php else: ?>
        <div class="file-grid">
            <?php foreach ($files as $file): ?>
            <div class="file-card" id="card-<?= htmlspecialchars($file['name']) ?>">
                <img class="file-card-thumb"
                     src="<?= $upload_dir . urlencode($file['name']) ?>"
                     alt="<?= htmlspecialchars($file['name']) ?>"
                     loading="lazy"
                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22><rect fill=%22%23f5f0e8%22 width=%22200%22 height=%22150%22/><text x=%2250%%22 y=%2250%%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2230%22>🖼️</text></svg>'">
                <div class="file-card-body">
                    <div class="file-card-name" title="<?= htmlspecialchars($file['name']) ?>">
                        <?= htmlspecialchars($file['name']) ?>
                    </div>
                    <div class="file-card-size"><?= format_size($file['size']) ?></div>
                    <div class="file-card-actions">
                        <a href="upload.php?action=download&file=<?= urlencode($file['name']) ?>"
                           class="btn-sm btn-download" download>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Unduh
                        </a>
                        <button class="btn-sm btn-delete"
                                onclick="promptDelete('<?= addslashes($file['name']) ?>')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- Delete Confirm Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>🗑️ Hapus Berkas?</h3>
            <p>Berkas <strong id="deleteFileName"></strong> akan dihapus secara permanen dan tidak dapat dipulihkan.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Batal</button>
                <button class="btn-confirm-delete" id="btnConfirmDelete">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <script>
        let fileToDelete = null;

        function promptDelete(name) {
            fileToDelete = name;
            document.getElementById('deleteFileName').textContent = name;
            document.getElementById('deleteModal').classList.add('open');
        }

        function closeModal() {
            fileToDelete = null;
            document.getElementById('deleteModal').classList.remove('open');
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function () {
            if (!fileToDelete) return;
            // Hapus via AJAX lalu reload halaman
            fetch('upload.php?action=delete&file=' + encodeURIComponent(fileToDelete))
                .then(r => r.json())
                .then(data => {
                    closeModal();
                    if (data.success) {
                        window.location.href = 'gallery.php?deleted=1';
                    } else {
                        window.location.href = 'gallery.php?error=1';
                    }
                })
                .catch(() => { window.location.href = 'gallery.php?error=1'; });
        });

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>