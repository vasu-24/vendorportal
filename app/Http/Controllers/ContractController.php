<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Organisation;
use App\Models\Contract;
use App\Models\Category;
use App\Models\Vendor;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;

class ContractController extends Controller
{
    // =====================================================
    // INDEX PAGE
    // =====================================================
    public function index()
    {
        $vendors = Vendor::with(['companyInfo', 'statutoryInfo'])
            ->where('approval_status', 'approved')
            ->where('registration_completed', true)
            ->orderBy('vendor_name')
            ->get();

        return view('pages.contracts.index', [
            'vendors' => $vendors,
        ]);
    }

    // =====================================================
    // CREATE PAGE
    // =====================================================
   public function create()
{
    $files = collect(File::files(public_path('agreements')))
        ->filter(fn($file) => in_array(strtolower($file->getExtension()), ['doc', 'docx', 'pdf']))
        ->map(fn($file) => $file->getFilename())
        ->values();

    $defaultFile = 'FIDE_Agreement_with_Placeholders2.docx';
    if (!$files->contains($defaultFile)) {
        $defaultFile = $files->first();
    }

    // Fetch Reporting Tags from Zoho
    $reportingTags = [];
    try {
        $zohoService = new \App\Services\ZohoService();
        if ($zohoService->isConnected()) {
            $reportingTags = $zohoService->getReportingTags();
        }
    } catch (\Exception $e) {
        \Log::error('Failed to fetch reporting tags: ' . $e->getMessage());
    }

    return view('pages.contracts.create', [
        'agreementFiles' => $files,
        'defaultFile'    => $defaultFile,
        'organisations'  => Organisation::orderBy('company_name')->get(),
        'vendors'        => Vendor::with(['companyInfo', 'statutoryInfo'])
                                ->where('approval_status', 'approved')
                                ->where('registration_completed', true)
                                ->orderBy('vendor_name')
                                ->get(),
        'categories'     => Category::where('status', 'active')->orderBy('name')->get(),
        'reportingTags'  => $reportingTags, // NEW
    ]);
}

    // =====================================================
    // EDIT PAGE
    // =====================================================
   public function edit($id)
{
    $contract = Contract::with(['items.category'])->findOrFail($id);

    if ($contract->status !== 'draft') {
        return redirect()->route('contracts.index')
            ->with('error', 'Only draft contracts can be edited');
    }

    $files = collect(File::files(public_path('agreements')))
        ->filter(fn($file) => in_array(strtolower($file->getExtension()), ['doc', 'docx', 'pdf']))
        ->map(fn($file) => $file->getFilename())
        ->values();

    // Fetch Reporting Tags from Zoho
    $reportingTags = [];
    try {
        $zohoService = new \App\Services\ZohoService();
        if ($zohoService->isConnected()) {
            $reportingTags = $zohoService->getReportingTags();
        }
    } catch (\Exception $e) {
        \Log::error('Failed to fetch reporting tags: ' . $e->getMessage());
    }

    return view('pages.contracts.edit', [
        'contract'       => $contract,
        'agreementFiles' => $files,
        'organisations'  => Organisation::orderBy('company_name')->get(),
        'vendors'        => Vendor::with(['companyInfo', 'statutoryInfo'])
                                ->where('approval_status', 'approved')
                                ->where('registration_completed', true)
                                ->orderBy('vendor_name')
                                ->get(),
        'categories'     => Category::where('status', 'active')->orderBy('name')->get(),
        'reportingTags'  => $reportingTags,
    ]);
}


    // =====================================================
    // PREVIEW TEMPLATE
    // =====================================================
    public function preview(Request $request)
    {
        $fileName = $request->query('file');

        if (!$fileName) {
            abort(400, 'File name is required.');
        }

        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            abort(400, 'Invalid file name.');
        }

        $sourcePath = public_path('agreements/' . $fileName);

        if (!File::exists($sourcePath)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($sourcePath, ['Content-Type' => 'application/pdf']);
        }

        if (!in_array($ext, ['doc', 'docx'])) {
            abort(400, 'Unsupported file type.');
        }

