<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Usuarios</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div id="loginSection">
            <h1>Sistema de Usuarios</h1>
            
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
            <p>Aquí puedes ver y editar tu información personal.</p>
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
            <p>Visualiza estadísticas y reportes de tu actividad.</p>
            <div style="margin: 20px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="padding: 15px; background: #3498db; color: white; border-radius: 5px; text-align: center;">
                    <h4>Sesiones</h4>
                    <p style="font-size: 24px; margin: 10px 0;">15</p>
                </div>
                <div style="padding: 15px; background: #27ae60; color: white; border-radius: 5px; text-align: center;">
                    <h4>Acciones</h4>
                    <p style="font-size: 24px; margin: 10px 0;">42</p>
                </div>
                <div style="padding: 15px; background: #e74c3c; color: white; border-radius: 5px; text-align: center;">
                    <h4>Errores</h4>
                    <p style="font-size: 24px; margin: 10px 0;">3</p>
                </div>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>

        <div id="ayuda" class="dashboard" style="display: none;">
            <h2>Centro de Ayuda</h2>
            <p>Encuentra respuestas a tus preguntas más frecuentes.</p>
            <div style="margin: 20px 0; text-align: left;">
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Cómo cambio mi contraseña?</h4>
                    <p>Ve a Configuración > Seguridad > Cambiar contraseña</p>
                </div>
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Cómo contacto soporte?</h4>
                    <p>Envía un email a soporte@ejemplo.com</p>
                </div>
                <div style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <h4>¿Cómo elimino mi cuenta?</h4>
                    <p>Contacta al administrador para procesos de eliminación</p>
                </div>
            </div>
            <button class="btn" onclick="goBack()">← Volver al Dashboard</button>
        </div>
    </div>

    <script>
        // Simulación de base de datos en memoria (en implementación real usar PHP + MySQL)
        let users = JSON.parse(localStorage.getItem('users') || '[]');
        let currentSession = null;

        // Registro de nuevo usuario
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('newUsername').value.trim();
            const messageDiv = document.getElementById('registerMessage');
            
            if (username === '') {
                showMessage(messageDiv, 'Por favor ingresa un nombre de usuario', 'error');
                return;
            }
            
            // Verificar si el usuario ya existe
            if (users.some(user => user.username === username)) {
                showMessage(messageDiv, 'Este usuario ya existe', 'error');
                return;
            }
            
            // Crear nuevo usuario
            const newUser = {
                username: username,
                registerDate: new Date().toLocaleDateString(),
                lastLogin: new Date().toLocaleString()
            };
            
            users.push(newUser);
            localStorage.setItem('users', JSON.stringify(users));
            
            showMessage(messageDiv, 'Usuario creado exitosamente', 'success');
            document.getElementById('newUsername').value = '';
            
            // Auto login después del registro
            setTimeout(() => {
                loginUser(newUser);
            }, 1000);
        });

        // Inicio de sesión
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const messageDiv = document.getElementById('loginMessage');
            
            if (username === '') {
                showMessage(messageDiv, 'Por favor ingresa un nombre de usuario', 'error');
                return;
            }
            
            // Buscar usuario
            const user = users.find(u => u.username === username);
            
            if (!user) {
                showMessage(messageDiv, 'Usuario no encontrado', 'error');
                return;
            }
            
            // Actualizar última sesión
            user.lastLogin = new Date().toLocaleString();
            localStorage.setItem('users', JSON.stringify(users));
            
            showMessage(messageDiv, 'Iniciando sesión...', 'success');
            document.getElementById('username').value = '';
            
            setTimeout(() => {
                loginUser(user);
            }, 1000);
        });

        function loginUser(user) {
            currentSession = user;
            document.getElementById('currentUser').textContent = user.username;
            document.getElementById('profileUser').textContent = user.username;
            document.getElementById('registerDate').textContent = user.registerDate;
            document.getElementById('lastLogin').textContent = user.lastLogin;
            
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
            currentSession = null;
            
            // Ocultar todas las páginas y mostrar login
            ['dashboard', 'perfil', 'configuracion', 'reportes', 'ayuda'].forEach(page => {
                document.getElementById(page).style.display = 'none';
            });
            
            document.getElementById('loginSection').style.display = 'block';
            
            // Limpiar mensajes
            document.getElementById('registerMessage').style.display = 'none';
            document.getElementById('loginMessage').style.display = 'none';
        }

        function showMessage(element, text, type) {
            element.textContent = text;
            element.className = 'message ' + type;
            element.style.display = 'block';
            
            setTimeout(() => {
                element.style.display = 'none';
            }, 3000);
        }

        // Verificar si hay sesión activa al cargar
        window.addEventListener('load', function() {
            const savedSession = localStorage.getItem('currentSession');
            if (savedSession) {
                const user = JSON.parse(savedSession);
                loginUser(user);
            }
        });

        // Guardar sesión
        window.addEventListener('beforeunload', function() {
            if (currentSession) {
                localStorage.setItem('currentSession', JSON.stringify(currentSession));
            } else {
                localStorage.removeItem('currentSession');
            }
        });
    </script>
</body>
</html>