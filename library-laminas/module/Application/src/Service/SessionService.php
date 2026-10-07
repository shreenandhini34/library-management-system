<?php

declare(strict_types=1);

namespace Application\Service;

class SessionService
{
    public function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login(string $username, int $userId): void
    {
        $this->start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
    }

    public function logout(): void
    {
        $this->start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        $this->start();
        return !empty($_SESSION['user_id']);
    }

    public function userId(): ?int
    {
        $this->start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function username(): string
    {
        $this->start();
        return isset($_SESSION['username']) ? (string) $_SESSION['username'] : '';
    }

    public function addFlash(string $type, string $message): void
    {
        $this->start();
        if (!isset($_SESSION['_flash'][$type])) {
            $_SESSION['_flash'][$type] = [];
        }
        $_SESSION['_flash'][$type][] = $message;
    }

    public function consumeFlash(string $type): array
    {
        $this->start();
        $messages = isset($_SESSION['_flash'][$type]) ? $_SESSION['_flash'][$type] : [];
        unset($_SESSION['_flash'][$type]);
        return is_array($messages) ? $messages : [];
    }

    public function csrfToken(): string
    {
        $this->start();
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public function validateCsrf(?string $token): bool
    {
        $this->start();
        return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}
