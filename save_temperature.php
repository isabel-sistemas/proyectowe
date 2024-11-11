<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "u493221695_sensor1";
$password = "12345678ABCabc++";
$dbname = "u493221695_sensor_datas";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar la zona horaria a La Paz, Bolivia
date_default_timezone_set('America/La_Paz');

// Leer los datos de la solicitud POST
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (isset($data['temperatura'])) {
    $temperatura = $data['temperatura'];
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");

    // Preparar y ejecutar la consulta SQL
    $stmt = $conn->prepare("INSERT INTO temperaturas (temperatura, fecha, hora) VALUES (?, ?, ?)");
    $stmt->bind_param("dss", $temperatura, $fecha, $hora);

    if ($stmt->execute()) {
        echo "Datos guardados exitosamente";
    } else {
        echo "Error al guardar los datos: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Datos inválidos";
}

$conn->close();
?>
