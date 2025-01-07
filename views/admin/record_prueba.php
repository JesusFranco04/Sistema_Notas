<?php 
session_start();
include('../../Crud/config.php'); // Ruta absoluta 

// Comprobación de errores en la consulta SQL
$sql = "
WITH mejores_estudiantes AS (
    SELECT 
        e.nombres AS Nombre,
        e.apellidos AS Apellido,
        sn.nombre AS Subnivel,
        n.nombre AS Nivel,
        c.id_curso AS Curso,
        cal.nota_final AS NotaFinal,
        ROW_NUMBER() OVER (
            PARTITION BY sn.id_subnivel, n.id_nivel, c.id_curso
            ORDER BY cal.nota_final DESC
        ) AS posicion
    FROM 
        estudiante e
    INNER JOIN calificacion cal ON e.id_estudiante = cal.id_estudiante
    INNER JOIN curso c ON cal.id_curso = c.id_curso
    INNER JOIN subnivel sn ON c.id_subnivel = sn.id_subnivel
    INNER JOIN nivel n ON c.id_nivel = n.id_nivel
    WHERE 
        cal.nota_final BETWEEN 9 AND 10
        AND e.estado = 'A'
        AND cal.estado_calificacion = 'A'
)
SELECT * 
FROM mejores_estudiantes
WHERE posicion <= 2;
";

$result = $conn->query($sql);

// Verificación de errores en la consulta SQL
if ($result === false) {
    echo "Error en la consulta: " . $conn->error;
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récord Académico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom right, #f9f9fb, #e6e6f2);
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .header {
            background: linear-gradient(to right, #B90F2C, #06a660);
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        p.subtitle {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 300;
        }
        .filter-bar {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .filter-bar input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
        }
        .filter-bar button {
            background: #06a660;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .table-wrapper {
            overflow-y: auto;
            max-height: 400px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #B90F2C;
            color: white;
        }
        table tbody tr:nth-child(even) {
            background-color: #f8f8fa;
        }
        table tbody tr:hover {
            background-color: #fbe8eb;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .gold {
            background-color: #FFD700;
        }
        .silver {
            background-color: #C0C0C0;
        }
        .bronze {
            background-color: #CD7F32;
        }
        .alert-error {
            margin-top: 20px;
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Récord Académico</h1>
                <p class="subtitle">¿Quieres conocer los mejores estudiantes de tu plantel de EBG y BTI?</p>
            </div>
            <div>
                <i class='bx bxs-trophy' style='font-size: 3rem; color: #FFD700;'></i>
            </div>
        </div>
        <div class="filter-bar">
            <input type="text" id="search" placeholder="Buscar por nombre o nivel" aria-label="Buscar por nombre o nivel">
            <button onclick="filterTable()">Buscar</button>
        </div>
        <div class="table-wrapper">
            <table id="recordTable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Subnivel</th>
                        <th>Nivel</th>
                        <th>Curso</th>
                        <th>Nota Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    if ($result->num_rows > 0): 
                        while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                echo htmlspecialchars($row['Nombre']);
                                if ($counter == 1) echo " <span class='badge gold'>🥇</span>";
                                elseif ($counter == 2) echo " <span class='badge silver'>🥈</span>";
                                elseif ($counter == 3) echo " <span class='badge bronze'>🥉</span>";
                                $counter++;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['Apellido']); ?></td>
                            <td><?php echo htmlspecialchars($row['Subnivel']); ?></td>
                            <td><?php echo htmlspecialchars($row['Nivel']); ?></td>
                            <td><?php echo htmlspecialchars($row['Curso']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['NotaFinal'], 2)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No se encontraron registros con el criterio buscado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Alerta de error debajo de la tabla -->
        <div id="alert" class="alert-error" style="display: none;">
            No se encontraron registros con el criterio buscado.
        </div>
    </div>
    <script>
function filterTable() {
    const input = document.getElementById('search').value.toLowerCase();
    const rows = document.querySelectorAll('#recordTable tbody tr');
    let matchFound = false;
    
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        const match = cells.some(cell => cell.textContent.toLowerCase().includes(input));
        row.style.display = match ? '' : 'none';
        if (match) matchFound = true;
    });
    
    document.getElementById('alert').style.display = matchFound ? 'none' : 'block';
}
    </script>
</body>
</html>


<?php
$conn->close();
?>