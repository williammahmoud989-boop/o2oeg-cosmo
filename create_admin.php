<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    User::updateOrCreate(
        ['email' => 'o2oegcosmo@gmail.com'],
        [
            'name' => 'Admin User',
            'password' => Hash::make('Amzabola@12345678'),
            'is_admin' => true,
        ]
    );
    echo "USER_CREATED_SUCCESSFULLY\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
