<?php

namespace App\Filament\Salon\Resources\InvoiceResource\Pages;

use App\Filament\Salon\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['invoice_number'] = 'INV-' . strtoupper(uniqid());
        return $data;
    }
}
