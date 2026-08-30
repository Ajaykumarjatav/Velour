<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminPlatformReportExportService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AdminPlatformReportController extends Controller
{
    public function __construct(
        protected AdminPlatformReportExportService $exports,
        protected AuditLogService $audit,
    ) {}

    public function export(Request $request, string $type)
    {
        abort_unless($this->exports->supports($type), 404);

        $this->audit->data('data.export', "Admin platform report: {$type}", null, [
            'report_type' => $type,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]);

        return $this->exports->download($type, $request);
    }

    public function exportAll(Request $request)
    {
        $this->audit->data('data.export', 'Admin platform report: all (ZIP)', null, [
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ]);

        return $this->exports->downloadZip($request);
    }
}
