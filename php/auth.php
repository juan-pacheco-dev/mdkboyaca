<?php
// php/auth.php — Helpers para verificar autenticación y roles

/**
 * Obtiene el rol del usuario actual de la sesión
 * @return string 'admin', 'estudiante'
 */
function current_role(): string
{
    if (!isset($_SESSION['user'])) {
        return 'guest';
    }
    return $_SESSION['user']['rol'] ?? 'guest';
}

/**
 * Verifica que haya sesión activa y opcionalmente un rol específico
 * Redirige a login.php si no está autenticado o no tiene el rol requerido
 * 
 * @param string|null $role Rol requerido ('admin', 'estudiante') o null para cualquier usuario autenticado
 */
function require_login(?string $role = null): void
{
    // Verificar que hay sesión
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    // Verificar rol si se especifica
    if ($role !== null && ($_SESSION['user']['rol'] ?? '') !== $role) {
        // Si el rol no coincide, redirigir (o mostrar error 403)
        // Por ahora redirigimos al login o al inicio según sea el caso
        header('Location: login.php');
        exit;
    }
}
?>