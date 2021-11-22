<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../models/Balance.php';
require_once __DIR__ . '/../../models/Transaction.php';
// DB & connect
$database = new Database();
$db = $database->connect();


$balance = new Balance($db);
$transaction = new Transaction($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));
$id = $data->id;
$amount = $data->amount;

if (!preg_match('/^-?[1-9]\d{0,8}?$/', $amount)) {
    http_response_code(400);

    $message = 'The amount must be an integer and start from 1';
    if (strlen($amount) > 9) {
        $message = 'The amount must be less than 10 symbols';
    }

    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => $message,
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}
if (!$balance->userExsist($id)) {
    http_response_code(400);
    echo json_encode([
        'message' => 'User id does not exist'
    ], JSON_PRETTY_PRINT);
    exit;
}

$amount += $balance->amount;

if ($amount < 0) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'The balance can\'t be less than 0',
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

if(is_null($balance->amount)) {
    $balance->createBalance($id, $amount);
} else {
    $balance->setBalance($id, $amount);
}

$formattedAmount = sprintf('%.2f',$data->amount/100 );

echo json_encode([
    'message' => "The balance has been updated by $formattedAmount RUB",
], JSON_PRETTY_PRINT);

$transaction->addTransaction($id, abs($data->amount), "The balance has been updated by $formattedAmount RUB");