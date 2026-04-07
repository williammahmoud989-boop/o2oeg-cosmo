<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$salon = \App\Models\Salon::where('name', 'like', '%رشا%')->orWhere('name_ar', 'like', '%رشا%')->first();

if ($salon) {
    $salon->is_featured = 1;
    $salon->status = 'active';
    $salon->save();
    echo 'OK';
} else {
    echo 'x';
}
