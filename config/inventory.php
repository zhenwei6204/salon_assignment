<?php

return [
   
    'low_stock_threshold' => 5,

    
    'admin_recipients' => [
        env('INVENTORY_ADMIN_EMAIL', 'admin@example.com'),
    ],
];