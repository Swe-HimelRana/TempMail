<?php

namespace App;

use PDO;

class Database
{
    private static $pdo = null;

    public static function connect()
    {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../data/mail.db';
            self::$pdo = new PDO("sqlite:" . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::initialize();
        }
        return self::$pdo;
    }

    private static function initialize()
    {
        $sql = "CREATE TABLE IF NOT EXISTS emails (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            account_email TEXT,
            message_id TEXT UNIQUE,
            sender TEXT,
            recipient TEXT,
            subject TEXT,
            body_html TEXT,
            body_text TEXT,
            received_at DATETIME,
            is_read INTEGER DEFAULT 0
        )";
        self::$pdo->exec($sql);
        
        // Index for faster lookups by recipient
        self::$pdo->exec("CREATE INDEX IF NOT EXISTS idx_recipient ON emails (recipient)");
    }

    public static function saveEmail($data)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO emails 
            (account_email, message_id, sender, recipient, subject, body_html, body_text, received_at) 
            VALUES (:account_email, :message_id, :sender, :recipient, :subject, :body_html, :body_text, :received_at)");
        
        return $stmt->execute([
            ':account_email' => $data['account_email'],
            ':message_id' => $data['message_id'],
            ':sender' => $data['sender'],
            ':recipient' => $data['recipient'],
            ':subject' => $data['subject'],
            ':body_html' => $data['body_html'],
            ':body_text' => $data['body_text'],
            ':received_at' => $data['received_at']
        ]);
    }

    public static function getMessages($email)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT id, sender, recipient, subject, received_at, is_read FROM emails WHERE recipient = :email ORDER BY received_at DESC");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getAllMessages()
    {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT id, sender, recipient, subject, received_at, is_read FROM emails ORDER BY received_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getMessage($id)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT * FROM emails WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function deleteMessage($id)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("DELETE FROM emails WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public static function deleteAccount($email)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("DELETE FROM emails WHERE recipient = :email");
        return $stmt->execute([':email' => $email]);
    }



    public static function getAccounts($search = '', $limit = 20, $offset = 0)
    {
        $pdo = self::connect();
        $params = [];
        
        // Base condition: ignore invalid emails
        $conditions = ["recipient IS NOT NULL", "recipient != ''"];

        if ($search) {
            $conditions[] = "recipient LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $where = "WHERE " . implode(' AND ', $conditions);
        
        // Count total for pagination
        $countSql = "SELECT COUNT(DISTINCT recipient) FROM emails $where";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Fetch data
        $sql = "SELECT recipient, COUNT(*) as count, MAX(received_at) as last_seen, MIN(received_at) as first_seen 
                FROM emails 
                $where
                GROUP BY recipient 
                ORDER BY last_seen DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'total' => $total,
            'accounts' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ];
    }
}
