<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user'])) {
    header('Location: main.php');
    exit;
}

// ===========================================
// CONFIGURACIÓN DE BASE DE DATOS - DEBE SER IGUAL QUE EN index.php
// ===========================================
$db_host = 'localhost';        // Dirección del servidor
$db_name = 'elpabssss'; // Nombre de tu base de datos
$db_user = 'root';            // Usuario de MySQL
$db_pass = '';                // Contraseña de MySQL
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
    
    $usuario_id = $_SESSION['user']['id'];
    
    if ($_POST['action'] == 'add_product') {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $descripcion = trim($_POST['descripcion']);
        $categoria = trim($_POST['categoria']);
        $stock = intval($_POST['stock']);
        
        if (empty($nombre) || $precio <= 0) {
            echo json_encode(['error' => 'Nombre y precio son obligatorios']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO productos (usuario_id, nombre_producto, precio, descripcion, categoria, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $nombre, $precio, $descripcion, $categoria, $stock]);
            
            echo json_encode(['success' => 'Producto agregado exitosamente']);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error al agregar producto: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'get_products') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
            $stmt->execute([$usuario_id]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['products' => $productos]);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error al obtener productos: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'update_product') {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $descripcion = trim($_POST['descripcion']);
        $categoria = trim($_POST['categoria']);
        $stock = intval($_POST['stock']);
        
        if (empty($nombre) || $precio <= 0) {
            echo json_encode(['error' => 'Nombre y precio son obligatorios']);
            exit;
        }
        
        try {
            // Solo actualizar si el producto pertenece al usuario actual
            $stmt = $pdo->prepare("UPDATE productos SET nombre_producto = ?, precio = ?, descripcion = ?, categoria = ?, stock = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$nombre, $precio, $descripcion, $categoria, $stock, $id, $usuario_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => 'Producto actualizado exitosamente']);
            } else {
                echo json_encode(['error' => 'Producto no encontrado o no tienes permisos']);
            }
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'delete_product') {
        $id = intval($_POST['id']);
        
        try {
            // Solo eliminar si pertenece al usuario actual
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $usuario_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => 'Producto eliminado exitosamente']);
            } else {
                echo json_encode(['error' => 'Producto no encontrado o no tienes permisos']);
            }
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error al eliminar producto: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'get_stats') {
        try {
            // Total de productos
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $total_productos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Valor total del inventario
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(precio * stock), 0) as valor_total FROM productos WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $valor_inventario = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'];
            
            // Categorías
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT categoria) as total_categorias FROM productos WHERE usuario_id = ? AND categoria IS NOT NULL AND categoria != ''");
            $stmt->execute([$usuario_id]);
            $total_categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total_categorias'];
            
            // Producto más caro
            $stmt = $pdo->prepare("SELECT nombre_producto, precio FROM productos WHERE usuario_id = ? ORDER BY precio DESC LIMIT 1");
            $stmt->execute([$usuario_id]);
            $producto_mas_caro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'total_productos' => $total_productos,
                'valor_inventario' => number_format($valor_inventario, 2),
                'total_categorias' => $total_categorias,
                'producto_mas_caro' => $producto_mas_caro
            ]);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error al obtener estadísticas: ' . $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] == 'search_products') {
        $busqueda = trim($_POST['busqueda']);
        
        try {
            if (empty($busqueda)) {
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id = ? ORDER BY fecha_creacion DESC");
                $stmt->execute([$usuario_id]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE usuario_id = ? AND (nombre_producto LIKE ? OR descripcion LIKE ? OR categoria LIKE ?) ORDER BY fecha_creacion DESC");
                $busqueda_param = "%$busqueda%";
                $stmt->execute([$usuario_id, $busqueda_param, $busqueda_param, $busqueda_param]);
            }
            
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['products' => $productos]);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Error en la búsqueda: ' . $e->getMessage()]);
        }
        exit;
    }
}

