<?php
class MustacheRenderer {
    private $mustache;

    public function __construct($viewsPath) {
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine([
            'loader' => new Mustache_Loader_FilesystemLoader($viewsPath),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($viewsPath . '/partial')
        ]);

    }

    public function render($template, $data = []) {
        header('Content-Type: text/html; charset=utf-8');
        echo $this->mustache->render($template, $data);
    }

}