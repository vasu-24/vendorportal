<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value, string $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get multiple settings by keys
     */
    public static function getValues(array $keys): array
    {
        return static::whereIn('key', $keys)->pluck('value', 'key')->toArray();
    }

    /**
     * Get all settings in a group
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }

    // =====================================================
    // TDS HELPERS
    // =====================================================

    /**
     * Get TDS monthly due date
     */
    public static function getTdsMonthlyDueDate(): int
    {
        return (int) static::getValue('tds_monthly_due_date', 7);
    }

    /**
     * Get TDS March FY end due date (April)
     */
    public static function getTdsMarchDueDate(): int
    {
        return (int) static::getValue('tds_march_due_date', 30);
    }

    /**
     * Get TDS warning days
     */
    public static function getTdsWarningDays(): int
    {
        return (int) static::getValue('tds_warning_days', 3);
    }

    /**
     * Get default TDS rate
     */
    public static function getDefaultTdsRate(): float
    {
        return (float) static::getValue('tds_default_rate', 5);
    }

    /**
     * Check if dashboard TDS warning is enabled
     */
    public static function showDashboardTdsWarning(): bool
    {
        return (bool) static::getValue('tds_show_dashboard_warning', true);
    }

    /**
     * Check if invoice page TDS warning is enabled
     */
    public static function showInvoiceTdsWarning(): bool
    {
        return (bool) static::getValue('tds_show_invoice_warning', true);
    }

    // =====================================================
    // COMPANY HELPERS
    // =====================================================

    /**
     * Get company name
     */
    public static function getCompanyName(): string
    {
        return static::getValue('company_name', config('app.name'));
    }

    /**
     * Get company logo path
     */
    public static function getCompanyLogo(): ?string
    {
        return static::getValue('company_logo');
    }

    // =====================================================
    // ZOHO HELPERS
    // =====================================================

    /**
     * Get Zoho expense account ID
     */
    public static function getZohoExpenseAccountId(): ?string
    {
        return static::getValue('zoho_expense_account_id');
    }

    /**
     * Check if auto sync to Zoho is enabled
     */
    public static function autoSyncToZoho(): bool
    {
        return (bool) static::getValue('auto_sync_zoho', true);
    }

    // =====================================================
    // APP HELPERS
    // =====================================================

    /**
     * Get invoice number prefix
     */
    public static function getInvoicePrefix(): string
    {
        return static::getValue('invoice_prefix', 'INV-');
    }

    /**
     * Get travel invoice number prefix
     */
    public static function getTravelInvoicePrefix(): string
    {
        return static::getValue('travel_invoice_prefix', 'TRV-');
    }

    /**
     * Get CEO approval threshold
     */
    public static function getCeoApprovalThreshold(): float
    {
        return (float) static::getValue('ceo_approval_threshold', 500000);
    }

    /**
     * Check if travel invoice module is enabled
     */
    public static function isTravelInvoiceEnabled(): bool
    {
        return (bool) static::getValue('enable_travel_invoice', true);
    }

    /**
     * Check if bulk upload is enabled
     */
    public static function isBulkUploadEnabled(): bool
    {
        return (bool) static::getValue('enable_bulk_upload', true);
    }

    /**
     * Check if bill attachment is required
     */
    public static function isBillAttachmentRequired(): bool
    {
        return (bool) static::getValue('require_bill_attachment', true);
    }
}