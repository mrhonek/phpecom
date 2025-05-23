<?php
// Direct database access script to update product images
// This script bypasses Laravel's routing system

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get database credentials from environment variables
function getDbCredentials() {
    // Try to get from environment variables (Railway sets these directly)
    $dbConnection = getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'] ?? null;
    
    // Check if the connection string is a full URL (postgres://user:pass@host:port/db)
    if ($dbConnection && strpos($dbConnection, '://') !== false) {
        return parseConnectionUrl($dbConnection);
    }
    
    $credentials = [
        'driver' => $dbConnection ?: 'pgsql',
        'host' => getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? null,
        'port' => getenv('DB_PORT') ?: $_ENV['DB_PORT'] ?? null,
        'database' => getenv('DB_DATABASE') ?: $_ENV['DB_DATABASE'] ?? null,
        'username' => getenv('DB_USERNAME') ?: $_ENV['DB_USERNAME'] ?? null,
        'password' => getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? null,
    ];
    
    // Check if we got credentials
    if (!$credentials['host'] || !$credentials['database'] || !$credentials['username']) {
        // Fall back to common values for PostgreSQL on Railway
        $credentials = [
            'driver' => 'pgsql',
            'host' => 'postgres.railway.internal',
            'port' => '5432',
            'database' => 'railway',
            'username' => 'postgres',
            'password' => getenv('PGPASSWORD') ?: $_ENV['PGPASSWORD'] ?? null,
        ];
    }
    
    return $credentials;
}

// Parse a database connection URL
function parseConnectionUrl($url) {
    $parsedUrl = parse_url($url);
    
    if ($parsedUrl === false) {
        return [
            'error' => 'Failed to parse connection URL',
            'url' => $url
        ];
    }
    
    $driver = ($parsedUrl['scheme'] === 'postgres') ? 'pgsql' : $parsedUrl['scheme'];
    $username = $parsedUrl['user'] ?? null;
    $password = $parsedUrl['pass'] ?? null;
    $host = $parsedUrl['host'] ?? null;
    $port = $parsedUrl['port'] ?? null;
    $database = ltrim($parsedUrl['path'] ?? '', '/');
    
    return [
        'driver' => $driver,
        'host' => $host,
        'port' => $port,
        'database' => $database,
        'username' => $username,
        'password' => $password,
    ];
}

// Connect to the database using a direct connection string
function connectToDatabase() {
    $credentials = getDbCredentials();
    
    if (isset($credentials['error'])) {
        return $credentials;
    }
    
    try {
        // For PostgreSQL
        if ($credentials['driver'] === 'pgsql') {
            $dsn = "pgsql:host={$credentials['host']}";
            if (!empty($credentials['port'])) {
                $dsn .= ";port={$credentials['port']}";
            }
            if (!empty($credentials['database'])) {
                $dsn .= ";dbname={$credentials['database']}";
            }
        } 
        // For MySQL
        else if ($credentials['driver'] === 'mysql') {
            $dsn = "mysql:host={$credentials['host']}";
            if (!empty($credentials['port'])) {
                $dsn .= ";port={$credentials['port']}";
            }
            if (!empty($credentials['database'])) {
                $dsn .= ";dbname={$credentials['database']}";
            }
        }
        // Default fallback
        else {
            $dsn = "{$credentials['driver']}:host={$credentials['host']}";
            if (!empty($credentials['port'])) {
                $dsn .= ";port={$credentials['port']}";
            }
            if (!empty($credentials['database'])) {
                $dsn .= ";dbname={$credentials['database']}";
            }
        }
        
        $pdo = new PDO(
            $dsn,
            $credentials['username'],
            $credentials['password'],
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
                'driver' => $credentials['driver'],
                'host' => $credentials['host'],
                'port' => $credentials['port'],
                'database' => $credentials['database'],
                'username' => $credentials['username'],
            ],
            'dsn' => $dsn ?? null,
        ];
    }
}

// List all environment variables (for debugging)
function listEnvironmentVariables() {
    $envVars = [];
    foreach ($_ENV as $key => $value) {
        if (strpos(strtolower($key), 'password') === false && 
            strpos(strtolower($key), 'secret') === false) {
            $envVars[$key] = $value;
        } else {
            $envVars[$key] = '[REDACTED]';
        }
    }
    
    $serverVars = [];
    foreach ($_SERVER as $key => $value) {
        if (strpos(strtolower($key), 'password') === false && 
            strpos(strtolower($key), 'secret') === false) {
            $serverVars[$key] = $value;
        } else {
            $serverVars[$key] = '[REDACTED]';
        }
    }
    
    return [
        'env' => $envVars,
        'server' => $serverVars,
        'getenv' => [
            'DB_CONNECTION' => getenv('DB_CONNECTION'),
            'DB_HOST' => getenv('DB_HOST'),
            'DB_PORT' => getenv('DB_PORT'),
            'DB_DATABASE' => getenv('DB_DATABASE'),
            'DB_USERNAME' => getenv('DB_USERNAME'),
            'PGPASSWORD' => getenv('PGPASSWORD') ? '[REDACTED]' : null,
            'DATABASE_URL' => getenv('DATABASE_URL') ? '[POSSIBLY SENSITIVE]' : null,
        ]
    ];
}

// Check if the table has the required columns
function checkTableColumns($pdo) {
    try {
        // For PostgreSQL
        $sql = "
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'products'
        ";
        
        $stmt = $pdo->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = [
            'id', 'image_filename', 'image_path', 'image_alt', 'image_thumbnail'
        ];
        
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            return [
                'error' => 'Missing required columns',
                'missing' => $missingColumns,
                'available' => $columns
            ];
        }
        
        return true;
    } catch (PDOException $e) {
        return [
            'error' => 'Failed to check table columns: ' . $e->getMessage()
        ];
    }
}

// Update product images
function updateProductImages($pdo) {
    // Check if the table has the required columns
    $columnsCheck = checkTableColumns($pdo);
    if ($columnsCheck !== true) {
        return $columnsCheck;
    }
    
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
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($debug) {
    echo json_encode([
        'environment' => listEnvironmentVariables(),
        'credentials' => getDbCredentials()
    ]);
    exit;
}

$connection = connectToDatabase();

if (isset($connection['error'])) {
    echo json_encode($connection);
    exit;
}

$result = updateProductImages($connection);
echo json_encode($result); 