$usuario = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Tienda Digital - <?php echo htmlspecialchars($usuario['username']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: rgba(255,255,255,0.95);
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: #2c3e50;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .welcome-section {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .welcome-section h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.blue {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .stat-card.green {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }

        .stat-card.orange {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .stat-card.purple {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }

        .stat-card h3 {
            font-size: 2.5em;
            margin: 10px 0;
            font-weight: bold;
        }

        .stat-card p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .controls-section {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .controls-grid {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 14px;
        }

        .products-section {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #ecf0f1;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .product-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #27ae60;
        }

        .product-category {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 10px;
        }

        .product-description {
            color: #7f8c8d;
            line-height: 1.5;
            margin-bottom: 15px;
            min-height: 60px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #95a5a6;
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .modal h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #bdc3c7;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 30px;
            cursor: pointer;
            color: #95a5a6;
            transition: color 0.3s;
        }

        .close:hover {
            color: #e74c3c;
        }

        .message {
            margin: 15px 0;
            padding: 15px;
            border-radius: 10px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .controls-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .product-actions {
                flex-direction: column;
            }

            .stat-card h3 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">🛒 Mi Tienda Digital</div>
            <div class="user-info">
                <span>👋 Hola, <strong><?php echo htmlspecialchars($usuario['username']); ?></strong></span>
                <a href="main.php" class="btn btn-secondary btn-small">← Volver al Sistema</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h1>Gestión de Productos</h1>
            <p>Administra tu inventario personal de manera fácil y eficiente</p>
            
            <div id="statsContainer" class="stats-grid">
                <div class="stat-card blue">
                    <p>📦 Total Productos</p>
                    <h3 id="totalProductos">0</h3>
                </div>
                <div class="stat-card green">
                    <p>💰 Valor Inventario</p>
                    <h3 id="valorInventario">$0</h3>
                </div>
                <div class="stat-card orange">
                    <p>📂 Categorías</p>
                    <h3 id="totalCategorias">0</h3>
                </div>
                <div class="stat-card purple">
                    <p>👑 Más Caro</p>
                    <h3 id="productoMasCaro">-</h3>
                </div>
            </div>
        </div>

        <div class="controls-section">
            <div class="controls-grid">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Buscar productos por nombre, categoría o descripción...">
                </div>
                <button class="btn btn-success" onclick="openProductModal()">
                    ➕ Nuevo Producto
                </button>
                <button class="btn btn-primary" onclick="loadProducts()">
                    🔄 Actualizar
                </button>
            </div>
        </div>

        <div class="products-section">
            <h2 style="color: #2c3e50; margin-bottom: 20px;">📋 Mis Productos</h2>
            <div id="productsContainer" class="products-grid">
                <!-- Los productos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar productos -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeProductModal()">&times;</span>
            <h2 id="modalTitle">➕ Agregar Producto</h2>
            <form id="productForm">
                <input type="hidden" id="productId" name="productId">
                
                <div class="form-group">
                    <label for="productName">📝 Nombre del Producto *</label>
                    <input type="text" id="productName" name="productName" required>
                </div>
                
                <div class="form-group">
                    <label for="productPrice">💰 Precio *</label>
                    <input type="number" id="productPrice" name="productPrice" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="productCategory">📂 Categoría</label>
                    <input type="text" id="productCategory" name="productCategory" placeholder="Ej: Electrónicos, Ropa, Hogar...">
                </div>
                
                <div class="form-group">
                    <label for="productStock">📦 Stock/Cantidad</label>
                    <input type="number" id="productStock" name="productStock" min="0" value="1">
                </div>
                
                <div class="form-group">
                    <label for="productDescription">📋 Descripción</label>
                    <textarea id="productDescription" name="productDescription" placeholder="Describe tu producto..."></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-success">💾 Guardar Producto</button>
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">❌ Cancelar</button>
                </div>
                
                <div id="productMessage" class="message" style="display: none;"></div>
            </form>
        </div>
    </div>

    <script>
        let currentEditingProduct = null;

        // Cargar datos al iniciar
        window.addEventListener('load', function() {
            loadProducts();
            loadStats();
        });

        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            const busqueda = this.value;
            searchProducts(busqueda);
        });

        // Formulario de productos
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const messageDiv = document.getElementById('productMessage');
            
            const data = {
                action: currentEditingProduct ? 'update_product' : 'add_product',
                nombre: formData.get('productName').trim(),
                precio: formData.get('productPrice'),
                categoria: formData.get('productCategory').trim(),
                stock: formData.get('productStock') || '1',
                descripcion: formData.get('productDescription').trim()
            };
            
            if (currentEditingProduct) {
                data.id = currentEditingProduct;
            }
            
            if (!data.nombre || !data.precio || parseFloat(data.precio) <= 0) {
                showMessage(messageDiv, 'Nombre y precio son obligatorios', 'error');
                return;
            }
            
            const body = Object.keys(data).map(key => 
                encodeURIComponent(key) + '=' + encodeURIComponent(data[key])
            ).join('&');
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showMessage(messageDiv, data.error, 'error');
                } else {
                    showMessage(messageDiv, data.success, 'success');
                    setTimeout(() => {
                        closeProductModal();
                        loadProducts();
                        loadStats();
                    }, 1000);
                }
            })
            .catch(error => {
                showMessage(messageDiv, 'Error de conexión', 'error');
                console.error('Error:', error);
            });
        });

        function loadProducts() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_products'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                displayProducts(data.products);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function displayProducts(products) {
            const container = document.getElementById('productsContainer');
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <h3>🛒 No tienes productos registrados</h3>
                        <p>¡Agrega tu primer producto para comenzar tu tienda digital!</p>
                        <button class="btn btn-success" onclick="openProductModal()" style="margin-top: 20px;">
                            ➕ Agregar Mi Primer Producto
                        </button>
                    </div>
                `;
                return;
            }
            
            let html = '';
            
            products.forEach(product => {
                const fechaCreacion = new Date(product.fecha_creacion).toLocaleDateString('es-ES');
                const descripcionCorta = product.descripcion ? 
                    (product.descripcion.length > 100 ? 
                        product.descripcion.substring(0, 100) + '...' : 
                        product.descripcion) : 
                    'Sin descripción';
                
                html += `
                    <div class="product-card">
                        <div class="product-header">
                            <div>
                                <div class="product-title">${product.nombre_producto}</div>
                                <div class="product-price">$${parseFloat(product.precio).toFixed(2)}</div>
                            </div>
                        </div>
                        
                        ${product.categoria ? `<div class="product-category">${product.categoria}</div>` : ''}
                        
                        <div class="product-description">${descripcionCorta}</div>
                        
                        <div class="product-meta">
                            <span>📦 Stock: ${product.stock}</span>
                            <span>📅 ${fechaCreacion}</span>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary btn-small" onclick="editProduct(${product.id})">
                                ✏️ Editar
                            </button>
                            <button class="btn btn-danger btn-small" onclick="deleteProduct(${product.id}, '${product.nombre_producto.replace(/'/g, "\\'")}')">
                                🗑️ Eliminar
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function loadStats() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_stats'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                document.getElementById('totalProductos').textContent = data.total_productos;
                document.getElementById('valorInventario').textContent = ' + data.valor_inventario;
                document.getElementById('totalCategorias').textContent = data.total_categorias;
                
                if (data.producto_mas_caro && data.producto_mas_caro.nombre_producto) {
                    document.getElementById('productoMasCaro').textContent = ' + parseFloat(data.producto_mas_caro.precio).toFixed(2);
                    document.getElementById('productoMasCaro').title = data.producto_mas_caro.nombre_producto;
                } else {
                    document.getElementById('productoMasCaro').textContent = '-';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function searchProducts(busqueda) {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=search_products&busqueda=${encodeURIComponent(busqueda)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                displayProducts(data.products);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function openProductModal(productId = null) {
            currentEditingProduct = productId;
            const modal = document.getElementById('productModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('productForm');
            
            if (productId) {
                title.textContent = '✏️ Editar Producto';
                loadProductForEdit(productId);
            } else {
                title.textContent = '➕ Agregar Producto';
                form.reset();
                document.getElementById('productId').value = '';
            }
            
            modal.style.display = 'block';
            document.getElementById('productMessage').style.display = 'none';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
            currentEditingProduct = null;
        }

        function loadProductForEdit(productId) {
            // Buscar el producto en los datos ya cargados
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_products'
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                
                const product = data.products.find(p => p.id == productId);
                if (product) {
                    document.getElementById('productName').value = product.nombre_producto;
                    document.getElementById('productPrice').value = product.precio;
                    document.getElementById('productCategory').value = product.categoria || '';
                    document.getElementById('productStock').value = product.stock;
                    document.getElementById('productDescription').value = product.descripcion || '';
                    document.getElementById('productId').value = product.id;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function editProduct(productId) {
            openProductModal(productId);
        }

        function deleteProduct(productId, productName) {
            if (!confirm(`¿Estás seguro de que quieres eliminar "${productName}"?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_product&id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    // Mostrar mensaje de éxito temporal
                    const tempMessage = document.createElement('div');
                    tempMessage.className = 'message success';
                    tempMessage.textContent = '✅ Producto eliminado exitosamente';
                    tempMessage.style.position = 'fixed';
                    tempMessage.style.top = '20px';
                    tempMessage.style.right = '20px';
                    tempMessage.style.zIndex = '1001';
                    tempMessage.style.minWidth = '300px';
                    document.body.appendChild(tempMessage);
                    
                    setTimeout(() => {
                        document.body.removeChild(tempMessage);
                    }, 3000);
                    
                    loadProducts();
                    loadStats();
                }
            })
            .catch(error => {
                alert('Error de conexión');
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

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeProductModal();
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProductModal();
            }
        });

        // Limpiar búsqueda con Enter
        document.getElementById('searchInput').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchProducts(this.value);
            }
        });

        // Auto-guardar al hacer cambios (opcional)
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            const busqueda = this.value;
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchProducts(busqueda);
            }, 300); // Esperar 300ms después de dejar de escribir
        });
    </script>
</body>
</html>