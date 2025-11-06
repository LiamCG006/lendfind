<?php
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Variable para almacenar los datos de la tabla
$datos_tabla = '';
$titulo_tabla = 'Panel de Administración - Gestión de Usuarios';
$contenido_principal = '';

// Variables para el buscador
$termino_busqueda = '';
$campo_busqueda = 'todos'; // Por defecto busca en todos los campos

// Variables para paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Procesar búsqueda si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])) {
    $termino_busqueda = trim($_POST['termino_busqueda']);
    $campo_busqueda = $_POST['campo_busqueda'];
    // Reiniciar a página 1 cuando se realiza una nueva búsqueda
    $pagina_actual = 1;
    $offset = 0;
}

// Procesar formulario de agregar usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_usuario'])) {
    $usuario = trim($_POST['Nombre']);
    $contraseña = trim($_POST['Contraseña']);
    $rol = trim($_POST['Rol']);
    
    if (!empty($usuario) && !empty($contraseña) && !empty($rol)) {
        // Validar si el usuario ya existe
        $sql_check = "SELECT id_Usuario FROM usuario WHERE Nombre= ?";
        $stmt_check = $con->prepare($sql_check);
        $stmt_check->bind_param("s", $usuario);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>El usuario <strong>"' . htmlspecialchars($usuario) . '"</strong> ya existe en el sistema.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            // Insertar nuevo usuario
            $sql = "INSERT INTO usuario VALUES (default,?, ?, ?, 'activo')";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("sss",$rol, $usuario, $contraseña);
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Usuario agregado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>Error al agregar usuario.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            }
            $stmt->close();
        }
    } else {
        $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Por favor, completa todos los campos.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// Procesar deshabilitar usuario
if (isset($_GET['deshabilitar'])) {
    $id = intval($_GET['deshabilitar']);
    $sql = "UPDATE usuario SET Estado = 'inactivo' WHERE id_Usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-user-slash me-2"></i>Usuario deshabilitado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    $stmt->close();
}

// Procesar habilitar usuario
if (isset($_GET['habilitar'])) {
    $id = intval($_GET['habilitar']);
    $sql = "UPDATE usuario SET Estado = 'activo' WHERE id_Usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-user-check me-2"></i>Usuario habilitado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    $stmt->close();
}

// Procesar editar usuario - CORREGIDO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_usuario'])) {
    $id = intval($_POST['id_Usuario']);
    $usuario = trim($_POST['Nombre']);
    $contraseña = trim($_POST['Contraseña']);
    $rol = trim($_POST['Rol']);
    
    // Debug: mostrar datos recibidos
    error_log("Datos recibidos para editar - ID: $id, Usuario: $usuario, Rol: $rol");
    
    if (!empty($usuario) && !empty($rol)) {
        // Validar si el nuevo usuario ya existe (excluyendo el usuario actual)
        $sql_check = "SELECT id_Usuario FROM usuario WHERE Nombre = ? AND id_Usuario != ?";
        $stmt_check = $con->prepare($sql_check);
        $stmt_check->bind_param("si", $usuario, $id);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>El usuario <strong>"' . htmlspecialchars($usuario) . '"</strong> ya existe en el sistema.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
            $stmt_check->close();
        } else {
            $stmt_check->close();
            
            // Actualizar usuario
            if (!empty($contraseña)) {
                $sql = "UPDATE usuario SET Rol = ?, Nombre = ?, Contraseña = ? WHERE id_Usuario = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("sssi", $usuario, $contraseña, $rol, $id);
            } else {
                $sql = "UPDATE usuario SET Rol = ?, Nombre = ? WHERE id_Usuario = ?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssi", $usuario, $rol, $id);
            }
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>Usuario actualizado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $error_msg = $stmt->error;
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>Error al actualizar usuario: ' . htmlspecialchars($error_msg) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
                error_log("Error en actualización: " . $error_msg);
            }
            $stmt->close();
        }
    } else {
        $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Por favor, completa todos los campos obligatorios.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    
    // Redirigir para evitar reenvío del formulario
    header("Location: admin.php");
    exit();
}

// Construir la consulta SQL según los parámetros de búsqueda
$sql_where = "";
$sql_count_where = "";
$params = array();
$types = "";

