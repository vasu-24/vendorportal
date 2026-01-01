<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contract Templates Configuration
    |--------------------------------------------------------------------------
    |
    | Define all contract templates with their types and properties.
    | type: 'paid' = requires configuration (rate, qty, contract value, invoice)
    | type: 'non_paid' = no configuration needed, no invoice
    |
    */

    'templates' => [

        // ========== PAID TEMPLATES (with config & invoice) ==========
        
        'consulting' => [
            'file' => 'Consulting_Agreement_Bold.docx',
            'type' => 'paid',
            'label' => 'Consulting Agreement',
            'description' => 'Agreement for hiring individual consultants',
            'allows_invoice' => true,
            'requires_config' => true,
        ],

        'msa' => [
            'file' => 'MSA_Template_Bold.docx',
            'type' => 'paid',
            'label' => 'Master Service Agreement (MSA)',
            'description' => 'Agreement for hiring service provider companies',
            'allows_invoice' => true,
            'requires_config' => true,
        ],

        // ========== NON-PAID TEMPLATES (no config, no invoice) ==========

        'fide_mou' => [
            'file' => 'FIDE_Agreement_Bold.docx',
            'type' => 'non_paid',
            'label' => 'FIDE MOU',
            'description' => 'Memorandum of Understanding for partnerships',
            'allows_invoice' => false,
            'requires_config' => false,
        ],

        'nda' => [
            'file' => 'NDA_Bold.docx',
            'type' => 'non_paid',
            'label' => 'Non-Disclosure Agreement (NDA)',
            'description' => 'Confidentiality agreement for protecting sensitive information',
            'allows_invoice' => false,
            'requires_config' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Contract Statuses
    |--------------------------------------------------------------------------
    |
    | Define contract lifecycle statuses for e-signature flow
    |
    */

    'statuses' => [
        'draft' => [
            'label' => 'Draft',
            'color' => 'secondary',
            'description' => 'Contract created but not sent for signature',
        ],
        'pending_signature' => [
            'label' => 'Pending Signature',
            'color' => 'warning',
            'description' => 'Contract sent for e-signature, awaiting signatures',
        ],
        'signed' => [
            'label' => 'Signed',
            'color' => 'success',
            'description' => 'Contract signed by all parties',
        ],
        'active' => [
            'label' => 'Active',
            'color' => 'primary',
            'description' => 'Contract is active and in effect',
        ],
        'expired' => [
            'label' => 'Expired',
            'color' => 'dark',
            'description' => 'Contract has expired',
        ],
        'terminated' => [
            'label' => 'Terminated',
            'color' => 'danger',
            'description' => 'Contract terminated before expiry',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (accessed via config('contracts.helpers'))
    |--------------------------------------------------------------------------
    */

    'helpers' => [
        
        // Get all paid template filenames
        'paid_templates' => [
            'Consulting_Agreement_Bold.docx',
            'MSA_Template_Bold.docx',
        ],

        // Get all non-paid template filenames
        'non_paid_templates' => [
            'FIDE_Agreement_Bold.docx',
            'NDA_Bold.docx',
        ],

    ],

];