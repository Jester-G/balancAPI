<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../models/Transaction.php';

// DB & connect
$database = new Database();
$db = $database->connect();


$transaction = new Transaction($db);

$id = $_GET['id'] ?? '';
$page = $_GET['page'] ?? 1;
$sort = $_GET['sort'] ?? 'date';
$orderBy = $_GET['order'] ?? 'DESC';

// Get pages count
$pages = $transaction->getPagesCount($id);

if (!preg_match('/^[1-9]\d*?$/', $page)) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'The page number must start with 1',
            'count_of_pages' =>  $pages,
            ]
    ], JSON_PRETTY_PRINT);
    exit;
}

if ($page > $pages) {
    http_response_code(400);
    echo json_encode([
        'error' => [
            'status' => 400,
            'message' => 'Nothing to show',
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$result = $transaction->userTransactions($id, $page, $sort, $orderBy);


$Arr = [];
$Arr['data'] = [];

while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {

    extract($row);
    $formattedAmount = sprintf('%.2f', $amount / 100);

    $Item = [
        'user_name' => $user_name,
        'transaction_id' => $id,
        'amount' => $formattedAmount,
        'message' => $message,
        'created_at' => $created_at,
    ];

    array_push($Arr['data'], $Item);
}
$Arr['pages'] = "$page of $pages";

echo json_encode($Arr, JSON_PRETTY_PRINT);