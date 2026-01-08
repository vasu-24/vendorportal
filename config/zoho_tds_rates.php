<?php

/**
 * ============================================================
 * ZOHO BOOKS TDS TAX RATES - INDIA EDITION
 * ============================================================
 * 
 * Organization ID: 60062970419
 * 
 * WHY HARDCODED:
 * - Zoho doesn't expose TDS via official API endpoint
 * - TDS rates are government-mandated and rarely change
 * - Tax IDs are organization-specific and permanent
 * 
 * TO UPDATE:
 * - If government changes TDS rates, update this file
 * - Tax IDs will remain same, only percentages may change
 * 
 * API REFERENCE (Undocumented):
 * GET /books/v3/contacts/editpage?organization_id={org_id}
 * Response: tds_taxes[]
 * 
 * ============================================================
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Section 194 - Dividend
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032042',
        'section'        => '194',
        'tax_name'       => 'Dividend',
        'tax_percentage' => 10,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032056',
        'section'        => '194',
        'tax_name'       => 'Dividend (Reduced)',
        'tax_percentage' => 7.5,
        'is_reduced'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section 194A - Interest (Other than Securities)
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032044',
        'section'        => '194A',
        'tax_name'       => 'Other Interest than securities',
        'tax_percentage' => 10,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032058',
        'section'        => '194A',
        'tax_name'       => 'Other Interest than securities (Reduced)',
        'tax_percentage' => 7.5,
        'is_reduced'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section 194C - Contractors ⭐ MOST COMMON FOR TRAVEL INVOICES
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032046',
        'section'        => '194C',
        'tax_name'       => 'Payment of contractors HUF/Indiv',
        'tax_percentage' => 1,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032060',
        'section'        => '194C',
        'tax_name'       => 'Payment of contractors HUF/Indiv (Reduced)',
        'tax_percentage' => 0.75,
        'is_reduced'     => true,
    ],
    [
        'tax_id'         => '3463444000000032048',
        'section'        => '194C',
        'tax_name'       => 'Payment of contractors for Others',
        'tax_percentage' => 2,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032062',
        'section'        => '194C',
        'tax_name'       => 'Payment of contractors for Others (Reduced)',
        'tax_percentage' => 1.5,
        'is_reduced'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section 194H - Commission or Brokerage
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032050',
        'section'        => '194H',
        'tax_name'       => 'Commission or Brokerage',
        'tax_percentage' => 2,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032064',
        'section'        => '194H',
        'tax_name'       => 'Commission or Brokerage (Reduced)',
        'tax_percentage' => 3.75,
        'is_reduced'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section 194I - Rent
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032052',
        'section'        => '194I',
        'tax_name'       => 'Rent on land or furniture etc',
        'tax_percentage' => 10,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032066',
        'section'        => '194I',
        'tax_name'       => 'Rent on land or furniture etc (Reduced)',
        'tax_percentage' => 7.5,
        'is_reduced'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Section 194J - Professional / Technical Fees
    |--------------------------------------------------------------------------
    */
    [
        'tax_id'         => '3463444000000032054',
        'section'        => '194J',
        'tax_name'       => 'Professional Fees',
        'tax_percentage' => 10,
        'is_reduced'     => false,
    ],
    [
        'tax_id'         => '3463444000000032068',
        'section'        => '194J',
        'tax_name'       => 'Professional Fees (Reduced)',
        'tax_percentage' => 7.5,
        'is_reduced'     => true,
    ],
    [
        'tax_id'         => '3463444000000032070',
        'section'        => '194J',
        'tax_name'       => 'Technical Fees (2%)',
        'tax_percentage' => 2,
        'is_reduced'     => false,
    ],

];