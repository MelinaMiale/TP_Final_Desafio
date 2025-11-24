<?php
require_once __DIR__ . '/../enums/Role.php';
class AdminModel {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getAdminStats() {
        $sqlTotalUsers = "SELECT COUNT(*) as total FROM USUARIO WHERE id_rol = '3'";
        $totalUsers = $this->connection->query($sqlTotalUsers)[0]['total'];

        $sqlTotalGames = "SELECT COUNT(*) as total FROM PARTIDA";
        $totalGames = $this->connection->query($sqlTotalGames)[0]['total'];

        $sqlTotalQuestions = "SELECT COUNT(*) as total FROM PREGUNTA";
        $totalQuestions = $this->connection->query($sqlTotalQuestions)[0]['total'];

        $sqlUserQuestions = "SELECT COUNT(*) as total 
                         FROM PREGUNTA p 
                         INNER JOIN USUARIO u ON p.id_autor = u.id 
                         WHERE u.id_rol = 3";
        $userQuestions = $this->connection->query($sqlUserQuestions)[0]['total'];

        return [
            'total_users' => $totalUsers,
            'total_games' => $totalGames,
            'total_questions' => $totalQuestions,
            'user_questions' => $userQuestions,
        ];
    }
    public function getNuevosJugadoresPorPeriodo($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $select = "HOUR(fechaDeRegistro) AS periodo";
                $where = "DATE(fechaDeRegistro) = CURDATE()";
                $group = "HOUR(fechaDeRegistro)";
                $order = "periodo ASC";
                break;
            case 'week':
                $select = "DATE(fechaDeRegistro) AS periodo";
                $where = "fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(fechaDeRegistro)";
                $order = "periodo ASC";
                break;
            case 'month':
                $select = "DATE(fechaDeRegistro) AS periodo, DAY(fechaDeRegistro) AS dia, MONTH(fechaDeRegistro) AS mes";
                $where = "fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group = "DATE(fechaDeRegistro)";
                $order = "periodo ASC";
                break;
            case 'year':
                $select = "DATE_FORMAT(fechaDeRegistro, '%Y-%m') AS periodo, YEAR(fechaDeRegistro) AS anio, MONTH(fechaDeRegistro) AS mes";
                $where = "fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                $group = "YEAR(fechaDeRegistro), MONTH(fechaDeRegistro)";
                $order = "periodo ASC";
                break;
            default:
                $select = "DATE(fechaDeRegistro) AS periodo";
                $where = "fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(fechaDeRegistro)";
                $order = "periodo ASC";
        }

        $sql = "SELECT $select, COUNT(*) AS cantidad
            FROM USUARIO
            WHERE id_rol = 3 AND $where
            GROUP BY $group
            ORDER BY $order";

