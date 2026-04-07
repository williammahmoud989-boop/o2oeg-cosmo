<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Validate a coupon code for a specific salon.
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'salon_id' => 'required|exists:salons,id',
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('salon_id', $request->salon_id)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return \response()->json([
                'valid' => false,
                'message' => 'كود الخصم غير صحيح أو غير متاح لهذا الصالون.',
            ], 422);
        }

        // Check expiry
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return \response()->json([
                'valid' => false,
                'message' => 'عذراً، هذا الكود انتهت صلاحيته.',
            ], 422);
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return \response()->json([
                'valid' => false,
                'message' => 'عذراً، هذا الكود وصل للحد الأقصى للاستخدام.',
            ], 422);
        }

        return \response()->json([
            'valid' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'message' => 'تم تطبيق كود الخصم بنجاح! 🎉',
        ]);
    }
}
