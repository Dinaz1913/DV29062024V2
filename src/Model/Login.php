<?php

namespace Reelz222z\Cryptoexchange\Model;

use PDO;

class Login
{
    public static function authenticate(string $username, string $password): ?User
    {
        $pdo = Database::getInstance()->getConnection();
        $normalizedUsername = trim(strtolower($username));

        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(name) = :name");
        $stmt->execute([':name' => $normalizedUsername]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new \Exception('User not found in database.');
        }

        $hashedPassword = md5(trim($password));

        if ($hashedPassword !== $user['password']) {
            throw new \Exception('Password mismatch for user: ' . $normalizedUsername);
        }

        $wallet = Wallet::loadWallet($user['id']);
        return new User($user['name'], $wallet, $user['email'], $user['password'], (int)$user['id']);
    }
}
