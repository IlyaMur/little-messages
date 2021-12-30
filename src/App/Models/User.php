<?php

declare(strict_types=1);

namespace Ilyamur\PhpMvc\App\Models;

use PDO;
use Ilyamur\PhpMvc\App\Mail;
use Ilyamur\PhpMvc\App\Token;
use Ilyamur\PhpMvc\Core\View;
use Ilyamur\PhpMvc\App\S3Helper;
use Ilyamur\PhpMvc\Config\Config;

class User extends \Ilyamur\PhpMvc\Core\Model
{
    const IMAGE_TYPE = 'avaImage';

    public array $errors = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    public function save(): bool
    {
        $this->validate();

        if (empty($this->errors)) {
            $token = new Token();
            $tokenHash = $token->getHash();
            $this->activationToken = $token->getValue();

            $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
            $sql = 'INSERT INTO users (name, email, password_hash, activation_hash)
                    VALUES (:name, :email, :password_hash, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $tokenHash, PDO::PARAM_STR);

            return $stmt->execute();
        }

        return false;
    }

    public function validate(): void
    {
        if (trim($this->name) === '') {
            $this->errors[] = 'Name is required';
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }

        if (static::emailExists($this->email, $this->id ?? null)) {
            $this->errors[] = 'Email already taken';
        }

        if (empty($this->password)) {
            return;
        }

        if (strlen($this->password) < 6) {
            $this->errors[] = 'Please enter at least 6 characters for the password';
        }

        if (preg_match('/.*[a-z]+.*/i', $this->password) === 0) {
            $this->errors[] = 'Password needs at least one letter';
        }

        if (preg_match('/.*\d+.*/i', $this->password) === 0) {
            $this->errors[] = 'Password needs at least one number';
        }
    }

