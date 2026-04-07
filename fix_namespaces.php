<?php
$files = [
    'app/Filament/Admin/Resources/SalonResource.php',
    'app/Filament/Admin/Resources/ServiceResource.php',
    'app/Filament/Admin/Resources/BookingResource.php',
    'app/Filament/Admin/Resources/SalonResource/RelationManagers/ServicesRelationManager.php',
    'app/Filament/Salon/Resources/ServiceResource.php',
    'app/Filament/Salon/Resources/BookingResource.php',
    'app/Filament/Salon/Resources/PromoCodes/PromoCodeResource.php',
    'app/Filament/Salon/Resources/PromoCodes/Schemas/PromoCodeForm.php',
    'app/Filament/Salon/Resources/PricingRules/PricingRuleResource.php',
    'app/Filament/Salon/Resources/PricingRules/Schemas/PricingRuleForm.php',
    'app/Filament/Salon/Pages/Tenancy/RegisterSalon.php',
    'app/Filament/Salon/Pages/Tenancy/EditSalonProfile.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Replace Form with Schema in signatures
        $content = str_replace('use Filament\Forms\Form;', 'use Filament\Schemas\Schema;', $content);
        $content = str_replace('public static function form(Form $form): Form', 'public static function form(Schema $schema): Schema', $content);
        $content = str_replace('public function form(Form $form): Form', 'public function form(Schema $schema): Schema', $content);
        
        // Fix all Forms Components to Schemas Components
        $content = str_replace('use Filament\\Forms\\Components\\', 'use Filament\\Schemas\\Components\\', $content);
        
        // Make sure Schema is used by the methods
        $content = preg_replace('/return \$form\\s*->schema/m', 'return $schema->schema', $content);
        
        // Put content back
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
