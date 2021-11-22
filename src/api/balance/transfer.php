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

$fromAmount = 0;
$toAmount = 0;

$fromUserName = '';
$toUserName = '';

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));
$fromId = $data->from;
$toId = $data->to;
$amount = $data->amount;

if (!preg_match('/^[1-9]\d{0,8}?$/', $amount)) {
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


if(!$balance->userExsist($fromId)) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'The User id in field "from" does not exist'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}
$fromUserName = $balance->userName;

$fromAmount = $balance->amount;
$fromAmount -= $amount;

if ($fromAmount < 0) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'The balance can\'t be less than 0',
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

if(!$balance->userExsist($toId)) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'The User id in field "to" does not exist'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$toUserName = $balance->userName;

$toAmount = $balance->amount;
$toAmount += $amount;

$balance->setBalance($fromId, $fromAmount);

if (is_null($balance->amount)) {
    $balance->createBalance($toId, $toAmount);
} else {
    $balance->setBalance($toId, $toAmount);
}

$formattedAmount = sprintf('%.2f',$amount/100 );

$transaction->addTransaction($fromId, $amount, "$formattedAmount RUB was transferred to the user $toUserName");
$transaction->addTransaction($toId, $amount, "$formattedAmount RUB was received from the user $fromUserName");

echo json_encode([
    'message' => "$formattedAmount RUB was transferred from the balance of the user $fromUserName to the balance of the user $toUserName",
], JSON_PRETTY_PRINT);
