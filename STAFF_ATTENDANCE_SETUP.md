# دليل إعداد ميزة تذكير حضور الموظفين
# Staff Attendance Reminder Setup Guide

## المتطلبات:
- حساب Twilio مع WhatsApp Business API
- Laravel project جاهز
- PHP 8.1+

## خطوات الإعداد:

### 1. إعداد Twilio:
1. اذهب إلى https://www.twilio.com
2. أنشئ حساب جديد
3. فعل WhatsApp Business API
4. احصل على:
   - Account SID
   - Auth Token
   - WhatsApp Number

### 2. تحديث .env:
```
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### 3. إعداد Webhook:
في Twilio Dashboard > WhatsApp:
- WHEN A MESSAGE COMES IN: https://yourdomain.com/api/twilio/webhook
- Method: HTTP POST

### 4. جدولة الأمر:
#### Windows:
استخدم Task Scheduler لتشغيل `schedule_attendance.ps1` يوميًا

#### Linux:
```bash
crontab -e
# أضف:
0 6 * * * php /path/to/artisan staff:send-attendance-reminders
```

### 5. اختبار الميزة:
```bash
# اختبار جاف
php artisan staff:send-attendance-reminders --dry-run

# تشغيل الاختبارات
php artisan test tests/Feature/TwilioWebhookTest.php
```

### 6. استخدام الميزة:
1. في لوحة الإدارة، أضف موظف جديد
2. فعل "تفعيل تذكير الحضور"
3. أدخل وقت الحضور ورقم واتساب
4. وافق على الخصوصية
5. انتظر الوقت المحدد لتلقي الرسالة

## استكشاف الأخطاء:
- تحقق من logs في `storage/logs/laravel.log`
- تأكد من صحة أرقام الهواتف (+country_code)
- تحقق من رصيد Twilio
- استخدم dry-run للاختبار

## التكلفة:
- Twilio WhatsApp: ~$0.005 للرسالة
- مكالمات: ~$0.014 للدقيقة

## الدعم:
إذا واجهت مشاكل، تحقق من:
- إعدادات Twilio
- متغيرات .env
- صلاحيات الملفات
- اتصال الإنترنت