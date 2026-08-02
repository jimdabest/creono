<?php
class Controller {
    // Load Model
    public function model(string $model): object {
        require_once '../app/Models/' . $model . '.php';
        return new $model();
    }

    // Load View
    public function view(string $view, array $data = []): void {
        if (file_exists('../app/Views/' . $view . '.php')) {
            require_once '../app/Views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }
}