        $outputDir = storage_path('app/agreements-temp');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0777, true);
        }

        $pdfName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfName;

        if (!File::exists($pdfPath)) {
            $sofficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
            $command = '"' . $sofficePath . '" --headless --convert-to pdf --outdir "' . $outputDir . '" "' . $sourcePath . '"';
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !File::exists($pdfPath)) {
                abort(500, 'Failed to convert file to PDF.');
            }
        }

        return response()->file($pdfPath, ['Content-Type' => 'application/pdf']);
    }

    // =====================================================
    // PREVIEW UPLOADED DOCUMENT
    // =====================================================
    public function previewDocument(Request $request)
    {
        $filePath = $request->query('file');

        if (!$filePath) {
            abort(400, 'File path is required.');
        }

        if (str_contains($filePath, '..')) {
            abort(400, 'Invalid file path.');
        }

        $fullPath = storage_path('app/public/' . $filePath);

        if (!File::exists($fullPath)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, ['Content-Type' => 'application/pdf']);
        }

        if (in_array($ext, ['doc', 'docx'])) {
            $outputDir = storage_path('app/contracts/previews');
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0777, true);
            }

            $pdfName = pathinfo($filePath, PATHINFO_FILENAME) . '.pdf';
            $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfName;

            if (!File::exists($pdfPath)) {
                $sofficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
                $command = '"' . $sofficePath . '" --headless --convert-to pdf --outdir "' . $outputDir . '" "' . $fullPath . '"';
                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !File::exists($pdfPath)) {
                    abort(500, 'Failed to convert file to PDF.');
                }
            }

            return response()->file($pdfPath, ['Content-Type' => 'application/pdf']);
        }

        abort(400, 'Unsupported file type.');
    }

    // =====================================================
    // DOWNLOAD WORD FILE - FULL DEBUG VERSION
    // =====================================================
    public function downloadWord($id, Request $request)
    {
        // ========== STEP 1: LOG START ==========
        Log::info('========== DOWNLOAD WORD STARTED ==========');
        Log::info('Contract ID: ' . $id);
        Log::info('Request Method: ' . $request->method());
        Log::info('Request Data: ' . json_encode($request->all()));

        try {
            // ========== STEP 2: CHECK ZIP EXTENSION ==========
            if (!class_exists('ZipArchive')) {
                Log::error('STEP 2 FAILED: ZipArchive class not found!');
                return response()->json([
                    'success' => false,
                    'step' => 2,
                    'error' => 'PHP ZIP extension is not enabled! Please enable it in php.ini'
                ], 500);
            }
            Log::info('STEP 2 PASSED: ZipArchive is available');

            // ========== STEP 3: GET CONTRACT ==========
            $contract = Contract::find($id);
            if (!$contract) {
                Log::error('STEP 3 FAILED: Contract not found with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'step' => 3,
                    'error' => 'Contract not found with ID: ' . $id
                ], 404);
            }
            Log::info('STEP 3 PASSED: Contract found - ' . $contract->contract_number);

            // ========== STEP 4: CHECK TEMPLATE FILE ==========
            $templateFile = $contract->template_file;
            Log::info('STEP 4: Template file from DB: ' . ($templateFile ?? 'NULL'));
            
            if (!$templateFile) {
                Log::error('STEP 4 FAILED: No template file in contract');
                return response()->json([
                    'success' => false,
                    'step' => 4,
                    'error' => 'No template file selected for this contract'
                ], 400);
            }

            // ========== STEP 5: CHECK TEMPLATE PATH ==========
            $templatePath = public_path('agreements/' . $templateFile);
            Log::info('STEP 5: Full template path: ' . $templatePath);
            
            if (!File::exists($templatePath)) {
                Log::error('STEP 5 FAILED: Template file not found at: ' . $templatePath);
                return response()->json([
                    'success' => false,
                    'step' => 5,
                    'error' => 'Template file not found at: ' . $templatePath
                ], 404);
            }
            Log::info('STEP 5 PASSED: Template file exists');

            // ========== STEP 6: CHECK FILE EXTENSION ==========
            $ext = strtolower(pathinfo($templateFile, PATHINFO_EXTENSION));
            Log::info('STEP 6: File extension: ' . $ext);
            
            if ($ext !== 'docx') {
                Log::error('STEP 6 FAILED: Not a docx file, got: ' . $ext);
                return response()->json([
                    'success' => false,
                    'step' => 6,
                    'error' => 'Template must be a .docx file. Got: ' . $ext
                ], 400);
            }
            Log::info('STEP 6 PASSED: File is .docx');

            // ========== STEP 7: LOAD TEMPLATE ==========
            Log::info('STEP 7: Loading template...');
            $templateProcessor = new TemplateProcessor($templatePath);
            Log::info('STEP 7 PASSED: Template loaded successfully');

            // ========== STEP 8: SET PLACEHOLDERS ==========
            Log::info('STEP 8: Setting placeholders...');
            
            // Party details from DB
            $templateProcessor->setValue('VENDOR_NAME', $contract->vendor_name ?? '');
            $templateProcessor->setValue('VENDOR_CIN', $contract->vendor_cin ?? '');
            $templateProcessor->setValue('VENDOR_ADDRESS', $contract->vendor_address ?? '');
            $templateProcessor->setValue('COMPANY_NAME', $contract->company_name ?? '');
            $templateProcessor->setValue('COMPANY_CIN', $contract->company_cin ?? '');
            $templateProcessor->setValue('COMPANY_ADDRESS', $contract->company_address ?? '');
            
            Log::info('STEP 8: DB values set - Vendor: ' . ($contract->vendor_name ?? 'NULL'));

            // Fields from POST request
            $effectiveDate = $request->input('effective_date');
            if ($effectiveDate) {
                $effectiveDate = Carbon::parse($effectiveDate)->format('d-m-Y');
            }
            $templateProcessor->setValue('EFFECTIVE_DATE', $effectiveDate ?? '');
            $templateProcessor->setValue('MOU_VALIDITY_YEARS', $request->input('mou_validity_years') ?? '');
            $templateProcessor->setValue('SECOND_PARTY_DESCRIPTION', $request->input('second_party_description') ?? '');
            $templateProcessor->setValue('MOU_PURPOSE', $request->input('mou_purpose') ?? '');
            $templateProcessor->setValue('MOU_OBJECTIVES', $request->input('mou_objectives') ?? '');
            $templateProcessor->setValue('TERMINATION_NOTICE_DAYS', $request->input('termination_notice_days') ?? '30');
            $templateProcessor->setValue('VENDOR_CONTACT_NAME', $request->input('vendor_contact_name') ?? '');
            $templateProcessor->setValue('VENDOR_CONTACT_EMAIL', $request->input('vendor_contact_email') ?? '');
            $templateProcessor->setValue('VENDOR_CONTACT_ADDRESS', $request->input('vendor_contact_address') ?? '');
            
            Log::info('STEP 8 PASSED: All placeholders set');
            Log::info('STEP 8: POST values - Effective Date: ' . ($effectiveDate ?? 'NULL'));

            // ========== STEP 9: CREATE OUTPUT DIRECTORY ==========
            $outputDir = storage_path('app/contracts/generated');
            Log::info('STEP 9: Output directory: ' . $outputDir);
            
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0777, true);
                Log::info('STEP 9: Created output directory');
            }
            Log::info('STEP 9 PASSED: Output directory ready');

            // ========== STEP 10: SAVE DOCUMENT ==========
            $outputFileName = $contract->contract_number . '.docx';
            $outputPath = $outputDir . DIRECTORY_SEPARATOR . $outputFileName;
            Log::info('STEP 10: Saving to: ' . $outputPath);
            
            $templateProcessor->saveAs($outputPath);
            
            if (!File::exists($outputPath)) {
                Log::error('STEP 10 FAILED: Output file was not created');
                return response()->json([
                    'success' => false,
                    'step' => 10,
                    'error' => 'Failed to save document to: ' . $outputPath
                ], 500);
            }
            
            $fileSize = filesize($outputPath);
            Log::info('STEP 10 PASSED: Document saved! Size: ' . $fileSize . ' bytes');

            // ========== STEP 11: DOWNLOAD ==========
            Log::info('STEP 11: Preparing download...');
            
            if (ob_get_level()) {
                ob_end_clean();
            }

            Log::info('STEP 11: Sending file for download...');
            Log::info('========== DOWNLOAD WORD COMPLETED ==========');

            return response()->download($outputPath, $outputFileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $outputFileName . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
            Log::error('PhpWord Exception: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'PhpWord Error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('General Exception: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}