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

    // Fetch ONLY tags that are linked to managers (NOT from Zoho)
    $reportingTags = \App\Models\ManagerTag::select('tag_id', 'tag_name')
        ->distinct()
        ->orderBy('tag_name')
        ->get()
        ->map(fn($t) => [
            'tag_id' => $t->tag_id,
            'tag_name' => $t->tag_name,
        ])
        ->toArray();

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
        'reportingTags'  => $reportingTags,
    ]);
}



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

    // Fetch ONLY tags that are linked to managers (NOT from Zoho)
    $reportingTags = \App\Models\ManagerTag::select('tag_id', 'tag_name')
        ->distinct()
        ->orderBy('tag_name')
        ->get()
        ->map(fn($t) => [
            'tag_id' => $t->tag_id,
            'tag_name' => $t->tag_name,
        ])
        ->toArray();

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
            Log::info('STEP 8: Template file is: ' . $templateFile);
            
            // =====================================================
            // COMMON FIELDS (from contract DB - for all templates)
            // =====================================================
            $templateProcessor->setValue('VENDOR_NAME', $contract->vendor_name ?? '');
            $templateProcessor->setValue('VENDOR_CIN', $contract->vendor_cin ?? '');
            $templateProcessor->setValue('VENDOR_ADDRESS', $contract->vendor_address ?? '');
            $templateProcessor->setValue('COMPANY_NAME', $contract->company_name ?? '');
            $templateProcessor->setValue('COMPANY_CIN', $contract->company_cin ?? '');
            $templateProcessor->setValue('COMPANY_ADDRESS', $contract->company_address ?? '');
            
            Log::info('STEP 8: DB values set - Vendor: ' . ($contract->vendor_name ?? 'NULL'));

            // =====================================================
            // FIDE MOU PLACEHOLDERS
            // =====================================================
            if (str_contains($templateFile, 'FIDE_Agreement') || str_contains($templateFile, 'FIDE Agreement')) {
                Log::info('STEP 8: Processing FIDE MOU template');
                
                $effectiveDate = $request->input('effective_date');
                if ($effectiveDate) {
                    $effectiveDate = Carbon::parse($effectiveDate)->format('d-m-Y');
                }
                
                $templateProcessor->setValue('EFFECTIVE_DATE', $effectiveDate ?? '');
                $templateProcessor->setValue('MOU_VALIDITY_YEARS', $request->input('mou_validity_years') ?? '');
                $templateProcessor->setValue('TERMINATION_NOTICE_DAYS', $request->input('termination_notice_days') ?? '30');
                $templateProcessor->setValue('SECOND_PARTY_DESCRIPTION', $request->input('second_party_description') ?? '');
                $templateProcessor->setValue('MOU_PURPOSE', $request->input('mou_purpose') ?? '');
                $templateProcessor->setValue('MOU_OBJECTIVES', $request->input('mou_objectives') ?? '');
                $templateProcessor->setValue('VENDOR_CONTACT_NAME', $request->input('vendor_contact_name') ?? '');
                $templateProcessor->setValue('VENDOR_CONTACT_DESIGNATION', $request->input('vendor_contact_designation') ?? '');
                $templateProcessor->setValue('VENDOR_CONTACT_EMAIL', $request->input('vendor_contact_email') ?? '');
                $templateProcessor->setValue('VENDOR_CONTACT_ADDRESS', $request->input('vendor_contact_address') ?? '');
                $templateProcessor->setValue('VENDOR_SIGNATORY_NAME', $request->input('vendor_signatory_name') ?? '');
                $templateProcessor->setValue('VENDOR_SIGNATORY_DESIGNATION', $request->input('vendor_signatory_designation') ?? '');
                $templateProcessor->setValue('VENDOR_SIGNATORY_PLACE', $request->input('vendor_signatory_place') ?? '');
                
                Log::info('STEP 8: FIDE MOU placeholders set');
            }

            // =====================================================
            // NDA PLACEHOLDERS
            // =====================================================
            if (str_contains($templateFile, 'NDA')) {
                Log::info('STEP 8: Processing NDA template');
                
                $effectiveDate = $request->input('effective_date');
                if ($effectiveDate) {
                    $effectiveDate = Carbon::parse($effectiveDate)->format('d-m-Y');
                }
                $signingDate = $request->input('signing_date');
                if ($signingDate) {
                    $signingDate = Carbon::parse($signingDate)->format('d-m-Y');
                }
                
                $templateProcessor->setValue('EFFECTIVE_DATE', $effectiveDate ?? '');
                $templateProcessor->setValue('NDA_TERM_YEARS', $request->input('nda_term_years') ?? '');
                $templateProcessor->setValue('CONFIDENTIALITY_SURVIVAL_YEARS', $request->input('confidentiality_survival_years') ?? '');
                $templateProcessor->setValue('DISCLOSING_PARTY_NAME', $request->input('disclosing_party_name') ?? '');
                $templateProcessor->setValue('DISCLOSING_PARTY_SHORT_NAME', $request->input('disclosing_party_short_name') ?? '');
                $templateProcessor->setValue('COMPANY_INCORPORATION_TYPE', $request->input('company_incorporation_type') ?? '');
                $templateProcessor->setValue('DISCLOSING_PARTY_ADDRESS', $request->input('disclosing_party_address') ?? '');
                $templateProcessor->setValue('CLIENT_LEGAL_NAME', $request->input('client_legal_name') ?? '');
                $templateProcessor->setValue('CLIENT_ADDRESS', $request->input('client_address') ?? '');
                $templateProcessor->setValue('CONFIDENTIALITY_PURPOSE', $request->input('confidentiality_purpose') ?? '');
                $templateProcessor->setValue('DISCLOSING_PARTY_SIGNATORY', $request->input('disclosing_party_signatory') ?? '');
                $templateProcessor->setValue('CLIENT_SIGNATORY', $request->input('client_signatory') ?? '');
                $templateProcessor->setValue('SIGNING_DATE', $signingDate ?? '');
                
                Log::info('STEP 8: NDA placeholders set');
            }

            // =====================================================
            // CONSULTING AGREEMENT PLACEHOLDERS
            // =====================================================
            if (str_contains($templateFile, 'Consulting')) {
                Log::info('STEP 8: Processing Consulting Agreement template');
                
                $agreementDate = $request->input('agreement_date');
                if ($agreementDate) {
                    $agreementDate = Carbon::parse($agreementDate)->format('d-m-Y');
                }
                $signingDate = $request->input('signing_date');
                if ($signingDate) {
                    $signingDate = Carbon::parse($signingDate)->format('d-m-Y');
                }
                $sowStartDate = $request->input('sow_start_date');
                if ($sowStartDate) {
                    $sowStartDate = Carbon::parse($sowStartDate)->format('d-m-Y');
                }
                $sowEndDate = $request->input('sow_end_date');
                if ($sowEndDate) {
                    $sowEndDate = Carbon::parse($sowEndDate)->format('d-m-Y');
                }
                
                $templateProcessor->setValue('AGREEMENT_DATE', $agreementDate ?? '');
                $templateProcessor->setValue('CONSULTANT_NAME', $request->input('consultant_name') ?? '');
                $templateProcessor->setValue('CONSULTANT_PAN', $request->input('consultant_pan') ?? '');
                $templateProcessor->setValue('CONSULTANT_ADDRESS', $request->input('consultant_address') ?? '');
                $templateProcessor->setValue('CLIENT_LEGAL_NAME', $request->input('client_legal_name') ?? '');
                $templateProcessor->setValue('CLIENT_CIN', $request->input('client_cin') ?? '');
                $templateProcessor->setValue('CLIENT_REGISTERED_ADDRESS', $request->input('client_registered_address') ?? '');
                $templateProcessor->setValue('SERVICES_DESCRIPTION', $request->input('services_description') ?? '');
                $scopeOfWork = $request->input('scope_of_work') ?? '';
                 if ($scopeOfWork) {
                 $scopeOfWork = str_replace("\n", '</w:t><w:br/><w:t>', $scopeOfWork);
                   }
                $templateProcessor->setValue('SOW_SCOPE_OF_WORK', $scopeOfWork);
                Log::info('STEP 8: Scope of Work set with line breaks');
                $templateProcessor->setValue('INITIAL_TERM_MONTHS', $request->input('initial_term_months') ?? '');
                $templateProcessor->setValue('TERMINATION_NOTICE_DAYS', $request->input('termination_notice_days') ?? '30');
                $templateProcessor->setValue('SOW_START_DATE', $sowStartDate ?? '');
                $templateProcessor->setValue('SOW_END_DATE', $sowEndDate ?? '');
                $templateProcessor->setValue('CONSULTANT_SIGNATORY_NAME', $request->input('consultant_signatory_name') ?? '');
                $templateProcessor->setValue('CLIENT_SIGNATORY_NAME', $request->input('client_signatory_name') ?? '');
                $templateProcessor->setValue('SIGNING_PLACE', $request->input('signing_place') ?? '');
                $templateProcessor->setValue('SIGNING_DATE', $signingDate ?? '');
                
                Log::info('STEP 8: Consulting Agreement placeholders set');
            }

            // =====================================================
            // MSA PLACEHOLDERS
            // =====================================================
            if (str_contains($templateFile, 'MSA')) {
                Log::info('STEP 8: Processing MSA template');
                
                $msaExecutionDate = $request->input('msa_execution_date');
                if ($msaExecutionDate) {
                    $msaExecutionDate = Carbon::parse($msaExecutionDate)->format('d-m-Y');
                }
                $signingDate = $request->input('signing_date');
                if ($signingDate) {
                    $signingDate = Carbon::parse($signingDate)->format('d-m-Y');
                }
                
                $templateProcessor->setValue('MSA_EXECUTION_DATE', $msaExecutionDate ?? '');
                $templateProcessor->setValue('SERVICE_PROVIDER_NAME', $request->input('service_provider_name') ?? '');
                $templateProcessor->setValue('SERVICE_PROVIDER_ADDRESS', $request->input('service_provider_address') ?? '');
                $templateProcessor->setValue('CLIENT_LEGAL_NAME', $request->input('client_legal_name') ?? '');
                $templateProcessor->setValue('CLIENT_CIN', $request->input('client_cin') ?? '');
                $templateProcessor->setValue('CLIENT_REGISTERED_ADDRESS', $request->input('client_registered_address') ?? '');
                $templateProcessor->setValue('CLIENT_BUSINESS_DESCRIPTION', $request->input('client_business_description') ?? '');
                $templateProcessor->setValue('SERVICES_DESCRIPTION', $request->input('services_description') ?? '');
                $templateProcessor->setValue('SOW_REFERENCE', $request->input('sow_reference') ?? '');
                $templateProcessor->setValue('SERVICE_FEES', $request->input('service_fees') ?? '');
                $templateProcessor->setValue('PAYMENT_TERMS', $request->input('payment_terms') ?? '');
                $templateProcessor->setValue('SERVICE_PROVIDER_SIGNATORY', $request->input('service_provider_signatory') ?? '');
                $templateProcessor->setValue('CLIENT_SIGNATORY', $request->input('client_signatory') ?? '');
                $templateProcessor->setValue('SIGNING_DATE', $signingDate ?? '');
                
                Log::info('STEP 8: MSA placeholders set');
            }

            Log::info('STEP 8 PASSED: All placeholders set for template type');

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