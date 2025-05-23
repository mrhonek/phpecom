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
    $dbConnection = getenv('DATABASE_URL') ?: getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'] ?? null;
    
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
    
    $driver = ($parsedUrl['scheme'] === 'postgres' || $parsedUrl['scheme'] === 'postgresql') ? 'pgsql' : $parsedUrl['scheme'];
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

// Get available database drivers
function getAvailableDrivers() {
    $pdoDrivers = PDO::getAvailableDrivers();
    
    $extLoaded = [
        'pgsql' => extension_loaded('pgsql'),
        'mysqli' => extension_loaded('mysqli'),
        'sqlite3' => extension_loaded('sqlite3'),
    ];
    
    return [
        'pdo_drivers' => $pdoDrivers,
        'extensions' => $extLoaded
    ];
}

// Connect to the database using a direct connection string
function connectToDatabase() {
    $credentials = getDbCredentials();
    
    if (isset($credentials['error'])) {
        return $credentials;
    }
    
    // Check what database drivers we have available
    $availableDrivers = getAvailableDrivers();
    
    // Try PDO first
    if (in_array('pgsql', $availableDrivers['pdo_drivers']) && $credentials['driver'] === 'pgsql') {
        try {
            $dsn = "pgsql:host={$credentials['host']}";
            if (!empty($credentials['port'])) {
                $dsn .= ";port={$credentials['port']}";
            }
            if (!empty($credentials['database'])) {
                $dsn .= ";dbname={$credentials['database']}";
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
            
            return [
                'connection' => $pdo,
                'type' => 'pdo'
            ];
        } catch (PDOException $e) {
            // PDO failed, will try other methods
        }
    }
    
    // Try direct PostgreSQL connection
    if ($availableDrivers['extensions']['pgsql'] && ($credentials['driver'] === 'pgsql' || 
        $credentials['driver'] === 'postgres' || $credentials['driver'] === 'postgresql')) {
        
        $connString = "host={$credentials['host']} ";
        if (!empty($credentials['port'])) {
            $connString .= "port={$credentials['port']} ";
        }
        $connString .= "dbname={$credentials['database']} ";
        $connString .= "user={$credentials['username']} ";
        if (!empty($credentials['password'])) {
            $connString .= "password={$credentials['password']}";
        }
        
        $pgConn = @pg_connect($connString);
        
        if ($pgConn) {
            return [
                'connection' => $pgConn,
                'type' => 'pgsql'
            ];
        }
    }
    
    // If we got here, all connection attempts failed
    return [
        'error' => 'Database connection failed: No working connection method found',
        'credentials' => [
            'driver' => $credentials['driver'],
            'host' => $credentials['host'],
            'port' => $credentials['port'],
            'database' => $credentials['database'],
            'username' => $credentials['username'],
        ],
        'available_drivers' => $availableDrivers,
    ];
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
        ],
        'phpinfo' => [
            'version' => phpversion(),
            'loaded_extensions' => get_loaded_extensions(),
            'pdo_drivers' => PDO::getAvailableDrivers(),
        ]
    ];
}

// Check if the table has the required columns (PostgreSQL)
function checkTableColumns($connection, $connectionType) {
    try {
        if ($connectionType === 'pdo') {
            // For PDO
            $sql = "
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'products'
            ";
            
            $stmt = $connection->query($sql);
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else if ($connectionType === 'pgsql') {
            // For pg_* functions
            $result = pg_query($connection, "
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'products'
            ");
            
            $columns = [];
            while ($row = pg_fetch_assoc($result)) {
                $columns[] = $row['column_name'];
            }
        } else {
            return [
                'error' => 'Unsupported connection type',
                'type' => $connectionType
            ];
        }
        
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
    } catch (Exception $e) {
        return [
            'error' => 'Failed to check table columns: ' . $e->getMessage()
        ];
    }
}

// Update product images with different connection types
function updateProductImages($connectionData) {
    $connection = $connectionData['connection'];
    $connectionType = $connectionData['type'];
    
    // Check if the table has the required columns
    $columnsCheck = checkTableColumns($connection, $connectionType);
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
    
    try {
        if ($connectionType === 'pdo') {
            // Use PDO
            foreach ($products as $product) {
                $stmt = $connection->prepare("
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
        } else if ($connectionType === 'pgsql') {
            // Use pg_* functions
            foreach ($products as $product) {
                $sql = "
                    UPDATE products 
                    SET image_filename = $1,
                        image_path = $2,
                        image_alt = $3,
                        image_thumbnail = $4
                    WHERE id = $5
                ";
                
                $result = pg_query_params(
                    $connection,
                    $sql,
                    [
                        $product['image_filename'],
                        $product['image_path'],
                        $product['image_alt'],
                        $product['image_thumbnail'],
                        $product['id']
                    ]
                );
                
                if ($result && pg_affected_rows($result) > 0) {
                    $updatedCount++;
                }
            }
        } else {
            return [
                'error' => 'Unsupported connection type',
                'type' => $connectionType
            ];
        }
        
        return [
            'status' => 'success',
            'message' => "Updated {$updatedCount} products with image data",
            'connection_type' => $connectionType
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Failed to update products: ' . $e->getMessage(),
            'connection_type' => $connectionType
        ];
    }
}

// Main execution
$debug = isset($_GET['debug']) && $_GET['debug'] === '1';

if ($debug) {
    echo json_encode([
        'environment' => listEnvironmentVariables(),
        'credentials' => getDbCredentials(),
        'available_drivers' => getAvailableDrivers()
    ]);
    exit;
}

$connectionData = connectToDatabase();

if (isset($connectionData['error'])) {
    echo json_encode($connectionData);
    exit;
}

$result = updateProductImages($connectionData);
echo json_encode($result); 