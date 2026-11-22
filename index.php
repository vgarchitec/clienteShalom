<?php
/**
 * Shalom API - Portal de Envíos y Consultas
 * Entrada principal y orquestador de vistas modularizadas
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/shalom.php';

// Si se recibe una acción de API por GET o POST, delegar al router de API
if (isset($_GET['action'])) {
    require_once __DIR__ . '/api.php';
    exit;
}

// Cargar agencias agrupadas por departamento para el Cotizador (Select2)
$agencies_by_dept = [];
try {
    $db = get_db();
    $stmt = $db->query("SELECT ter_id, nombre, departamento, provincia, direccion FROM agencies ORDER BY departamento ASC, nombre ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dept = !empty($row['departamento']) ? strtoupper(trim($row['departamento'])) : 'OTRAS SEDES';
        $agencies_by_dept[$dept][] = $row;
    }
} catch (Exception $e) {
    // Continuar con array vacío si la tabla está en proceso de creación
}

$has_key = !empty(get_env_var('SHALOM_API_KEY'));

// Renderizar cabecera HTML
require __DIR__ . '/views/header.php';
?>

<!-- Contenedor Principal de Pestañas -->
<main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
    <?php require __DIR__ . '/views/tab-tracking.php'; ?>
    <?php require __DIR__ . '/views/tab-agencies.php'; ?>
    <?php require __DIR__ . '/views/tab-quote.php'; ?>
    <?php require __DIR__ . '/views/tab-dni.php'; ?>
    <?php require __DIR__ . '/views/tab-pro.php'; ?>
</main>

<?php
// Modales y pie de página
require __DIR__ . '/views/modals/qr-scanner.php';
require __DIR__ . '/views/modals/settings.php';
require __DIR__ . '/views/footer.php';
