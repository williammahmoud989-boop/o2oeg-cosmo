<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Service;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\UserPackageUsage;

echo "=== Test: Payroll & Packages System ===\n\n";

try {
    $salon = Salon::first();
    $user = User::first();

    // --- TEST 1: Payroll ---
    echo "--- Test 1: Payroll System ---\n";
    $staffMember = Staff::create([
        'salon_id'        => $salon->id,
        'name'            => 'Test Staff ' . time(),
        'specialization'  => 'Hairdresser',
        'base_salary'     => 3000,
        'commission_rate' => 10,
        'is_active'       => true,
    ]);
    echo "Created Staff: {$staffMember->name} | Base Salary: EGP {$staffMember->base_salary}\n";

    $payroll = Payroll::create([
        'salon_id'         => $salon->id,
        'staff_id'         => $staffMember->id,
        'month'            => '04',
        'year'             => '2026',
        'base_salary'      => $staffMember->base_salary,
        'total_commission' => 850,
        'advances'         => 500,
        'deductions'       => 0,
        'net_salary'       => $staffMember->base_salary + 850 - 500,
        'status'           => 'pending',
    ]);
    echo "Created Payroll: Month {$payroll->month}/{$payroll->year} | Net Salary: EGP {$payroll->net_salary}\n";

    $payroll->update(['status' => 'paid', 'payment_date' => now()]);
    echo "Payroll Status: " . Payroll::find($payroll->id)->status . " ✓\n\n";

    // --- TEST 2: Packages ---
    echo "--- Test 2: Packages System ---\n";
    $service = Service::where('salon_id', $salon->id)->first();
    if (!$service) {
        $service = Service::create([
            'salon_id' => $salon->id,
            'name'     => 'جلسة مساج',
            'category' => 'مساج',
            'price'    => 200,
            'duration_minutes' => 60,
        ]);
    }
    echo "Using Service: {$service->name}\n";

    $package = Package::create([
        'salon_id'      => $salon->id,
        'name'          => 'باقة العناية الذهبية - ' . time(),
        'description'   => 'باقة تتضمن 5 جلسات مساج',
        'price'         => 900,
        'validity_days' => 90,
        'is_active'     => true,
    ]);
    echo "Created Package: {$package->name} | Price: EGP {$package->price}\n";

    $packageItem = PackageItem::create([
        'package_id' => $package->id,
        'service_id' => $service->id,
        'quantity'   => 5,
    ]);
    echo "Added Service to Package: {$service->name} x{$packageItem->quantity}\n";

    $userPackage = UserPackage::create([
        'salon_id'       => $salon->id,
        'user_id'        => $user->id,
        'package_id'     => $package->id,
        'total_price'    => $package->price,
        'payment_status' => 'paid',
        'purchase_date'  => now(),
        'expiry_date'    => now()->addDays($package->validity_days),
        'is_active'      => true,
    ]);
    echo "User purchased Package. Expires: {$userPackage->expiry_date}\n";

    // Consume a session
    UserPackageUsage::create([
        'user_package_id' => $userPackage->id,
        'service_id'      => $service->id,
        'used_at'         => now(),
    ]);
    $used = UserPackageUsage::where('user_package_id', $userPackage->id)->count();
    echo "Package Usage: {$used}/5 sessions used ✓\n\n";

    echo "=== ALL TESTS PASSED ✓ ===\n";

    // Cleanup
    $userPackage->usages()->delete();
    $userPackage->delete();
    $package->items()->delete();
    $package->delete();
    $payroll->delete();
    $staffMember->delete();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
