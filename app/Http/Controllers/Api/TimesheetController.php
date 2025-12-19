<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    // =====================================================
    // VALIDATE UPLOADED TIMESHEET
    // =====================================================
    public function validateTimesheet(Request $request)
    {
        $request->validate([
            'timesheet' => 'required|file|mimes:xlsx,xls|max:5120'
        ]);

        $file = $request->file('timesheet');
        $extension = strtolower($file->getClientOriginalExtension());

        // Check extension
        if (!in_array($extension, ['xlsx', 'xls'])) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Only Excel files (.xlsx, .xls) allowed'
            ], 422);
        }

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'File size must be less than 5MB'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Timesheet accepted',
            'filename' => $file->getClientOriginalName(),
            'size' => round($file->getSize() / 1024, 2) . ' KB'
        ]);
    }
}