<?php

class EditorController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayEditorHome() {
        // datos para probar la interfaz
//        $data = [
//            "editorName" => "Melina",
//            "currentDate" => date("d/m/Y"),
//            "pendingReports" => 2,
//            "resolvedReports" => 5,
//            "avgResolutionTime" => 3,
//
//            "pendingReportsList" => [
//                [
//                    "id" => 1,
//                    "preguntaEnunciado" => "¿En qué año llegó Colón a América?",
//                    "usuarioNombre" => "Juan22",
//                    "comentario_usuario" => "El enunciado debería ser '¿En qué año se descubrió América?'",
//                    "fecha_reporte" => "10/11/2025",
//                    "estado_origen" => "Aprobada"
//                ],
//                [
//                    "id" => 2,
//                    "preguntaEnunciado" => "¿Cuál es la capital de Francia?",
//                    "usuarioNombre" => "Ana33",
//                    "comentario_usuario" => "La respuesta correcta debería ser París, no Lyon",
//                    "fecha_reporte" => "11/11/2025",
//                    "estado_origen" => "Aprobada"
//                ]
//            ],
//
//            "history" => [
//                [
//                    "preguntaEnunciado" => "¿Quién pintó La Última Cena?",
//                    "usuarioNombre" => "Carlos10",
//                    "comentario_usuario" => "El enunciado está mal redactado",
//                    "accion" => "rechazar",
//                    "comentario_editor" => "El enunciado original es correcto",
//                    "estado_origen" => "Aprobada",
//                    "estado_destino" => "Rechazada",
//                    "fecha_reporte" => "05/11/2025",
//                    "fecha_revision" => "07/11/2025"
//                ],
//                [
//                    "preguntaEnunciado" => "¿Cuál es el valor aproximado de π?",
//                    "usuarioNombre" => "Lucia99",
//                    "comentario_usuario" => "La opción correcta es 3.1416",
//                    "accion" => "aprobar",
//                    "comentario_editor" => "Se confirma la corrección",
//                    "estado_origen" => "Pendiente",
//                    "estado_destino" => "Aprobada",
//                    "fecha_reporte" => "02/11/2025",
//                    "fecha_revision" => "03/11/2025"
//                ]
//            ]
//        ];
        $metrics = $this->model->getEditorMetrics();
        $pending = $this->model->getPendingReports();
        $history = $this->model->getHistory();
        $data = array_merge($metrics, [
            "editorName" => $_SESSION["full_name"],
            "currentDate" => date("d/m/Y"),
            "pendingReportsList" => $pending,
            "history" => $history
        ]);

        $this->renderer->render("editorScreen", $data);
    }

    public function resolvePlayerReport() {
        $reportId = $_POST['reportId'];
        $_SESSION["reportId"] = $reportId;
        $action   = $_POST['action'];
        $editorComment = $_POST['editorComment'] ?? '';
        $editorId = $_SESSION['userId'];

        if ($action === 'rechazar') {
            $this->model->rejectReport($reportId, $editorComment, $editorId);
            // podria tener una vista nueva donde ver solamente los reportes
            header("Location: ?controller=editor&method=displayEditorHome");
            exit;
        }

        if ($action === 'aprobar') {
            header("Location: ?controller=question&method=editQuestion");
            exit;
        }
    }

}