if (!empty($termino_busqueda)) {
    switch ($campo_busqueda) {
        case 'id':
            $sql_where = " WHERE id_Usuario = ?";
            $sql_count_where = " WHERE id_Usuario = ?";
            $params[] = $termino_busqueda;
            $types .= "i";
            break;
        case 'usuario':
            $sql_where = " WHERE Nombre LIKE ?";
            $sql_count_where = " WHERE Nombre LIKE ?";
            $params[] = "%" . $termino_busqueda . "%";
            $types .= "s";
            break;
        case 'contraseña':
            $sql_where = " WHERE Contraseña LIKE ?";
            $sql_count_where = " WHERE Contraseña LIKE ?";
            $params[] = "%" . $termino_busqueda . "%";
            $types .= "s";
            break;
        case 'rol':
            $sql_where = " WHERE Rol = ?";
            $sql_count_where = " WHERE Rol = ?";
            $params[] = $termino_busqueda;
            $types .= "s";
            break;
        case 'todos':
            $sql_where = " WHERE id_Usuario = ? OR Nombre LIKE ? OR Contraseña LIKE ? OR Rol = ?";
            $sql_count_where = " WHERE id_Usuario = ? OR Nombre LIKE ? OR Contraseña LIKE ? OR Rol = ?";
            $params[] = $termino_busqueda;
            $params[] = "%" . $termino_busqueda . "%";
            $params[] = "%" . $termino_busqueda . "%";
            $params[] = $termino_busqueda;
            $types .= "isss";
            break;
    }
}

// Consulta para contar el total de registros
$sql_count = "SELECT COUNT(*) as total FROM usuario" . $sql_count_where;
$stmt_count = $con->prepare($sql_count);

if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}

$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_registros = $result_count->fetch_assoc()['total'];
$stmt_count->close();

// Calcular total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);
if ($pagina_actual > $total_paginas && $total_paginas > 0) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Consulta para obtener los registros de la página actual
$sql = "SELECT * FROM usuario" . $sql_where . " ORDER BY id_Usuario ASC LIMIT ? OFFSET ?";
$params[] = $registros_por_pagina;
$params[] = $offset;
$types .= "ii";

