<?php

class CategoryController {
    private $model;
    private $renderer;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function addCategory() {}

    public function updateCategory() {}

    public function deleteCategory() {}
    public function manageCategories() {
        // Datos de prueba hardcodeados
        $data = [
            "totalCategories" => 3,
            "activeCount" => 2,
            "disabledCount" => 1,
            "hasCategories" => true,
            "categories" => [
                [
                    "id" => 1,
                    "nombre" => "Historia",
                    "color" => "#ff6347",
                ],
                [
                    "id" => 2,
                    "nombre" => "Ciencia",
                    "color" => "#1e90ff",
                ],
                [
                    "id" => 3,
                    "nombre" => "Deportes",
                    "color" => "#32cd32",
                ],
            ],
            "hasHistory" => true,
            "history" => [
                [
                    "fecha" => "2025-11-20 10:15:00",
                    "accion" => "Creación",
                    "detalle" => "Se creó la categoría 'Historia'",
                ],
                [
                    "fecha" => "2025-11-20 11:00:00",
                    "accion" => "Edición",
                    "detalle" => "Se cambió el color de 'Ciencia'",
                ],
                [
                    "fecha" => "2025-11-21 09:30:00",
                    "accion" => "Deshabilitación",
                    "detalle" => "Se deshabilitó la categoría 'Deportes'",
                ],
            ],
        ];

        $this->renderer->render("categories", $data);
    }

}