<!DOCTYPE html>
<html lang="es">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Temperatura vs Tiempo</title>
    <style>
        table {
            width: 40%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .graph {
            width: 100%;
            height: 400px;
            border: 1px solid black;
            position: relative;
        }
        .graph .line {
            stroke: blue;
            stroke-width: 2;
            fill: none;
        }
    </style>
</head>
<body>
    <form action="" method="POST">
        <label for="fecha_inicio">Fecha y Hora de Inicio:</label>
        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" required>
        <label for="fecha_fin">Fecha y Hora de Fin:</label>
        <input type="datetime-local" id="fecha_fin" name="fecha_fin" required>
        <input type="submit" value="Filtrar">
    </form>

    <h5 class="m-0 me-2">Filtrado</h5>
    <small class="text-muted">Datos Encontrados</small>

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

    // Obtener los valores del formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        // Convertir la fecha y hora de inicio y fin en dos partes
        $fecha_inicio_part = date('Y-m-d', strtotime($fecha_inicio));
        $hora_inicio_part = date('H:i:s', strtotime($fecha_inicio));
        $fecha_fin_part = date('Y-m-d', strtotime($fecha_fin));
        $hora_fin_part = date('H:i:s', strtotime($fecha_fin));

        // Preparar la consulta SQL con parámetros para evitar SQL injection
        $sql = "SELECT temperatura, fecha, hora 
                FROM temperaturas 
                WHERE (fecha > ? OR (fecha = ? AND hora >= ?)) 
                AND (fecha < ? OR (fecha = ? AND hora <= ?))
                ORDER BY fecha ASC, hora ASC";

        // Preparar la consulta
        $stmt = $conn->prepare($sql);

        // Vincular los parámetros
        $stmt->bind_param("ssssss", $fecha_inicio_part, $fecha_inicio_part, $hora_inicio_part, $fecha_fin_part, $fecha_fin_part, $hora_fin_part);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultados
        $result = $stmt->get_result();

        // Almacenar los datos en arrays
        $temperaturas = array();
        $fechas = array();

        if ($result->num_rows > 0) {
            echo "<h4>Resultados del Filtro</h4>";
            echo "<table>";
            echo "<tr><th>Temperatura (°C)</th><th>Fecha</th><th>Hora</th></tr>";
            
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["temperatura"] . "</td>";
                echo "<td>" . $row["fecha"] . "</td>";
                echo "<td>" . $row["hora"] . "</td>";
                echo "</tr>";

                $temperaturas[] = $row["temperatura"];
                $fechas[] = $row["fecha"] . " " . $row["hora"];
            }
            
            echo "</table>";
        } else {
            echo "<p>No se encontraron datos en el rango especificado.</p>";
        }

        // Cerrar la conexión y liberar recursos
        $stmt->close();
        $conn->close();
    }
    ?>
    <div class="graph">
        <svg width="80%" height="60%">
            <?php
            if (!empty($temperaturas) && !empty($fechas)) {
                $max_temp = max($temperaturas);
                $min_temp = min($temperaturas);
                $temp_range = $max_temp - $min_temp;
                $graph_width = 1000;
                $graph_height = 400;

                $num_points = count($temperaturas);
                $x_step = $graph_width / ($num_points - 1);

                $points = [];
                foreach ($temperaturas as $index => $temp) {
                    $x = $index * $x_step;
                    $y = $graph_height - (($temp - $min_temp) / $temp_range) * $graph_height;
                    $points[] = "$x,$y";
                }

                $points_str = implode(" ", $points);
                echo "<polyline points='$points_str' class='line' />";
            }
            ?>
        </svg>
    </div>
</body>
</html>