$stmt = $con->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $datos_tabla .= '<div class="table-responsive">';
    $datos_tabla .= '<table class="table table-striped table-hover">';
    $datos_tabla .= '<thead class="table-dark">';
    $datos_tabla .= '<tr>';
    $datos_tabla .= '<th>ID</th>';
    $datos_tabla .= '<th>Usuario</th>';
    $datos_tabla .= '<th>Contraseña</th>';
    $datos_tabla .= '<th>Rol</th>';
    $datos_tabla .= '<th>Estado</th>';
    $datos_tabla .= '<th>Acciones</th>';
    $datos_tabla .= '</tr>';
    $datos_tabla .= '</thead>';
    $datos_tabla .= '<tbody>';
    
    while ($fila = $resultado->fetch_assoc()) {
        $estado_badge = $fila['Estado'] == 'activo' ? 
            '<span class="badge bg-success">Activo</span>' : 
            '<span class="badge bg-secondary">Inactivo</span>';
        
        // Definir color del badge según el rol (0=Admin, 1=Usuario)
        $rol_badge = '';
        if ($fila['Rol'] == '0') {
            $rol_badge = '<span class="badge bg-danger">Admin (0)</span>';
        } else if ($fila['Rol'] == '1') {
            $rol_badge = '<span class="badge bg-primary">Usuario (1)</span>';
        } else {
            $rol_badge = '<span class="badge bg-secondary">' . htmlspecialchars($fila['Rol']) . '</span>';
        }
        
        $boton_estado = $fila['Estado'] == 'activo' ?
            '<button class="btn btn-sm btn-warning me-1" title="Deshabilitar" onclick="confirmarDeshabilitar(' . $fila['id_Usuario'] . ')">
                <i class="fas fa-user-slash"></i>
             </button>' :
            '<button class="btn btn-sm btn-success me-1" title="Habilitar" onclick="confirmarHabilitar(' . $fila['id_Usuario'] . ')">
                <i class="fas fa-user-check"></i>
             </button>';
        
        $datos_tabla .= '<tr>';
        $datos_tabla .= '<td>' . $fila['id_Usuario'] . '</td>';
        $datos_tabla .= '<td>' . htmlspecialchars($fila['Nombre']) . '</td>';
        $datos_tabla .= '<td>' . htmlspecialchars($fila['Contraseña']) . '</td>';
        $datos_tabla .= '<td>' . $rol_badge . '</td>';
        $datos_tabla .= '<td>' . $estado_badge . '</td>';
        $datos_tabla .= '<td>';
        $datos_tabla .= $boton_estado;
        $datos_tabla .= '<button class="btn btn-sm btn-primary me-1" title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditar" 
                         onclick="cargarDatosEditar(' . $fila['id_Usuario'] . ', \'' . htmlspecialchars($fila['Nombre']) . '\', \'' . htmlspecialchars($fila['Contraseña']) . '\', \'' . htmlspecialchars($fila['Rol']) . '\')">
                <i class="fas fa-edit"></i>
             </button>';
        $datos_tabla .= '</td>';
        $datos_tabla .= '</tr>';
    }
    
    $datos_tabla .= '</tbody></table></div>';
    
    // Contar usuarios activos e inactivos (sin paginación para estadísticas)
    $sql_count_estados = "SELECT Estado, COUNT(*) as total FROM usuario" . $sql_count_where . " GROUP BY Estado";
    $stmt_estados = $con->prepare($sql_count_estados);
    
    // Contar roles (sin paginación para estadísticas)
    $sql_count_roles = "SELECT Rol, COUNT(*) as total FROM usuario" . $sql_count_where . " GROUP BY Rol";
    $stmt_roles = $con->prepare($sql_count_roles);
    
    if (!empty($termino_busqueda)) {
        // Reconstruir parámetros para las consultas de estadísticas
        $params_estados = array();
        $types_estados = "";
        
        switch ($campo_busqueda) {
            case 'id':
                $params_estados[] = $termino_busqueda;
                $types_estados .= "i";
                break;
            case 'usuario':
                $params_estados[] = "%" . $termino_busqueda . "%";
                $types_estados .= "s";
                break;
            case 'contraseña':
                $params_estados[] = "%" . $termino_busqueda . "%";
                $types_estados .= "s";
                break;
            case 'rol':
                $params_estados[] = $termino_busqueda;
                $types_estados .= "s";
                break;
            case 'todos':
                $params_estados[] = $termino_busqueda;
                $params_estados[] = "%" . $termino_busqueda . "%";
                $params_estados[] = "%" . $termino_busqueda . "%";
                $params_estados[] = $termino_busqueda;
                $types_estados .= "isss";
                break;
        }
        
        $stmt_estados->bind_param($types_estados, ...$params_estados);
        $stmt_roles->bind_param($types_estados, ...$params_estados);
    }
    
    $stmt_estados->execute();
    $result_estados = $stmt_estados->get_result();
    $activos = 0;
    $inactivos = 0;
    
    while ($row = $result_estados->fetch_assoc()) {
        if ($row['Estado'] == 'activo') {
            $activos = $row['total'];
        } else {
            $inactivos = $row['total'];
        }
    }
    $stmt_estados->close();
    
    // Obtener estadísticas de roles
    $stmt_roles->execute();
    $result_roles = $stmt_roles->get_result();
    $roles_count = array();
    while ($row = $result_roles->fetch_assoc()) {
        $roles_count[$row['Rol']] = $row['total'];
    }
    $stmt_roles->close();
    
    // Generar paginación
    $paginacion = generarPaginacion($pagina_actual, $total_paginas, $termino_busqueda, $campo_busqueda);
    
    // Estadísticas
    $contenido_principal .= '
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>' . $total_registros . '</h4>
                            <p>Total Usuarios</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>' . $activos . '</h4>
                            <p>Usuarios Activos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>' . $inactivos . '</h4>
                            <p>Usuarios Inactivos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-slash fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>' . $total_paginas . '</h4>
                            <p>Páginas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas de Roles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Distribución de Roles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">';
    
    // Mostrar estadísticas de roles
    $total_admins = isset($roles_count['0']) ? $roles_count['0'] : 0;
    $total_usuarios = isset($roles_count['1']) ? $roles_count['1'] : 0;
    
    $contenido_principal .= '
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h3>' . $total_admins . '</h3>
                                    <p class="mb-0">Administradores (0)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>' . $total_usuarios . '</h3>
                                    <p class="mb-0">Usuarios (1)</p>
                                </div>
                            </div>
                        </div>';
    
    $contenido_principal .= '
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Gestión de Usuarios</h5>
                    <p class="card-text">Administra los usuarios del sistema desde esta sección.</p>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                        <i class="fas fa-plus me-2"></i>Agregar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Buscador -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-search me-2"></i>Buscar Usuarios
                    </h5>
                    <form method="POST" action="admin.php" class="row g-3">
                        <div class="col-md-5">
                            <label for="termino_busqueda" class="form-label">Término de búsqueda</label>
                            <input type="text" class="form-control" id="termino_busqueda" name="termino_busqueda" 
                                   value="' . htmlspecialchars($termino_busqueda) . '" placeholder="Ingrese término a buscar...">
                        </div>
                        <div class="col-md-5">
                            <label for="campo_busqueda" class="form-label">Buscar por</label>
                            <select class="form-select" id="campo_busqueda" name="campo_busqueda">
                                <option value="todos" ' . ($campo_busqueda == 'todos' ? 'selected' : '') . '>Todos los campos</option>
                                <option value="id" ' . ($campo_busqueda == 'id' ? 'selected' : '') . '>ID</option>
                                <option value="usuario" ' . ($campo_busqueda == 'usuario' ? 'selected' : '') . '>Usuario</option>
                                <option value="contraseña" ' . ($campo_busqueda == 'contraseña' ? 'selected' : '') . '>Contraseña</option>
                                <option value="rol" ' . ($campo_busqueda == 'rol' ? 'selected' : '') . '>Rol</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="buscar" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                        ' . (!empty($termino_busqueda) ? '
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Mostrando resultados para: <strong>"' . htmlspecialchars($termino_busqueda) . '"</strong> en <strong>' . 
                                ($campo_busqueda == 'todos' ? 'todos los campos' : $campo_busqueda) . '</strong></small>
                                <a href="admin.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Limpiar búsqueda
                                </a>
                            </div>
                        </div>' : '') . '
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información de paginación -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">
                        Mostrando <strong>' . (($pagina_actual - 1) * $registros_por_pagina + 1) . '</strong> 
                        a <strong>' . min($pagina_actual * $registros_por_pagina, $total_registros) . '</strong> 
                        de <strong>' . $total_registros . '</strong> registros
                    </span>
                </div>
                <div>
                    <span class="text-muted">
                        Página <strong>' . $pagina_actual . '</strong> de <strong>' . $total_paginas . '</strong>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    ' . $datos_tabla . '
    
    <!-- Paginación -->
    ' . $paginacion . '
    ';
} else {
    $contenido_principal .= '
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">No hay usuarios registrados</h4>
        <p>No se encontraron usuarios en la base de datos.</p>
        <hr>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregar">
            <i class="fas fa-plus me-2"></i>Agregar Primer Usuario
        </button>
    </div>';
}

