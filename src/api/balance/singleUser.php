<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../models/Balance.php';

// DB & connect
$database = new Database();
$db = $database->connect();


$balance = new Balance($db);

$currency = 'RUB';
$id = $_GET['id'] ?? '';

if($balance->userExsist($id)) {

    if (!empty($_GET['currency']) && (strtoupper($_GET['currency']) == 'USD')) {
        $currencyAmount = $balance->exchange();
        $balance->amount /= $currencyAmount;
        $currency = 'USD';
    }
    $formattedAmount = sprintf('%.2f', $balance->amount/100);

    $Arr = [
        'user_id' => $id,
        'user_name' => $balance->userName,
        'amount' => $formattedAmount,
        'currency' => $currency,
    ];
    
    echo json_encode($Arr, JSON_PRETTY_PRINT);
} else {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'User id does not exist'
        ]
    ], JSON_PRETTY_PRINT);
}