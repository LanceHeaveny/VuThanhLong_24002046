<?php

class User
{
    public int $id = 0;
    public string $username;
    public string $password;
    public string $address;
    private PDO $conn;

    public function __construct(PDO $conn, string $username, string $password, string $address)
    {
        $this->validateRequiredText($username, 'Tên đăng nhập');
        $this->validateRequiredText($address, 'Địa chỉ');

        $this->conn = $conn;
        $this->username = $username;
        $this->address = $address;
        $this->setPassword($password);
    }

    public function create(): int
    {
        $this->validateRequiredText($this->username, 'Tên đăng nhập');
        $this->validateRequiredText($this->address, 'Địa chỉ');

        if (password_get_info($this->password)['algo'] === null) {
            $this->setPassword($this->password);
        }

        $statement = $this->conn->prepare(
            'INSERT INTO users (username, password, address) VALUES (:username, :password, :address)'
        );
        $statement->execute([
            ':username' => $this->username,
            ':password' => $this->password,
            ':address' => $this->address,
        ]);

        $this->id = (int) $this->conn->lastInsertId();

        return $this->id;
    }

    public function setPassword(string $password): void
    {
        if ($password === '') {
            throw new InvalidArgumentException('Mật khẩu không được để trống.');
        }

        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    private function validateRequiredText(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException($field . ' không được để trống.');
        }
    }
}