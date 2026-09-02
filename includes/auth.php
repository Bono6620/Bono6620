<?php
require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        if (empty($_SESSION['user_id'])) {
            $user = null;
        } else {
            $stmt = get_db()->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function current_role(): string
{
    $user = current_user();
    return $user['role'] ?? 'viewer';
}

function can_edit(): bool
{
    return in_array(current_role(), ['admin', 'editor'], true);
}

function is_admin(): bool
{
    return current_role() === 'admin';
}

function require_edit(): void
{
    require_login();
    if (!can_edit()) {
        http_response_code(403);
        exit('مالكش صلاحية تعديل، انت بصلاحية "مشاهدة فقط".');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('الصفحة دي للمدير بس.');
    }
}

function attempt_login(string $username, string $password): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        return $user;
    }

    return null;
}

function role_label(string $role): string
{
    return match ($role) {
        'admin' => 'مدير',
        'editor' => 'محرر',
        default => 'مشاهدة فقط',
    };
}
