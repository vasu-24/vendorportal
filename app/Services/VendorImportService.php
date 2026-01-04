<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorCompanyInfo;
use App\Models\VendorContact;
use App\Models\VendorStatutoryInfo;
use App\Models\VendorBankDetail;
use App\Models\VendorTaxInfo;
use App\Models\VendorBusinessProfile;
use App\Jobs\SyncVendorToZoho;
use App\Jobs\SendVendorPasswordEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class VendorImportService
{
    protected $results;

    // Column mapping (Excel column index => field info)
    protected $columnMapping = [
        0 => ['table' => 'vendor', 'field' => 'vendor_name', 'required' => true],
        1 => ['table' => 'company_info', 'field' => 'business_type'],
        2 => ['table' => 'company_info', 'field' => 'incorporation_date', 'type' => 'date'],
        3 => ['table' => 'company_info', 'field' => 'registered_address'],
        4 => ['table' => 'company_info', 'field' => 'corporate_address'],
        5 => ['table' => 'company_info', 'field' => 'website'],
        6 => ['table' => 'company_info', 'field' => 'parent_company'],
        7 => ['table' => 'contact', 'field' => 'contact_person', 'required' => true],
        8 => ['table' => 'contact', 'field' => 'designation'],
        9 => ['table' => 'contact', 'field' => 'mobile', 'required' => true],
        10 => ['table' => 'vendor', 'field' => 'vendor_email', 'required' => true],
        11 => ['table' => 'statutory_info', 'field' => 'pan_number'],
        12 => ['table' => 'statutory_info', 'field' => 'tan_number'],
        13 => ['table' => 'statutory_info', 'field' => 'gstin'],
        14 => ['table' => 'statutory_info', 'field' => 'cin'],
        15 => ['table' => 'statutory_info', 'field' => 'msme_registered'],
        16 => ['table' => 'bank_details', 'field' => 'bank_name'],
        17 => ['table' => 'bank_details', 'field' => 'branch_address'],
        18 => ['table' => 'bank_details', 'field' => 'account_holder_name'],
        19 => ['table' => 'bank_details', 'field' => 'account_number'],
        20 => ['table' => 'bank_details', 'field' => 'ifsc_code'],
        21 => ['table' => 'bank_details', 'field' => 'account_type'],
        22 => ['table' => 'tax_info', 'field' => 'tax_residency'],
        23 => ['table' => 'tax_info', 'field' => 'gst_reverse_charge'],
        24 => ['table' => 'tax_info', 'field' => 'sez_status'],
        25 => ['table' => 'business_profile', 'field' => 'core_activities'],
        26 => ['table' => 'business_profile', 'field' => 'employee_count'],
        27 => ['table' => 'business_profile', 'field' => 'credit_period'],
        28 => ['table' => 'business_profile', 'field' => 'turnover_fy1'],
        29 => ['table' => 'business_profile', 'field' => 'turnover_fy2'],
        30 => ['table' => 'business_profile', 'field' => 'turnover_fy3'],
    ];

    public function __construct()
    {
        $this->resetResults();
    }

    protected function resetResults()
    {
        $this->results = [
            'total_rows' => 0,
            'imported' => 0,
            'skipped' => 0,
            'queued_jobs' => 0,
            'errors' => [],
        ];
    }

    /**
     * Import vendors from Excel file
     */
    public function import(string $filePath): array
    {
        $this->resetResults();

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Skip header rows (row 1 = section headers, row 2 = column headers)
            $dataRows = array_slice($rows, 2);
            $this->results['total_rows'] = count($dataRows);

            foreach ($dataRows as $index => $row) {
                $rowNumber = $index + 3; // Actual row number in Excel

                try {
                    $this->processRow($row, $rowNumber);
                } catch (Exception $e) {
                    $this->results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    $this->results['skipped']++;
                    Log::error("Import row {$rowNumber} failed", ['error' => $e->getMessage()]);
                }
            }

            return [
                'success' => true,
                'message' => "Imported {$this->results['imported']} vendors successfully. {$this->results['queued_jobs']} background jobs queued.",
                'data' => $this->results,
            ];

        } catch (Exception $e) {
            Log::error('Vendor import failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to process Excel file: ' . $e->getMessage(),
                'data' => $this->results,
            ];
        }
    }

    /**
     * Process a single row
     */
    protected function processRow(array $row, int $rowNumber): void
    {
        // Check if row is empty
        $legalEntityName = trim($row[0] ?? '');
        $email = trim($row[10] ?? '');

        if (empty($legalEntityName) && empty($email)) {
            return; // Skip empty rows
        }

        // Validate required fields
        $this->validateRow($row, $rowNumber);

        // Check for duplicate email
        if (Vendor::where('vendor_email', $email)->exists()) {
            throw new Exception("Email '{$email}' already exists");
        }

        // Parse row data into tables
        $parsedData = $this->parseRowData($row);

        // Create vendor in transaction
        DB::beginTransaction();

        try {
            // Create main vendor record
            $vendor = Vendor::create([
                'vendor_name' => $parsedData['vendor']['vendor_name'],
                'vendor_email' => $parsedData['vendor']['vendor_email'],
                'status' => 'accepted',
                'approval_status' => Vendor::STATUS_APPROVED,
                'approved_at' => now(),
                'token' => Vendor::generateToken(),
                'registration_completed' => true,
                'registration_completed_at' => now(),
                'current_step' => 4,
            ]);

            // Create related records
            if (!empty($parsedData['company_info'])) {
                $parsedData['company_info']['legal_entity_name'] = $parsedData['vendor']['vendor_name'];
                $vendor->companyInfo()->create($parsedData['company_info']);
            }

            if (!empty($parsedData['contact'])) {
                $parsedData['contact']['email'] = $parsedData['vendor']['vendor_email'];
                $vendor->contact()->create($parsedData['contact']);
            }

            if (!empty($parsedData['statutory_info'])) {
                $vendor->statutoryInfo()->create($parsedData['statutory_info']);
            }

            if (!empty($parsedData['bank_details'])) {
                $vendor->bankDetails()->create($parsedData['bank_details']);
            }

            if (!empty($parsedData['tax_info'])) {
                $vendor->taxInfo()->create($parsedData['tax_info']);
            }

            if (!empty($parsedData['business_profile'])) {
                $vendor->businessProfile()->create($parsedData['business_profile']);
            }

            DB::commit();
            $this->results['imported']++;

            // 🔥 DISPATCH JOBS TO QUEUE (runs in background - NO TIMEOUT!)
            SyncVendorToZoho::dispatch($vendor);
            SendVendorPasswordEmail::dispatch($vendor);
            $this->results['queued_jobs'] += 2;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate required fields
     */
    protected function validateRow(array $row, int $rowNumber): void
    {
        $errors = [];

        // Legal Entity Name (column 0)
        if (empty(trim($row[0] ?? ''))) {
            $errors[] = 'Legal Entity Name is required';
        }

        // Contact Person (column 7)
        if (empty(trim($row[7] ?? ''))) {
            $errors[] = 'Contact Person Name is required';
        }

        // Mobile (column 9)
        if (empty(trim($row[9] ?? ''))) {
            $errors[] = 'Mobile Number is required';
        }

        // Email (column 10)
        $email = trim($row[10] ?? '');
        if (empty($email)) {
            $errors[] = 'Email ID is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }
    }

    /**
     * Parse row data into table-specific arrays
     */
    protected function parseRowData(array $row): array
    {
        $data = [
            'vendor' => [],
            'company_info' => [],
            'contact' => [],
            'statutory_info' => [],
            'bank_details' => [],
            'tax_info' => [],
            'business_profile' => [],
        ];

        foreach ($this->columnMapping as $colIndex => $mapping) {
            $value = $row[$colIndex] ?? '';
            
            // Clean the value - remove ALL extra spaces
            if (is_string($value)) {
                $value = trim($value);
                $value = preg_replace('/\s+/', ' ', $value);
            }
            
            // Convert to string and trim again (for numeric values from Excel)
            $value = trim((string) $value);

            if (empty($value)) {
                continue;
            }

            // Handle date fields
            if (isset($mapping['type']) && $mapping['type'] === 'date') {
                $value = $this->parseDate($value);
            }

            // Truncate to safe lengths to prevent DB errors
            $maxLengths = [
                'pan_number' => 10,
                'tan_number' => 10,
                'gstin' => 15,
                'cin' => 21,
                'ifsc_code' => 11,
                'mobile' => 15,
                'account_number' => 20,
            ];
            
            $field = $mapping['field'];
            if (isset($maxLengths[$field])) {
                $value = substr($value, 0, $maxLengths[$field]);
            }

            $table = $mapping['table'];
            $data[$table][$field] = $value;
        }

        return $data;
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's a numeric value (Excel serial date)
        if (is_numeric($value)) {
            $unixTimestamp = ($value - 25569) * 86400;
            return date('Y-m-d', $unixTimestamp);
        }

        // Try common date formats
        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'd-M-Y', 'd/M/Y'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}