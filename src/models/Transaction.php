<?php

class Transaction
{
    // DB stuff
    private $conn;
    private $table = 'transactions';

    private $id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * @param int $userId
     * @param int $amount
     * @param string $message
     * @return bool
     */
    public function addTransaction(int $userId, int $amount, string $message)
    {
        $this->id = bin2hex(random_bytes(16));

        $query = "INSERT INTO {$this->table}
            SET              
              id = :id,  
              user_id = :user_id,                   
              amount = :amount,              
              message = :message;";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':id', $this->id);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':message', $message);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * @param int $userId
     * @param int $page
     * @param string $sort
     * @param string $orderBy
     * @return mixed
     */
    public function userTransactions(int $userId, int $page, string $sort, string $orderBy)
    {

        if (strtolower($sort) == 'sum') {
            $sort = 't.amount';
        } else {
            $sort = 't.created_at';
        }

        if (strtoupper($orderBy) == 'ASC') {
            $orderBy = 'ASC';
        } else {
            $orderBy = 'DESC';
        }

        $page = ($page - 1) * 5;

        $query = "SELECT
                u.user_name,
                t.id,
                t.amount,
                t.message,
                t.created_at
              FROM
              {$this->table} AS t
              LEFT JOIN
                users AS u ON t.user_id=u.id
              WHERE
                t.user_id=:id
              ORDER BY $sort $orderBy
              LIMIT 5 OFFSET $page;";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId);

        $stmt->execute();

        return $stmt;
    }

    /**
     * Finds the number of pages for the user
     * @param int $userId
     * @return false|float
     */
    public function getPagesCount(int $userId)
    {
        $query = "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE user_id=:id;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_CLASS);
        return ceil($result[0]->cnt / 5);
    }

}