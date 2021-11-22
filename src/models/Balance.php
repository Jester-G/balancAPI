<?php

class Balance
{
    // DB stuff
    private $conn;
    private $table = 'balance';

    // output properties
    public $userName;
    public $userId;
    public $amount;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }
    /**
     * Get info about the balance of single user
     * @param int $userId
     * @return bool
     */
    public function userExsist(int $userId)
    {
        $query = "SELECT 
                u.id, u.user_name, b.amount 
              FROM users AS u 
              LEFT JOIN 
                {$this->table} AS b ON u.id=b.user_id 
              WHERE 
                u.id= :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $this->userId = $row['id'];
            $this->amount = $row['amount'];
            $this->userName = $row['user_name'];

            return true;
        }

        return false;
    }
    /**
     * Updates the user's balance if the user exist in table 'balance'
     * @param int $userId
     * @param int $amount
     * @return bool
     */
    public function setBalance(int $userId, int $amount)
    {
        $query = "UPDATE {$this->table}
            SET              
              amount = :amount
            WHERE user_id = :id;";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':amount', $amount);
        $stmt->bindValue(':id', $userId);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }
    /**
     * Adds a new row in table 'balance' if the user doesn't exist in table
     * @param int $userId
     * @param int $amount
     * @return bool
     */
    public function createBalance(int $userId, int $amount)
    {
        $query = "INSERT INTO {$this->table}
                SET
                  user_id=:id,
                  amount=:amount;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->bindValue(':amount', $amount);

        if ($stmt->execute()) {
            return true;
        }

        printf("Error %s.\n", $stmt->error);
        return false;
    }

    public function exchange()
    {
        // set API Endpoint and API key
        $endpoint = 'latest.json';
        $appId = '15465b6116604636a712053e16d1fb48';
        $base = 'USD';
        $symbols = 'RUB';

        // Initialize CURL:
        $ch = curl_init('https://openexchangerates.org/api/'.$endpoint.'?app_id='.$appId.'&base='.$base.'&symbols='.$symbols);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        // Access the exchange rate
        return $exchangeRates['rates'][$symbols];
    }
}
