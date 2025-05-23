<?php
// Direct database access script to update product images
// This script bypasses Laravel's routing system

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get database credentials from .env file
function getEnvVars() {
    $envFile = dirname(__DIR__) . '/.env';
    if (!file_exists($envFile)) {
        return [
            'error' => 'ENV file not found',
            'path' => $envFile
        ];
    }

    $vars = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $vars[trim($key)] = trim($value);
        }
    }
    
    return $vars;
}

// Connect to the database
function connectToDatabase() {
    $env = getEnvVars();
    
    if (isset($env['error'])) {
        return $env;
    }
    
    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? '3306';
    $database = $env['DB_DATABASE'] ?? 'laravel';
    $username = $env['DB_USERNAME'] ?? 'root';
    $password = $env['DB_PASSWORD'] ?? '';
    
    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$database}",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        return $pdo;
    } catch (PDOException $e) {
        return [
            'error' => 'Database connection failed: ' . $e->getMessage(),
            'credentials' => [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
            ]
        ];
    }
}

// Update product images
function updateProductImages($pdo) {
    // Define product images data
    $products = [
        [
            'id' => 1,
            'image_filename' => 'smartphone-xs-pro.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Smartphone XS Pro on a wooden surface',
            'image_thumbnail' => 'smartphone-xs-pro-thumb.jpg',
        ],
        [
            'id' => 2,
            'image_filename' => 'ultra-hd-tv.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Ultra HD Smart TV in modern living room',
            'image_thumbnail' => 'ultra-hd-tv-thumb.jpg',
        ],
        [
            'id' => 3,
            'image_filename' => 'wireless-headphones.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Wireless noise-cancelling headphones in black',
            'image_thumbnail' => 'wireless-headphones-thumb.jpg',
        ],
        [
            'id' => 4,
            'image_filename' => 'digital-camera.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Professional digital camera with lens',
            'image_thumbnail' => 'digital-camera-thumb.jpg',
        ],
        [
            'id' => 5,
            'image_filename' => 'bluetooth-speaker.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Portable bluetooth speaker in blue color',
            'image_thumbnail' => 'bluetooth-speaker-thumb.jpg',
        ],
        [
            'id' => 6,
            'image_filename' => 'fitness-smartwatch.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Fitness smartwatch showing heart rate',
            'image_thumbnail' => 'fitness-smartwatch-thumb.jpg',
        ],
        [
            'id' => 7,
            'image_filename' => 'coffee-grinder.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Electric coffee grinder with coffee beans',
            'image_thumbnail' => 'coffee-grinder-thumb.jpg',
        ],
        [
            'id' => 8,
            'image_filename' => 'mechanical-keyboard.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Mechanical keyboard with RGB lighting',
            'image_thumbnail' => 'mechanical-keyboard-thumb.jpg',
        ],
        [
            'id' => 9,
            'image_filename' => 'office-chair.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Ergonomic office chair in black',
            'image_thumbnail' => 'office-chair-thumb.jpg',
        ],
        [
            'id' => 10,
            'image_filename' => 'smart-home-hub.jpg',
            'image_path' => 'images/products',
            'image_alt' => 'Smart home hub on a coffee table',
            'image_thumbnail' => 'smart-home-hub-thumb.jpg',
        ],
    ];
    
    $updatedCount = 0;
    $errors = [];
    
    try {
        foreach ($products as $product) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET image_filename = :image_filename,
                    image_path = :image_path,
                    image_alt = :image_alt,
                    image_thumbnail = :image_thumbnail
                WHERE id = :id
            ");
            
            $result = $stmt->execute([
                ':id' => $product['id'],
                ':image_filename' => $product['image_filename'],
                ':image_path' => $product['image_path'],
                ':image_alt' => $product['image_alt'],
                ':image_thumbnail' => $product['image_thumbnail']
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $updatedCount++;
            }
        }
        
        return [
            'status' => 'success',
            'message' => "Updated {$updatedCount} products with image data",
        ];
    } catch (PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to update products: ' . $e->getMessage()
        ];
    }
}

// Main execution
$connection = connectToDatabase();

if (isset($connection['error'])) {
    echo json_encode($connection);
    exit;
}

$result = updateProductImages($connection);
echo json_encode($result); 