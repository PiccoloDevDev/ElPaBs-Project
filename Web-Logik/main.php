<?php
session_start();

// ===========================================
// CONFIGURACIÓN DE BASE DE DATOS - EDITAR ESTOS VALORES
// ===========================================
$db_host = 'localhost';        // Dirección del servidor (normalmente localhost con XAMPP)
$db_name = 'elpabssss'; // Nombre de tu base de datos
$db_user = 'root';            // Usuario de MySQL (por defecto 'root' en XAMPP)
$db_pass = '';                // Contraseña de MySQL (vacía por defecto en XAMPP)
// ===========================================

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] == 'register') {
        $username = trim($_POST['username']);
        
        if (empty($username)) {
            echo json_encode(['error' => 'El nombre de usuario no puede estar vacío']);
            exit;
        }
        
        try {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['error' => 'Este usuario ya existe']);
            } else {
                // Crear nuevo usuario
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, fecha_registro, ultima_sesion) VALUES (?, NOW(), NOW())");
                $stmt->execute([$username]);
                
                // Obtener datos del usuario recién creado
                $userId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['user'] = $user;
                
                echo json_encode([
                    'success' => 'Usuario creado exitosamente',
                    'user' => [
                        'username' => $user['username'],
                        'fecha_registro' => date('d/m/Y', strtotime($user['fecha_registro'])),
                        'ultima_sesion' => date('d/m/Y H:i', strtotime($user['ultima_sesion']))
                    ]
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'login') {
        $username = trim($_POST['username']);
        
        if (empty($username)) {
            echo json_encode(['error' => 'El nombre de usuario no puede estar vacío']);
            exit;
        }
        
        try {
            // Buscar usuario
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo json_encode(['error' => 'Usuario no encontrado']);
            } else {
                // Actualizar última sesión
                $stmt = $pdo->prepare("UPDATE usuarios SET ultima_sesion = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Obtener datos actualizados
                $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$user['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $_SESSION['user'] = $user;
                
                echo json_encode([
                    'success' => 'Sesión iniciada correctamente',
                    'user' => [
                        'username' => $user['username'],
                        'fecha_registro' => date('d/m/Y', strtotime($user['fecha_registro'])),
                        'ultima_sesion' => date('d/m/Y H:i', strtotime($user['ultima_sesion']))
                    ]
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'logout') {
        session_destroy();
        echo json_encode(['success' => 'Sesión cerrada']);
        exit;
    }
    
    if ($_POST['action'] == 'check_session') {
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            echo json_encode([
                'logged_in' => true,
                'user' => [
                    'username' => $user['username'],
                    'fecha_registro' => date('d/m/Y', strtotime($user['fecha_registro'])),
                    'ultima_sesion' => date('d/m/Y H:i', strtotime($user['ultima_sesion']))
                ]
            ]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Usuarios - PHP + MySQL</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 90%;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
            font-size: 2.5em;
        }

        .columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .column {
            padding: 30px;
            border: 2px solid #3498db;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .column h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background: #e74c3c;
            color: white;
        }

        .success {
            background: #27ae60;
            color: white;
        }

        .dashboard {
            display: none;
            text-align: center;
        }

        .dashboard h2 {
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .buttons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        .page-btn {
            padding: 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .page-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .page-btn.tienda {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .page-btn.tienda:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
        }

        .logout-btn {
            margin-top: 30px;
            background: #e74c3c;
            padding: 10px 20px;
            width: auto;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        @media (max-width: 768px) {
            .columns {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .buttons-grid {
                grid-template-columns: 1fr;
            }
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="loginSection">
            <h1>Sistema de Usuarios</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">PHP + MySQL</p>
            
            <div class="columns">
                <!-- Columna 1: Crear Usuario -->
                <div class="column">
                    <h2>Crear Nuevo Usuario</h2>
                    <form id="registerForm">
                        <div class="form-group">
                            <label for="newUsername">Nombre de Usuario:</label>
                            <input type="text" id="newUsername" name="newUsername" required>
                        </div>
                        <button type="submit" class="btn">Crear Usuario</button>
                        <div id="registerMessage" class="message" style="display: none;"></div>
                    </form>
                </div>

                <!-- Columna 2: Iniciar Sesión -->
                <div class="column">
                    <h2>Iniciar Sesión</h2>
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <button type="submit" class="btn">Iniciar Sesión</button>
                        <div id="loginMessage" class="message" style="display: none;"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dashboard después del login -->
        <div id="dashboard" class="dashboard">
            <h2>Bienvenido, <span id="currentUser"></span></h2>
            <p>Selecciona una opción:</p>
            
            <div class="buttons-grid">
                <button class="page-btn" onclick="navigateToPage('perfil')">
                    👤 Mi Perfil
                </button>
                <a href="moded.php" class="page-btn tienda">
                    🛒 Mi Tienda Digital
                </a>
                <button class="page-btn" onclick="navigateToPage('configuracion')">
                    ⚙️ Configuración
                </button>
                <button class="page-btn" onclick="navigateToPage('reportes')">
                    📊 Reportes
                </button>
                <button class="page-btn" onclick="navigateToPage('ayuda')">
                    ❓ Ayuda
                </button>
            </div>
            
            <button class="btn logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>

        <!-- Páginas individuales -->
        <div id="perfil" class="dashboard" style="display: none;">
            <h2>Mi Perfil</h2>
            <p>Aquí puedes ver tu información personal almacenada en la base de datos.</p>
            <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                <h3>Información del Usuario:</h3>
                <p><strong>Usuario:</strong> <span id="profileUser"></span></p>
                <p><strong>Fecha de registro:</strong> <span id="registerDate"></span></p>
                <p><strong>Última sesión:</strong> <span id="lastLogin"></span></p>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>

        <div id="configuracion" class="dashboard" style="display: none;">
            <h2>Configuración</h2>
            <p>Ajusta las preferencias de tu cuenta.</p>
            <div style="margin: 20px 0;">
                <div class="form-group">
                    <label>Tema de la aplicación:</label>
                    <select style="width: 100%; padding: 10px; margin-top: 5px;">
                        <option>Claro</option>
                        <option>Oscuro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notificaciones:</label>
                    <input type="checkbox" checked> Recibir notificaciones por email
                </div>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>

        <div id="reportes" class="dashboard" style="display: none;">
            <h2>Reportes</h2>
            <p>Visualiza estadísticas de la base de datos.</p>
            <div style="margin: 20px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="padding: 15px; background: #3498db; color: white; border-radius: 5px; text-align: center;">
                    <h4>Sesiones</h4>
                    <p style="font-size: 24px; margin: 10px 0;">15</p>
                </div>
                <div style="padding: 15px; background: #27ae60; color: white; border-radius: 5px; text-align: center;">
                    <h4>Usuarios Activos</h4>
                    <p style="font-size: 24px; margin: 10px 0;">8</p>
                </div>
                <div style="padding: 15px; background: #e74c3c; color: white; border-radius: 5px; text-align: center;">
                    <h4>Conexiones</h4>
                    <p style="font-size: 24px; margin: 10px 0;">142</p>
                </div>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>

        <div id="ayuda" class="dashboard" style="display: none;">
            <h2>Centro de Ayuda</h2>
            <p>Información sobre el sistema PHP + MySQL.</p>
            <div style="margin: 20px 0; text-align: left;">
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Cómo funciona la base de datos?</h4>
                    <p>Los usuarios se almacenan en una tabla MySQL con campos: id, username, fecha_registro, ultima_sesion</p>
                </div>
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Las sesiones son seguras?</h4>
                    <p>Se usan sesiones PHP nativas ($_SESSION) para mantener el estado del usuario</p>
                </div>
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Cómo contacto soporte?</h4>
                    <p>Este es un sistema de demostración. Revisa el código PHP para entender la implementación</p>
                </div>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>
    </div>

    <script>
        let currentSession = null;

        // Verificar sesión al cargar la página
        window.addEventListener('load', function() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=check_session'
            })
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    loginUser(data.user);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Registro de nuevo usuario
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('newUsername').value.trim();
            const messageDiv = document.getElementById('registerMessage');
            const form = this;
            
            if (username === '') {
                showMessage(messageDiv, 'Por favor ingresa un nombre de usuario', 'error');
                return;
            }
            
            form.classList.add('loading');
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=register&username=${encodeURIComponent(username)}`
            })
            .then(response => response.json())
            .then(data => {
                form.classList.remove('loading');
                
                if (data.error) {
                    showMessage(messageDiv, data.error, 'error');
                } else {
                    showMessage(messageDiv, data.success, 'success');
                    document.getElementById('newUsername').value = '';
                    
                    setTimeout(() => {
                        loginUser(data.user);
                    }, 1000);
                }
            })
            .catch(error => {
                form.classList.remove('loading');
                showMessage(messageDiv, 'Error de conexión', 'error');
                console.error('Error:', error);
            });
        });

        // Inicio de sesión
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const messageDiv = document.getElementById('loginMessage');
            const form = this;
            
            if (username === '') {
                showMessage(messageDiv, 'Por favor ingresa un nombre de usuario', 'error');
                return;
            }
            
            form.classList.add('loading');
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=login&username=${encodeURIComponent(username)}`
            })
            .then(response => response.json())
            .then(data => {
                form.classList.remove('loading');
                
                if (data.error) {
                    showMessage(messageDiv, data.error, 'error');
                } else {
                    showMessage(messageDiv, data.success, 'success');
                    document.getElementById('username').value = '';
                    
                    setTimeout(() => {
                        loginUser(data.user);
                    }, 1000);
                }
            })
            .catch(error => {
                form.classList.remove('loading');
                showMessage(messageDiv, 'Error de conexión', 'error');
                console.error('Error:', error);
            });
        });

        function loginUser(user) {
            currentSession = user;
            document.getElementById('currentUser').textContent = user.username;
            document.getElementById('profileUser').textContent = user.username;
            document.getElementById('registerDate').textContent = user.fecha_registro;
            document.getElementById('lastLogin').textContent = user.ultima_sesion;
            
            // Ocultar login y mostrar dashboard
            document.getElementById('loginSection').style.display = 'none';
            document.getElementById('dashboard').style.display = 'block';
        }

        function navigateToPage(page) {
            // Ocultar dashboard principal
            document.getElementById('dashboard').style.display = 'none';
            // Mostrar página específica
            document.getElementById(page).style.display = 'block';
        }

        function goBack() {
            // Ocultar todas las páginas específicas
            ['perfil', 'configuracion', 'reportes', 'ayuda'].forEach(page => {
                document.getElementById(page).style.display = 'none';
            });
            // Mostrar dashboard principal
            document.getElementById('dashboard').style.display = 'block';
        }

        function logout() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=logout'
            })
            .then(response => response.json())
            .then(data => {
                currentSession = null;
                
                // Ocultar todas las páginas y mostrar login
                ['dashboard', 'perfil', 'configuracion', 'reportes', 'ayuda'].forEach(page => {
                    document.getElementById(page).style.display = 'none';
                });
                
                document.getElementById('loginSection').style.display = 'block';
                
                // Limpiar mensajes
                document.getElementById('registerMessage').style.display = 'none';
                document.getElementById('loginMessage').style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function showMessage(element, text, type) {
            element.textContent = text;
            element.className = 'message ' + type;
            element.style.display = 'block';
            
            setTimeout(() => {
                element.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>