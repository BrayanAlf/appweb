<?php
// Conexión a la base de datos de Azure
$host = 'servidorpaas.database.windows.net';
$db   = 'empresa';
$user = 'azureusersql';
$pass = 'Azureuser-sql1';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Guardar nuevo trabajador
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar"])) {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];

    $sql = "INSERT INTO trabajadores (nombre, apellido, correo) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $apellido, $correo]);
}

// Obtener nombres únicos para el combo box
$nombres = [];
$stmt = $pdo->query("SELECT DISTINCT nombre FROM trabajadores ORDER BY nombre ASC");
while ($row = $stmt->fetch()) {
    $nombres[] = $row['nombre'];
}

// Consultar trabajadores filtrados por nombre
$trabajadoresFiltrados = [];

if (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '') {
    $nombreFiltro = $_GET['filtro_nombre'];
    $stmt = $pdo->prepare("SELECT * FROM trabajadores WHERE nombre = ? ORDER BY fecha_registro DESC");
    $stmt->execute([$nombreFiltro]);
} else {
    $stmt = $pdo->query("SELECT * FROM trabajadores ORDER BY fecha_registro DESC");
}

$trabajadoresFiltrados = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registro de Trabajadores</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1, h2 { color: #333; }
        form { margin-bottom: 30px; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #ccc; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

<div class="container">
    <h1>Registro de Trabajadores</h1>

    <form method="post">
        <input type="hidden" name="guardar" value="1">

        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>Apellido:</label>
        <input type="text" name="apellido" required>

        <label>Correo Electrónico:</label>
        <input type="email" name="correo" required>

        <button type="submit">Registrar Trabajador</button>
    </form>

    <h2>Filtrar Trabajadores por Nombre</h2>
    <form method="get">
        <label>Selecciona un nombre:</label>
        <select name="filtro_nombre">
            <option value="">-- Todos los trabajadores --</option>
            <?php foreach ($nombres as $n): ?>
                <option value="<?= htmlspecialchars($n) ?>" <?= (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $n) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($n) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <?php if (!empty($trabajadoresFiltrados)): ?>
        <h3>
            <?= isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '' 
                ? 'Trabajadores con nombre: ' . htmlspecialchars($_GET['filtro_nombre']) 
                : 'Todos los trabajadores registrados' ?>
        </h3>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Correo</th>
                <th>Fecha de Registro</th>
            </tr>
            <?php foreach ($trabajadoresFiltrados as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['nombre']) ?></td>
                    <td><?= htmlspecialchars($t['apellido']) ?></td>
                    <td><?= htmlspecialchars($t['correo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['fecha_registro'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif (isset($_GET['filtro_nombre'])): ?>
        <p>No se encontraron trabajadores con ese nombre.</p>
    <?php endif; ?>
</div>

</body>
</html>
