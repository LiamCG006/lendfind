<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    
    // Validaciones básicas
    if (empty($username) || empty($password) || empty($email)) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        // En producción, aquí guardarías en la base de datos
        $_SESSION['success'] = '¡Cuenta creada exitosamente! Ahora puedes iniciar sesión.';
        header('Location: login.php');
        exit;
    }
    
    // Mantener datos del formulario en caso de error
    $_SESSION['form_data'] = [
        'username' => $username,
        'email' => $email
    ];
}

$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema IA</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="ai-header">
                <div class="ai-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48">
                        <path fill="currentColor" d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 5.5V7H9V5.5L3 7V9L5 9.5V15.5L3 16V18L9 16.5V18H15V16.5L21 18V16L19 15.5V9.5L21 9Z"/>
                    </svg>
                </div>
                <h1>Registro</h1>
                <p>Crea tu cuenta en el sistema de IA</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" action="guardarRegistro.php" method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Elige un nombre de usuario"
                           value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="tu@email.com"
                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Mínimo 6 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Repite tu contraseña">
                </div>
                
                <button type="submit" class="login-btn">
                    <span>Crear Cuenta</span>
                </button>
                
                <div class="register-link">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
                </div>
                <?php
      if(isset($_GET['error'])):?>
     <p><?php
     echo $_GET['error'];?></p>
     <?php
     endif;
     echo $_GET['success'] ?? '';
     ?>
            </form>
        </div>
    </div>
</body>
</html>