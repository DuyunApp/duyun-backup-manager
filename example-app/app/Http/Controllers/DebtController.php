<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use DuyunApp\DuyunBackupManager\Facades\BackupManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DebtController extends Controller
{
    public function backup(Request $request)
    {
        // ✅ تصحيح قواعد التحقق
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'from'    => ['required', 'date'],
            'to'      => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // ✅ تحديد التاريخ النهائي إذا لم يُرسل
        $from = $validated['from'];
        $to   = $validated['to'] ?? now()->toDateTimeString();

        // ✅ تجهيز الفلاتر الديناميكية

        $filters = [
            'user_id'    => $validated['user_id'],
            'created_at' => ['>=', $from],
        ];

        // إذا تم إرسال `to` أضف شرط إضافي
        // if (!empty($validated['to'])) {
        //     $filters['created_at'] = ['<=', $to];
        // }

        // ✅ تنفيذ النسخ الاحتياطي
        $path = BackupManager::for(Debt::class)
            ->filters($filters)
            ->columns(['id', 'amount', "title", 'created_at'])
            ->disk('public') // يمكن تغييره إلى s3 أو local
            ->directory('user-backups/' . date('Y'))
            ->fileName('debt-report-' . $validated['user_id'] . '-' . now()->format('Ymd_His') . '.xlsx')
            ->deleteAfterBackup(true)
            // ;
            // dd($path);
            ->run();

        // ✅ إرسال الملف للتحميل
        // http://127.0.0.1:8000/debts/backup?user_id=1&from=2025-01-01&to=2025-11-07
        return response()->download($path)->deleteFileAfterSend();
    }
}
