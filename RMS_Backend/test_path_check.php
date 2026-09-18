<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doc = App\Models\Document::find(1);
if (!$doc) {
    echo "Document not found\n";
    exit;
}
echo "Document: " . $doc->original_filename . "\n";
echo "Stored path: " . $doc->stored_path . "\n";
echo "Mime type: " . $doc->mime_type . "\n";

$path1 = storage_path('app/private/' . $doc->stored_path);
$path2 = storage_path('app/' . $doc->stored_path);
echo "Path1: " . $path1 . "\n";
echo "Path1 exists: " . (file_exists($path1) ? 'YES' : 'NO') . "\n";
echo "Path2: " . $path2 . "\n";
echo "Path2 exists: " . (file_exists($path2) ? 'YES' : 'NO') . "\n";

// Also check extension/mime
$ext = strtolower(pathinfo($doc->original_filename, PATHINFO_EXTENSION));
echo "Extension: " . $ext . "\n";
$extMimes = [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'doc' => 'application/msword',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
];
$mime = $extMimes[$ext] ?? ($doc->mime_type ?: 'application/octet-stream');
echo "Resolved MIME: " . $mime . "\n";

// Try to open and read the file
$activePath = file_exists($path1) ? $path1 : $path2;
if (file_exists($activePath)) {
    $stream = fopen($activePath, 'rb');
    if ($stream) {
        echo "File can be opened: YES\n";
        echo "File size: " . filesize($activePath) . " bytes\n";
        $data = fread($stream, 4);
        echo "First 4 bytes (hex): " . bin2hex($data) . "\n";
        fclose($stream);
    } else {
        echo "File cannot be opened!\n";
    }
} else {
    echo "File not found on disk!\n";
}
