<?php

require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';


//Si ya hay sesion lo manda al panel correspondiente 

if (isset($_SESSION['user'])) {
    $redirect_url = ($_SESSION['user']['rol'] == 'admin') ? 'admin.php' : 'student.php';
    header('Location: ' . $redirect_url);
    exit;
}

$error = '';
$correcto = '';

//Procesar el formulario

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['username'] ?? '');
    $documento = trim($_POST['documento'] ?? ''); // Nuevo campo DNI
    $contrasena_nueva = trim($_POST['new_password'] ?? '');
    $confirmar_nueva = trim($_POST['confirm_password'] ?? '');

    // ============================================
    // SEGURIDAD: Bloquear cambio de contraseña para 'admin'
    // ============================================
    if (strtolower($usuario) === 'admin') {
        $error = 'No es posible restablecer la contraseña. Contacte al administrador.';
    } elseif ($usuario === '' || $documento === '' || $contrasena_nueva == '' || $confirmar_nueva === '') {
        $error = 'Por favor, completa todos los campos.';
    } elseif ($contrasena_nueva !== $confirmar_nueva) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($contrasena_nueva) < 6) {
        $error = 'La contraseña debe tener mínimo 6 caracteres.';
    } elseif (!preg_match('/[0-9]/', $contrasena_nueva) || !preg_match('/[a-zA-Z]/', $contrasena_nueva)) {
        $error = 'La contraseña debe contener al menos una letra y un número.';
    } else {
        // ============================================
        // SEGURIDAD: Verificar USUARIO + DOCUMENTO
        // ============================================
        $stmt = mysqli_prepare($mysqli, "
            SELECT ID_PERSONA, USUARIO, DOCUMENTO
            FROM persona
            WHERE USUARIO = ? AND DOCUMENTO = ?
            LIMIT 1 
        ");

        mysqli_stmt_bind_param($stmt, "ss", $usuario, $documento);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($row) {
            // Crear el hash nuevo para la contraseña nueva
            $nuevo_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);

            // Hacer el update
            $stmt_upd = mysqli_prepare($mysqli, "
                UPDATE persona
                SET CONTRASENA = ?
                WHERE ID_PERSONA = ?
            ");

            mysqli_stmt_bind_param($stmt_upd, "si", $nuevo_hash, $row['ID_PERSONA']);
            $ok = mysqli_stmt_execute($stmt_upd);
            mysqli_stmt_close($stmt_upd);

            if ($ok) {
                $correcto = 'Tu contraseña se ha actualizado correctamente. Ahora puedes iniciar sesión.';
            } else {
                $error = 'Ocurrió un error al actualizar la contraseña. Intenta de nuevo.';
            }
        } else {
            // ============================================
            // SEGURIDAD: Mensaje genérico (no revelar si usuario o documento falló)
            // ============================================
            $error = 'Usuario o número de documento incorrecto.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="tienda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="img/favicon-96x96.png" sizes="96x96" type="image/png">
</head>

<body>

    <!-- NAVBAR TIENDA -->
    <nav class="navbar_tienda">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" />
        </a>

        <!-- Menú colapsable -->
        <ul class="nav-links">
            <li><a href="index.php">Volver</a></li>
        </ul>

        <!-- Botón hamburguesa -->
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <br><br><br><br>

    <!-- Usamos la misma clase .form para conservar estilos -->
    <form class="form" method="post" action="contrasena_olvidada.php" autocomplete="off">
        <h2 class="heading">Restablecer Contraseña</h2>

        <!-- Mensajes de error / éxito -->
        <?php if ($error): ?>
            <div class="alert alert-error"
                style="color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 6px;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($correcto): ?>
            <div class="alert alert-success"
                style="color: green; text-align: center; background: #e8f5e9; padding: 10px; border-radius: 6px;">
                <?= htmlspecialchars($correcto, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Usuario -->
        <div class="field">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Usuario" class="input-field" required>
        </div>

        <!-- Número de Documento / Cédula (Verificación) -->
        <div class="field">
            <i class="fas fa-id-card"></i>
            <input type="text" name="documento" placeholder="Número de Documento / Cédula" class="input-field" required>
        </div>

        <!-- Nueva contraseña -->
        <div class="field">
            <i class="fas fa-lock"></i>
            <input type="password" name="new_password" placeholder="Nueva contraseña" class="input-field" required>
        </div>

        <!-- Confirmar nueva contraseña -->
        <div class="field">
            <i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" placeholder="Confirmar contraseña" class="input-field"
                required>
        </div>

        <div class="btn">
            <button type="submit" class="button1 login-button">Actualizar contraseña</button>
            <a href="login.php" class="button2"
                style="display: block; text-align: center; text-decoration: none; padding: 12px 0;">
                Volver al inicio de sesión
            </a>
        </div>
    </form>

    <!-- FOOTER ESPECÍFICO LOGIN -->
    <footer class="footer-login">
        <?php include './temp/footer.php'; ?>
    </footer>

    <!-- Script hamburguesa -->
    <script>
        const hamburger = document.querySelector('.navbar_tienda .hamburger');
        const navLinks = document.querySelector('.navbar_tienda .nav-links');

        if (hamburger && navLinks) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('open');
            });
        }
    </script>
</body>

</html>