<?php

// Sample simple API response
header('Content-Type: application/json');
echo json_encode([
    'message' => 'PHPecom API running successfully',
    'status' => 'ok',
    'version' => '1.0.0'
]); 