// Función para generar la paginación
function generarPaginacion($pagina_actual, $total_paginas, $termino_busqueda, $campo_busqueda) {
    if ($total_paginas <= 1) return '';
    
    $paginacion = '<nav aria-label="Paginación de usuarios">';
    $paginacion .= '<ul class="pagination justify-content-center">';
    
    // Parámetros base para los enlaces
    $parametros_base = "admin.php";
    if (!empty($termino_busqueda)) {
        $parametros_base .= "?termino_busqueda=" . urlencode($termino_busqueda) . "&campo_busqueda=" . urlencode($campo_busqueda);
    } else {
        $parametros_base .= "?";
    }
    
    // Botón anterior
    if ($pagina_actual > 1) {
        $paginacion .= '<li class="page-item">';
        $paginacion .= '<a class="page-link" href="' . $parametros_base . '&pagina=' . ($pagina_actual - 1) . '" aria-label="Anterior">';
        $paginacion .= '<span aria-hidden="true">&laquo;</span>';
        $paginacion .= '</a></li>';
    } else {
        $paginacion .= '<li class="page-item disabled">';
        $paginacion .= '<a class="page-link" href="#" tabindex="-1" aria-disabled="true">';
        $paginacion .= '<span aria-hidden="true">&laquo;</span>';
        $paginacion .= '</a></li>';
    }
    
    // Números de página
    $inicio = max(1, $pagina_actual - 2);
    $fin = min($total_paginas, $pagina_actual + 2);
    
    for ($i = $inicio; $i <= $fin; $i++) {
        if ($i == $pagina_actual) {
            $paginacion .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $paginacion .= '<li class="page-item"><a class="page-link" href="' . $parametros_base . '&pagina=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        $paginacion .= '<li class="page-item">';
        $paginacion .= '<a class="page-link" href="' . $parametros_base . '&pagina=' . ($pagina_actual + 1) . '" aria-label="Siguiente">';
        $paginacion .= '<span aria-hidden="true">&raquo;</span>';
        $paginacion .= '</a></li>';
    } else {
        $paginacion .= '<li class="page-item disabled">';
        $paginacion .= '<a class="page-link" href="#" tabindex="-1" aria-disabled="true">';
        $paginacion .= '<span aria-hidden="true">&raquo;</span>';
        $paginacion .= '</a></li>';
    }
    
    $paginacion .= '</ul></nav>';
    return $paginacion;
}

// Mostrar mensajes
if (isset($mensaje)) {
    $contenido_principal = $mensaje . $contenido_principal;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Panel de Administración</title>
    <link href="admin.css" rel="stylesheet">
    <style>
       
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">Lend<span>Find</span></div>
            <nav>
                <ul>
                    <li><a href="inteligencia.php">Inicio</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenedor Principal - Ahora ocupa toda la página -->
    <div class="main-container">
        <div class="table-container">
            <div class="table-header">
                <?php echo $titulo_tabla; ?>
            </div>
            <div class="table-content">
                <?php echo $contenido_principal; ?>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Usuario -->
    <div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="admin.php" id="formAgregar">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarLabel">
                            <i class="fas fa-user-plus me-2"></i>Agregar Nuevo Usuario
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="Nombre" class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Nombre" name="Nombre" required 
                                   placeholder="Ingrese el nombre de usuario">
                            <div class="form-text">El nombre de usuario debe ser único.</div>
                        </div>
                        <div class="mb-3">
                            <label for="Contraseña" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Contraseña" name="Contraseña" required 
                                   placeholder="Ingrese la contraseña">
                        </div>
                        <div class="mb-3">
                            <label for="Rol" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="Rol" name="Rol" required>
                                <option value="">Seleccione un rol</option>
                                <option value="0">Administrador (0)</option>
                                <option value="1">Usuario (1)</option>
                            </select>
                            <div class="form-text">0 = Administrador, 1 = Usuario</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="agregar_usuario" class="btn btn-success">Agregar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="admin.php" id="formEditar">
                    <input type="hidden" id="editar_id" name="id_Usuario">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarLabel">
                            <i class="fas fa-user-edit me-2"></i>Editar Usuario
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editar_Nombre" class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_Nombre" name="Nombre" required>
                            <div class="form-text">El nombre de usuario debe ser único.</div>
                        </div>
                        <div class="mb-3">
                            <label for="editar_Contraseña" class="form-label">Contraseña (dejar vacío para no cambiar)</label>
                            <input type="text" class="form-control" id="editar_Contraseña" name="Contraseña" 
                                   placeholder="Dejar vacío para mantener la contraseña actual">
                        </div>
                        <div class="mb-3">
                            <label for="editar_Rol" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="editar_Rol" name="Rol" required>
                                <option value="">Seleccione un rol</option>
                                <option value="0">Administrador (0)</option>
                                <option value="1">Usuario (1)</option>
                            </select>
                            <div class="form-text">0 = Administrador, 1 = Usuario</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="logo">Lend<span>Find</span></div>
            <div class="footer-links">
                <a href="index.php">Chat</a>
                <a href="admin.php">Admin</a>
                <a href="#privacy">Privacidad</a>
                <a href="#terms">Términos</a>
                <a href="#help">Ayuda</a>
            </div>
            <p>&copy; 2025 LendFind. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarDeshabilitar(id) {
            if (confirm('¿Estás seguro de que deseas deshabilitar este usuario?')) {
                window.location.href = 'admin.php?deshabilitar=' + id;
            }
        }

        function confirmarHabilitar(id) {
            if (confirm('¿Estás seguro de que deseas habilitar este usuario?')) {
                window.location.href = 'admin.php?habilitar=' + id;
            }
        }

        function cargarDatosEditar(id, usuario, contraseña, rol) {
            console.log('Cargando datos para editar:', id, usuario, contraseña, rol);
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_Nombre').value = usuario;
            document.getElementById('editar_Contraseña').value = contraseña;
            document.getElementById('editar_Rol').value = rol;
        }

        // Limpiar formulario de agregar cuando se cierra el modal
        document.getElementById('modalAgregar').addEventListener('hidden.bs.modal', function () {
            document.getElementById('formAgregar').reset();
        });

        // Poner foco en el campo de búsqueda al cargar la página si hay búsqueda activa
        document.addEventListener('DOMContentLoaded', function() {
            const terminoBusqueda = '<?php echo $termino_busqueda; ?>';
            if (terminoBusqueda) {
                document.getElementById('termino_busqueda').focus();
            }
        });

        // Debug: mostrar datos del formulario antes de enviar
        document.getElementById('formEditar').addEventListener('submit', function(e) {
            console.log('Enviando formulario de edición:');
            console.log('ID:', document.getElementById('editar_id').value);
            console.log('Usuario:', document.getElementById('editar_Nombre').value);
            console.log('Contraseña:', document.getElementById('editar_Contraseña').value);
            console.log('Rol:', document.getElementById('editar_Rol').value);
        });
    </script>
</body>
</html>
<?php
// Cerrar conexión
if (isset($con)) {
    $con->close();
}
?>
