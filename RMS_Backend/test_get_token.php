<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get a user token for testing
$user = App\Models\User::find(5);
if (!$user) {
    echo "User 5 not found\n";
    // Try to find any user
    $user = App\Models\User::first();
    if (!$user) {
        echo "No users found\n";
        exit;
    }
}

// Create a token
$token = $user->createToken('test-token')->plainTextToken;
echo "Token: " . $token . "\n";
echo "User ID: " . $user->id . "\n";
echo "User email: " . $user->email . "\n";

// Get document 1
$doc = App\Models\Document::find(1);
echo "Document ID: " . $doc->id . "\n";
echo "\n=== Test this URL with the token ===\n";
echo "curl -H \"Authorization: Bearer $token\" -H \"Accept: application/json\" http://127.0.0.1:8000/api/applicant/documents/1/download -o /tmp/test_download.pdf -v\n";