        return $this->connection->query($sql);
    }

    public function getPartidasPorPeriodo($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $select = "HOUR(fecha) AS periodo";
                $where = "DATE(fecha) = CURDATE()";
                $group = "HOUR(fecha)";
                $order = "periodo ASC";
                break;
            case 'week':
                $select = "DATE(fecha) AS periodo";
                $where = "fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(fecha)";
                $order = "periodo ASC";
                break;
            case 'month':
                $select = "DATE(fecha) AS periodo, DAY(fecha) AS dia, MONTH(fecha) AS mes";
                $where = "fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group = "DATE(fecha)";
                $order = "periodo ASC";
                break;
            case 'year':
                $select = "DATE_FORMAT(fecha, '%Y-%m') AS periodo, YEAR(fecha) AS anio, MONTH(fecha) AS mes";
                $where = "fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                $group = "YEAR(fecha), MONTH(fecha)";
                $order = "periodo ASC";
                break;
            default:
                $select = "DATE(fecha) AS periodo";
                $where = "fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(fecha)";
                $order = "periodo ASC";
        }

        $sql = "SELECT $select, COUNT(*) AS cantidad
        FROM PARTIDA
        WHERE $where
        GROUP BY $group
        ORDER BY $order";

        return $this->connection->query($sql);
    }
    public function getNuevasPreguntasPorPeriodo($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $select = "HOUR(p.fecha_creacion) AS periodo";
                $where = "DATE(p.fecha_creacion) = CURDATE()";
                $group = "HOUR(p.fecha_creacion)";
                $order = "periodo ASC";
                break;
            case 'week':
                $select = "DATE(p.fecha_creacion) AS periodo";
                $where = "p.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(p.fecha_creacion)";
                $order = "periodo ASC";
                break;
            case 'month':
                $select = "DATE(p.fecha_creacion) AS periodo, DAY(p.fecha_creacion) AS dia, MONTH(p.fecha_creacion) AS mes";
                $where = "p.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                $group = "DATE(p.fecha_creacion)";
                $order = "periodo ASC";
                break;
            case 'year':
                $select = "DATE_FORMAT(p.fecha_creacion, '%Y-%m') AS periodo, YEAR(p.fecha_creacion) AS anio, MONTH(p.fecha_creacion) AS mes";
                $where = "p.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                $group = "YEAR(p.fecha_creacion), MONTH(p.fecha_creacion)";
                $order = "periodo ASC";
                break;
            default:
                $select = "DATE(p.fecha_creacion) AS periodo";
                $where = "p.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                $group = "DATE(p.fecha_creacion)";
                $order = "periodo ASC";
        }

        $sql = "SELECT $select, COUNT(*) AS cantidad
        FROM PREGUNTA p
        INNER JOIN USUARIO u ON p.id_autor = u.id
        WHERE u.id_rol = 3 AND $where
        GROUP BY $group
        ORDER BY $order";

        return $this->connection->query($sql);
    }

    public function getUsuariosConEstadisticas() {
        $sql = "SELECT 
                u.id,
                u.nombre_usuario,
                u.nombre_completo,
                COALESCE(COUNT(ru.id), 0) as total_respuestas,
                COALESCE(SUM(ru.fue_correcta), 0) as respuestas_correctas,
                CASE 
                    WHEN COUNT(ru.id) > 0 THEN ROUND((SUM(ru.fue_correcta) * 100.0) / COUNT(ru.id), 2)
                    ELSE 0
                END as porcentaje_correctas
            FROM USUARIO u
            LEFT JOIN respuesta_usuario ru ON u.id = ru.id_usuario
            WHERE u.id_rol = 3
            GROUP BY u.id, u.nombre_usuario, u.nombre_completo
            ORDER BY porcentaje_correctas DESC";

        return $this->connection->query($sql);
    }

    public function getUsuariosPorPais($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $where = "DATE(u.fechaDeRegistro) = CURDATE()";
                break;
            case 'week':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                break;
            default:
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $sql = "SELECT 
                COALESCE(p.nombre, 'Sin país') as pais,
                COUNT(u.id) as cantidad
            FROM USUARIO u
            LEFT JOIN CIUDAD c ON u.id_ciudad = c.id
            LEFT JOIN PAIS p ON c.id_pais = p.id
            WHERE u.id_rol = 3 AND $where
            GROUP BY p.nombre
            ORDER BY cantidad DESC";

        return $this->connection->query($sql);
    }

    public function getUsuariosPorSexo($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $where = "DATE(u.fechaDeRegistro) = CURDATE()";
                break;
            case 'week':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                break;
            default:
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $sql = "SELECT 
                COALESCE(s.nombre, 'No especificado') as sexo,
                COUNT(u.id) as cantidad
            FROM USUARIO u
            LEFT JOIN SEXO s ON u.id_sexo = s.id
            WHERE u.id_rol = 3 AND $where
            GROUP BY s.nombre
            ORDER BY cantidad DESC";

        return $this->connection->query($sql);
    }


    public function getUsuariosPorEdad($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $where = "DATE(u.fechaDeRegistro) = CURDATE()";
                break;
            case 'week':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                break;
            default:
                $where = "u.fechaDeRegistro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $sql = "SELECT 
            CASE 
                WHEN (YEAR(CURDATE()) - u.anio_nacimiento) < 18 THEN 'Menores -18'
                WHEN (YEAR(CURDATE()) - u.anio_nacimiento) BETWEEN 18 AND 30 THEN 'Adultos Jóvenes 18 - 30'
                WHEN (YEAR(CURDATE()) - u.anio_nacimiento) BETWEEN 31 AND 65 THEN 'Adultos Mayores 31 - 65'
                WHEN (YEAR(CURDATE()) - u.anio_nacimiento) > 65 THEN 'Jubilados +65'
                ELSE 'Sin Edad'
            END as rango_edad,
            COUNT(u.id) as cantidad
        FROM USUARIO u
        WHERE u.id_rol = 3 AND u.anio_nacimiento IS NOT NULL AND $where
        GROUP BY rango_edad
        ORDER BY 
            CASE rango_edad
                WHEN 'Menores' THEN 1
                WHEN 'Adultos Jóvenes' THEN 2
                WHEN 'Adultos Mayores' THEN 3
                WHEN 'Jubilados' THEN 4
                ELSE 5
            END";

        return $this->connection->query($sql);
    }
    public function getPrequntasPorDificultad($periodo = 'week') {
        switch ($periodo) {
            case 'day':
                $where = "DATE(fecha_creacion) = CURDATE()";
                break;
            case 'week':
                $where = "fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where = "fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $where = "fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
                break;
            case 'all':
                $where = "1=1";
                break;
            default:
                $where = "fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $sql = "SELECT 
                CASE 
                    WHEN dificultad_actual = 1 THEN 'Fácil'
                    WHEN dificultad_actual = 2 THEN 'Medio'
                    WHEN dificultad_actual = 3 THEN 'Difícil'
                    ELSE 'Sin clasificar'
                END as dificultad,
                COUNT(id) as cantidad
            FROM PREGUNTA
            WHERE $where
            GROUP BY dificultad_actual
            ORDER BY dificultad_actual ASC";

        return $this->connection->query($sql);
    }

}
