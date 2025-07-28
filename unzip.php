<?php
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '25M');
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 300);

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_file'])) {
    if ($_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
        $statusMessage = "<p style='color:red;'>❌ Upload error: " . $_FILES['zip_file']['error'] . "</p>";
    } elseif ($_FILES['zip_file']['size'] > 20 * 1024 * 1024) {
        $statusMessage = "<p style='color:red;'>❌ File terlalu besar. Maksimal 20MB.</p>";
    } else {
        $zipFile = $_FILES['zip_file']['tmp_name'];
        $zip = new ZipArchive;

        $res = $zip->open($zipFile);
        if ($res === TRUE) {
            $basePath = __DIR__;
            $firstEntry = $zip->getNameIndex(0);
            $baseFolder = (substr($firstEntry, -1) === '/') ? $firstEntry : '';

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (substr($entryName, -1) === '/') continue;

                $relativePath = $entryName;
                if ($baseFolder && strpos($entryName, $baseFolder) === 0) {
                    $relativePath = substr($entryName, strlen($baseFolder));
                }

                // Bersihkan path untuk menghindari eksploitasi
                $relativePath = str_replace(['..\\', '../', '..'], '', $relativePath);
                $targetPath = $basePath . '/' . $relativePath;
                $targetDir = dirname($targetPath);

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $stream = $zip->getStream($entryName);
                if ($stream) {
                    file_put_contents($targetPath, stream_get_contents($stream));
                    fclose($stream);
                }
            }

            $zip->close();
            $statusMessage = "<p style='color:green;'>✅ Sukses! ZIP berhasil diekstrak.</p>";
        } else {
            $statusMessage = "<p style='color:red;'>❌ Gagal membuka ZIP. Kode error: $res</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload & Ekstrak ZIP</title>
</head>
<body>
    <h2>Upload ZIP</h2>
    <?php if (!empty($statusMessage)) echo $statusMessage; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="zip_file" accept=".zip" required>
        <br><br>
        <input type="submit" value="Unggah & Ekstrak">
    </form>
</body>
</html>
