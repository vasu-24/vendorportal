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
        // List doc/docx/pdf files in public/agreements
        $files = collect(File::files(public_path('agreements')))
            ->filter(function ($file) {
                return in_array(strtolower($file->getExtension()), ['doc', 'docx', 'pdf']);
            })
            ->map(function ($file) {
                return $file->getFilename();
            })
            ->values();

        // Default template
        $defaultFile = 'FIDE MOU Template.docx';
        if (!$files->contains($defaultFile)) {
            $defaultFile = $files->first();
        }

        $organisations = Organisation::orderBy('company_name')->get();

        $vendors = Vendor::with(['companyInfo', 'statutoryInfo'])
            ->where('approval_status', 'approved')
            ->where('registration_completed', true)
            ->orderBy('vendor_name')
            ->get();

        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pages.contracts.create', [
            'agreementFiles' => $files,
            'defaultFile'    => $defaultFile,
            'organisations'  => $organisations,
            'vendors'        => $vendors,
            'categories'     => $categories,
        ]);
    }

    // =====================================================
    // EDIT PAGE
    // =====================================================

    public function edit($id)
    {
        $contract = Contract::with(['items.category'])->findOrFail($id);

        // Only draft contracts can be edited
        if ($contract->status !== 'draft') {
            return redirect()->route('contracts.index')
                ->with('error', 'Only draft contracts can be edited');
        }

        $files = collect(File::files(public_path('agreements')))
            ->filter(function ($file) {
                return in_array(strtolower($file->getExtension()), ['doc', 'docx', 'pdf']);
            })
            ->map(function ($file) {
                return $file->getFilename();
            })
            ->values();

        $organisations = Organisation::orderBy('company_name')->get();

        $vendors = Vendor::with(['companyInfo', 'statutoryInfo'])
            ->where('approval_status', 'approved')
            ->where('registration_completed', true)
            ->orderBy('vendor_name')
            ->get();

        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('pages.contracts.edit', [
            'contract'       => $contract,
            'agreementFiles' => $files,
            'organisations'  => $organisations,
            'vendors'        => $vendors,
            'categories'     => $categories,
        ]);
    }

    // =====================================================
    // PREVIEW TEMPLATE (Convert to PDF)
    // =====================================================

    public function preview(Request $request)
    {
        $fileName = $request->query('file');

        if (!$fileName) {
            abort(400, 'File name is required.');
        }

        // Security check
        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            abort(400, 'Invalid file name.');
        }

        $sourcePath = public_path('agreements/' . $fileName);

        if (!File::exists($sourcePath)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // If PDF, stream directly
        if ($ext === 'pdf') {
            return response()->file($sourcePath, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Only allow DOC/DOCX for conversion
        if (!in_array($ext, ['doc', 'docx'])) {
            abort(400, 'Unsupported file type.');
        }

        // Convert to PDF using LibreOffice
        $outputDir = storage_path('app/agreements-temp');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0777, true);
        }

        $pdfName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfName;

        if (!File::exists($pdfPath)) {
            $sofficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';

            $command = '"' . $sofficePath . '"'
                . ' --headless --convert-to pdf'
                . ' --outdir "' . $outputDir . '"'
                . ' "' . $sourcePath . '"';

            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !File::exists($pdfPath)) {
                abort(500, 'Failed to convert file to PDF.');
            }
        }

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // =====================================================
    // PREVIEW UPLOADED CONTRACT DOCUMENT
    // =====================================================

    public function previewDocument(Request $request)
    {
        $filePath = $request->query('file');

        if (!$filePath) {
            abort(400, 'File path is required.');
        }

        // Security check
        if (str_contains($filePath, '..')) {
            abort(400, 'Invalid file path.');
        }

        $fullPath = storage_path('app/public/' . $filePath);

        if (!File::exists($fullPath)) {
            abort(404, 'File not found.');
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // PDF - stream directly
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // DOC/DOCX - convert to PDF
        if (in_array($ext, ['doc', 'docx'])) {
            $outputDir = storage_path('app/contracts/previews');
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0777, true);
            }

            $pdfName = pathinfo($filePath, PATHINFO_FILENAME) . '.pdf';
            $pdfPath = $outputDir . DIRECTORY_SEPARATOR . $pdfName;

            if (!File::exists($pdfPath)) {
                $sofficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';

                $command = '"' . $sofficePath . '"'
                    . ' --headless --convert-to pdf'
                    . ' --outdir "' . $outputDir . '"'
                    . ' "' . $fullPath . '"';

                exec($command, $output, $returnVar);

                if ($returnVar !== 0 || !File::exists($pdfPath)) {
                    abort(500, 'Failed to convert file to PDF.');
                }
            }

            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        abort(400, 'Unsupported file type.');
    }

    // =====================================================
    // DOWNLOAD WORD FILE (Auto-download after create)
    // Only 6 placeholders - All stored in contracts table
    // =====================================================

    public function downloadWord($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            
            $templateFile = $contract->template_file;
            
            if (!$templateFile) {
                return back()->with('error', 'No template file selected.');
            }
            
            $templatePath = public_path('agreements/' . $templateFile);
            
            if (!File::exists($templatePath)) {
                return back()->with('error', 'Template file not found.');
            }

            $ext = strtolower(pathinfo($templateFile, PATHINFO_EXTENSION));
            if ($ext !== 'docx') {
                return back()->with('error', 'Template must be a .docx file.');
            }
            
            // Load template
            $templateProcessor = new TemplateProcessor($templatePath);
            
            // Only 6 placeholders - Direct from contracts table
            $templateProcessor->setValue('VENDOR_NAME', $contract->vendor_name ?? '');
            $templateProcessor->setValue('VENDOR_CIN', $contract->vendor_cin ?? '');
            $templateProcessor->setValue('VENDOR_ADDRESS', $contract->vendor_address ?? '');
            $templateProcessor->setValue('COMPANY_NAME', $contract->company_name ?? '');
            $templateProcessor->setValue('COMPANY_CIN', $contract->company_cin ?? '');
            $templateProcessor->setValue('COMPANY_ADDRESS', $contract->company_address ?? '');
            
            // Save output file
            $outputDir = storage_path('app/contracts/generated');
            if (!File::exists($outputDir)) {
                File::makeDirectory($outputDir, 0777, true);
            }
            
            $outputFileName = $contract->contract_number . '.docx';
            $outputPath = $outputDir . DIRECTORY_SEPARATOR . $outputFileName;
            
            // Save the document
            $templateProcessor->saveAs($outputPath);
            
            // Clear output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Download file
            return response()->download($outputPath, $outputFileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Download Contract Word Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate Word document.');
        }
    }
}