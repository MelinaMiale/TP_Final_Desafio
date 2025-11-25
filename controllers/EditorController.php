<?php

class EditorController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function displayEditorHome() {
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
            header("Location: ?controller=editor&method=displayEditorHome");
            exit;
        }

        if ($action === 'aprobar') {
            header("Location: ?controller=question&method=editQuestion");
            exit;
        }
    }

}