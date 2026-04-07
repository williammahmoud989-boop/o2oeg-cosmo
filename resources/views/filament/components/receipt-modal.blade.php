<div class="flex flex-col items-center justify-center p-4">
    @if($record->payment_receipt)
        <div class="mb-4 text-sm text-gray-500">
            <p><strong>كود الحجز:</strong> {{ $record->booking_code }}</p>
            <p><strong>وسيلة الدفع:</strong> {{ $record->payment_method }}</p>
        </div>
        <img 
            src="{{ asset('storage/' . $record->payment_receipt) }}" 
            alt="Payment Receipt" 
            class="max-w-full rounded-lg shadow-lg border border-gray-200"
            style="max-height: 500px; object-fit: contain;"
        >
        <div class="mt-4 flex gap-4">
            <a 
                href="{{ asset('storage/' . $record->payment_receipt) }}" 
                target="_blank"
                class="px-4 py-2 bg-primary-600 text-black rounded-lg text-sm font-bold hover:bg-primary-700 transition-colors"
                style="background-color: #fce7f3; border: 1px solid #f9a8d4;"
            >
                تحميل الصورة
            </a>
        </div>
    @else
        <div class="p-8 text-center text-gray-500">
            <p>لا يوجد إيصال مرفوع لهذا الحجز.</p>
        </div>
    @endif
</div>
