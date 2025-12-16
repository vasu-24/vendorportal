<?php

return [
    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'redirect_uri' => env('ZOHO_REDIRECT_URI'),
    
    'accounts_url' => 'https://accounts.zoho.in',
    'api_url' => 'https://www.zohoapis.in',
    
    // Full access to Zoho Books
    'scopes' => [
        'ZohoBooks.fullaccess.all',
    ],

        'expense_account_id' => env('ZOHO_EXPENSE_ACCOUNT_ID'),
];