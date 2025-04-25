<?php

// Set content type to JSON
header('Content-Type: application/json');

// Determine the requested route
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$path_parts = explode('/', $path);

// Simple API router
$response = [
    'error' => 'Route not found',
    'status' => 404
];

// Root endpoint - API info
if ($path === '' || $path === 'api') {
    $response = [
        'message' => 'PHPecom API running successfully',
        'status' => 'ok',
        'version' => '1.0.0',
        'endpoints' => [
            '/api/products',
            '/api/products/{id}',
            '/api/health'
        ]
    ];
}

// Health check
if ($path === 'api/health') {
    $response = [
        'status' => 'ok',
        'version' => '1.0.0',
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Products endpoint - return sample data
if ($path === 'api/products') {
    $response = [
        'status' => 'success',
        'products' => [
            [
                'id' => 1,
                'name' => 'Product 1',
                'price' => 19.99,
                'description' => 'Product 1 description'
            ],
            [
                'id' => 2,
                'name' => 'Product 2',
                'price' => 29.99,
                'description' => 'Product 2 description'
            ],
            [
                'id' => 3,
                'name' => 'Product 3',
                'price' => 39.99,
                'description' => 'Product 3 description'
            ]
        ]
    ];
}

// Single product endpoint
if (count($path_parts) === 3 && $path_parts[0] === 'api' && $path_parts[1] === 'products' && is_numeric($path_parts[2])) {
    $product_id = (int)$path_parts[2];
    
    // Mock product data
    $product = null;
    $products = [
        1 => [
            'id' => 1,
            'name' => 'Product 1',
            'price' => 19.99,
            'description' => 'Product 1 description'
        ],
        2 => [
            'id' => 2,
            'name' => 'Product 2',
            'price' => 29.99,
            'description' => 'Product 2 description'
        ],
        3 => [
            'id' => 3,
            'name' => 'Product 3',
            'price' => 39.99,
            'description' => 'Product 3 description'
        ]
    ];
    
    if (isset($products[$product_id])) {
        $response = [
            'status' => 'success',
            'product' => $products[$product_id]
        ];
    } else {
        $response = [
            'error' => 'Product not found',
            'status' => 404
        ];
    }
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT); 