    public static function emailExists(string $email, ?string $ignoreId = null): bool
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id !== $ignoreId) {
                return true;
            }
        }

        return false;
    }

    public static function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM users
                WHERE email = :email';

        $stmt = static::getDB()->prepare($sql);
        $stmt->bindValue('email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        $user = $stmt->fetch();

        return $user ? $user : null;
    }

    public static function authenticate(string $email, string $password): ?static
    {
        $user = static::findByEmail($email);

        if (
            $user &&
            password_verify($password, $user->password_hash) &&
            $user->is_active
        ) {
            return $user;
        }

        return null;
    }

    public static function findById(int $userId): static|false
    {
        $sql = 'SELECT * from users
                WHERE id = :id';
        $db = static::getDB();

        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $userId, PDO::PARAM_INT);

        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        return $stmt->fetch();
    }

    public function rememberLogin(): bool
    {
        $token = new Token();
        $hashedToken = $token->getHash();

        $this->expiresAt = time() + 60 * 60 * 24 * 30;
        $this->rememberToken = $token->getValue();

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:tokenHash, :userId, :expiresAt)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('tokenHash', $hashedToken, PDO::PARAM_STR);
        $stmt->bindValue('userId', $this->id, PDO::PARAM_INT);
        $stmt->bindValue('expiresAt', date('Y-m-d H-i-s', $this->expiresAt), PDO::PARAM_STR);

        return $stmt->execute();
    }

    public static function sendPasswordRequest(string $email): void
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            }
        }
    }

    public function startPasswordReset(): bool
    {
        $token = new Token();
        $hashToken = $token->getHash();
        $this->passwordResetToken = $token->getValue();

        $expiryTimestamp = time() + 60 * 60 * 2;

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();

        $stmt = $db->prepare($sql);

        $stmt->bindValue(':token_hash', $hashToken, PDO::PARAM_STR);
        $stmt->bindValue(':expires_at', date('Y-m-d H:i:s', $expiryTimestamp), PDO::PARAM_STR);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    protected function sendPasswordResetEmail(): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/password/reset/" . $this->passwordResetToken;
        $text = View::getTemplate('password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('password/reset_email.html', ['url' => $url]);

        Mail::send(
            to: $this->email,
            subject: 'Password reset',
            name: $this->name,
            text: $text,
            html: $html
        );
    }

    public static function findByPasswordReset(string $token): ?static
    {
        $token = new Token($token);
        $hashedToken = $token->getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('token_hash', $hashedToken, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);

        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && strtotime($user->password_reset_expires_at) > time()) {
            return $user;
        }

        return null;
    }

    public function resetPassword(string $password): bool
    {
        $this->password = $password;
        $this->validate();

        if (!empty($this->errors)) {
            return false;
        }

        $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);

        $sql = 'UPDATE users
                SET password_hash = :password_hash,
                    password_reset_hash = NULL,
                    password_reset_expires_at = NULL
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $this->id, PDO::PARAM_INT);
        $stmt->bindValue('password_hash', $passwordHash, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function sendActivationEmail(): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        $url = "$protocol://" . $_SERVER['HTTP_HOST'] . "/signup/activate/" . $this->activationToken;
        $text = View::getTemplate('signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('signup/activation_email.html', ['url' => $url]);

        Mail::send(
            to: $this->email,
            subject: 'Account activation',
            name: $this->name,
            text: $text,
            html: $html
        );
    }

    public static function activate(string $value): bool
    {
        $token = new Token($value);
        $hashedToken = $token->getHash();

        if (!static::findActivationToken($hashedToken)) {
            return false;
        };

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = NULL
                WHERE activation_hash = :hashedToken';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('hashedToken', $hashedToken, PDO::PARAM_STR);

        return $stmt->execute();
    }

    protected static function findActivationToken(string $hashedToken): bool
    {
        $sql = 'SELECT activation_hash FROM users
                WHERE activation_hash = :hashedToken';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue('hashedToken', $hashedToken, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch() ? true : false;
    }

    private function validateInputImage(): void
    {
        switch ($this->file[static::IMAGE_TYPE]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file uploaded';
                break;
            case UPLOAD_ERR_INI_SIZE:
                $this->errors[] = 'File is too large';
                break;
            default:
                $this->errors[] = 'File not uploaded';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->file[static::IMAGE_TYPE]['tmp_name']);

        if (!in_array($mimeType, static::MIME_TYPES)) {
            $this->errors[] = 'Invalid format';
        }

        if ($this->file[static::IMAGE_TYPE]['size'] > 100000) {
            $this->errors[] = 'File is too large';
        }
    }

    public function update(array $data, array $imgsData): bool
    {
        $this->name = $data['name'];
        $this->email = $data['email'];

        foreach ($imgsData as $key => $val) {
            $this->file[$key] = $val;
        }

        //  validate only if value was provided
        if ($data['password'] !== '') {
            $this->password = $data['password'];
        }

        $this->validate();
        $isFileUploaded = file_exists($this->file[static::IMAGE_TYPE]['tmp_name']);

        if ($isFileUploaded) {
            $this->validateInputImage();
        }

        if (empty($this->errors)) {
            if ($isFileUploaded) {
                $this->generateUploadDestination(static::IMAGE_TYPE);

                $imgUrl = Config::AWS_STORING ? $this->saveToS3(type: static::IMAGE_TYPE) : $this->file['destination'];
            }

            $sql = 'UPDATE users
                    SET name = :name, email = :email';

            if (isset($this->password)) {
                $sql .= ', password_hash = :password_hash';
            }

            if (isset($imgUrl)) {
                $sql .= ', ava_link = :ava_link';
            }

            $sql .= " WHERE id = :id";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            if (isset($this->password)) {
                $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
                $stmt->bindValue('password_hash', $passwordHash, PDO::PARAM_STR);
            }

            if (isset($imgUrl)) {
                $stmt->bindValue('ava_link', $imgUrl, PDO::PARAM_STR);
            }

            $stmt->bindValue('name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue('email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue('id', $this->id, PDO::PARAM_INT);

            $isCorrect = $stmt->execute();

            if (
                isset($this->ava_link) &&
                isset($imgUrl) &&
                $isCorrect
            ) {
                static::deleteFromStorage($this->ava_link, static::IMAGE_TYPE);
            }

            return $isCorrect;
        }

        return false;
    }
}
