<?php

class CategoryController {
    private $model;
    private $renderer;
    private $questionModel;

    public function __construct($model, $renderer) {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->questionModel = new QuestionModel($this->model->getConnection());
    }

    public function addCategory() {
        $categoryData = [
            'name' => $_POST['name'],
            'color' => $_POST['color']
        ];
        $this->model->addCategory($categoryData);
        header("Location: ?controller=category&method=manageCategories");
        exit;
    }

    public function updateCategory() {
        $categoryData = [
            'name' => $_POST['name'],
            'color' => $_POST['color'],
            'id' => $_POST['categoryId'],
            'editorComment' => $_POST['editorComment'],
            'action' => 'EDITAR'
        ];
        $this->model->updateCategory($categoryData);
        $this->model->logEditorActivity($categoryData);
        header("Location: ?controller=category&method=manageCategories");
        exit;
    }

    public function disableCategory() {
        $id = $_POST['categoryId'];
        $editorComment = $_POST['editorComment'] ?? '';

        $relatedQuestions = $this->questionModel->getQuestionsByCategoryId($id);

        if (empty($relatedQuestions)) {
            $this->model->disableCategory($id, $editorComment);
            header("Location: ?controller=category&method=manageCategories");
            exit;
        } else {
            $currentCategory = $this->model->getCategoryById($id);

            $categories = $this->model->getAllCategories();
            $categoriesFiltered = array_values(array_filter($categories, function($cat) use ($id) {
                return $cat['id'] != $id;
            }));
            $preview = array_slice($relatedQuestions, 0, 5);
            $remainingCount = max(0, count($relatedQuestions) - count($preview));

            $this->renderer->render("disableCategory", [
                'categoryId' => $id,
                'currentCategory' => $currentCategory,
                'relatedQuestionsPreview' => $preview,
                "remainingCount" => $remainingCount,
                "relatedQuestionsCount" => count($relatedQuestions),
                'categories' => $categoriesFiltered
            ]);
        }
    }

    public function reassignAndDisableCategory() {
        $oldCategoryId = $_POST['oldCategoryId'];
        $newCategoryId = $_POST['newCategoryId'];
        $editorComment = $_POST['editorComment'] ?? '';

        $this->questionModel->reassignQuestions($oldCategoryId, $newCategoryId, $editorComment);
        $this->model->disableCategory($oldCategoryId, $editorComment);
        header("Location: ?controller=category&method=manageCategories");
        exit;
    }


    public function manageCategories() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $searchCategory = $_GET['searchCategory'] ?? null;
        $categories = $this->model->getCategoriesPaginated($limit, $offset, $searchCategory);
        $totalCategories = $this->model->getTotalCategoriesCount($searchCategory);
        $totalPages = max(1, (int)ceil($totalCategories / $limit));

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'selected' => ($i === $page),
                'searchCategory' => $searchCategory
            ];
        }

        $stats = $this->model->getCategoryStats();

        $history = $this->model->getCategoryAuditHistory($limit = 10);
        $hasHistory = !empty($history);

        $this->renderer->render("categories", [
            'categories' => $categories ?? [],
            'hasCategories' => !empty($categories),
            'totalCategories' => $stats['total'],
            'activeCount' => $stats['active'],
            'disabledCount' => $stats['disabled'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pages' => $pages,
            'history' => $history,
            'hasHistory' => $hasHistory,
            'searchCategory'  => $searchCategory
        ]);
    }

    public function editCategory() {
        $categoryId = $_POST['categoryId'];
        $categoryToEdit = $this->model->getCategoryById($categoryId);

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $categories = $this->model->getCategoriesPaginated($limit, $offset);
        $totalCategories = $this->model->getTotalCategoriesCount();
        $totalPages = max(1, (int)ceil($totalCategories / $limit));

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'selected' => ($i === $page)
            ];
        }

        $stats = $this->model->getCategoryStats();
        $history = $this->model->getCategoryAuditHistory($limit = 10);
        $hasHistory = !empty($history);

        $this->renderer->render("categories", [
            'categories' => $categories ?? [],
            'hasCategories' => !empty($categories),
            'totalCategories' => $stats['total'],
            'activeCount' => $stats['active'],
            'disabledCount' => $stats['disabled'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pages' => $pages,
            'history' => $history,
            'hasHistory' => $hasHistory,
            'categoryToEdit' => $categoryToEdit,
            'showNewCategoryForm' => false
        ]);
    }

    public function newCategory() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $categories = $this->model->getCategoriesPaginated($limit, $offset);
        $totalCategories = $this->model->getTotalCategoriesCount();
        $totalPages = max(1, (int)ceil($totalCategories / $limit));

        $pages = [];
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'selected' => ($i === $page)
            ];
        }

        $stats = $this->model->getCategoryStats();

        $history = $this->model->getCategoryAuditHistory($limit = 10);
        $hasHistory = !empty($history);

        $this->renderer->render("categories", [
            'categories' => $categories ?? [],
            'hasCategories' => !empty($categories),
            'totalCategories' => $stats['total'],
            'activeCount' => $stats['active'],
            'disabledCount' => $stats['disabled'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pages' => $pages,
            'history' => $history,
            'hasHistory' => $hasHistory,
            "showNewCategoryForm" => true
        ]);
    }

}