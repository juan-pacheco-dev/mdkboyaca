<?php
// login.php CORREGIDO

// 1. Cargar configuración y verificar si ya hay una sesión
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';

// Si el usuario ya está logueado, se redirige a su panel
if (isset($_SESSION['user'])) {
    $redirect_url = ($_SESSION['user']['rol'] === 'admin') ? 'inicio_admin.php' : 'student.php';
    header('Location: ' . $redirect_url);
    exit;
}

$error = '';

// 2. Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    if ($u === '' || $p === '') {
        $error = 'Por favor, ingresa usuario y contraseña.';
    } else {
        // 3. Buscar al usuario en la base de datos
        $stmt = mysqli_prepare($mysqli, "
            SELECT ID_PERSONA, USUARIO, CONTRASENA, ID_ROL, ID_ESTADO 
            FROM persona 
            WHERE USUARIO = ? 
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 's', $u);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        // 4. Verificar credenciales
        if ($row) {
            if (password_verify($p, $row['CONTRASENA'])) {
                // Verificar estado (1=Activo, 2=Inactivo, 3=Suspendido, 4=Retirado)
                // Si el usuario dijo que "no le muestra el modal", quizas lo mejor es redirigir a una pagina de "inactivo"
                // o bloquear el login.
                // PERO el usuario dijo: "me sigue dejando ingresar al panel del estudiante y no me muestra el modal de que no ha pagado"
                // Esto implica que quiere que ENTREN pero vean el modal.
                // Sin embargo, si estn inactivos, quizs no deberan poder navegar.
                // Voy a permitir el login pero en la sesion guardar el estado, para que cada pagina pueda decidir.
                // O mejor aun, si esta inactivo, lo redirijo a student.php donde DEBERIA salir el modal.

                // Vamos a guardar el estado en la sesion tambien
                $_SESSION['user'] = [
                    'id' => (int) $row['ID_PERSONA'],
                    'usuario' => $row['USUARIO'],
                    'rol' => ((int) $row['ID_ROL'] === 1) ? 'admin' : 'estudiante',
                    'estado' => (int) $row['ID_ESTADO']
                ];

                // Redirigir
                $redirect_url = ($_SESSION['user']['rol'] === 'admin') ? 'inicio_admin.php' : 'student.php';
                header('Location: ' . $redirect_url);
                exit;

            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="tienda.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="img/favicon-96x96.png" sizes="96x96" type="image/png">
</head>

<body class="login-body">

    <!-- Contenedor principal para empujar el footer -->
    <div class="main-wrapper">

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

        <!-- FORMULARIO LOGIN -->
        <form class="form" method="post" action="login.php" autocomplete="on">
            <h2 class="heading">Iniciar Sesión</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"
                    style="color: red; text-align: center; background: #ffebee; padding: 10px; border-radius: 6px; margin-bottom:15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Usuario" class="input-field" required
                    autocomplete="username">
            </div>

            <div class="field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" class="input-field" required
                    autocomplete="current-password">
            </div>

            <div class="btn">
                <button type="submit" class="button1 login-button">Entrar</button>
                <a href="index.php" class="button2"
                    style="display: block; text-align: center; text-decoration: none; padding: 12px 0;">Cancelar</a>
            </div>

            <a href="contrasena_olvidada.php" class="button3" style="text-align: center;">¿Olvidaste tu contraseña?</a>

        </form>

    </div> <!-- Fin main-wrapper -->

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