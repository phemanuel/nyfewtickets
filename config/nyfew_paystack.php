<?php
return [
    'secret_key' => env('NYFEW_PAYSTACK_SECRET_KEY'),
    'base_url' => 'https://api.paystack.co',
    'reservation_minutes' => 20,
    'enabled' => env('NYFEW_TICKET_SALES_ENABLED', false),
];
