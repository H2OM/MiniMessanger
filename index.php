<?php
    include_once __DIR__ . '/Controllers/IndexController.php';
    spl_autoload('Controller/IndexController');
    use Controllers\IndexController;

    $uri = explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
    $controller = new IndexController();
    $path = $uri[1];

    for ($i = 2; $i < count($uri); $i++) {
        if (!empty($uri[$i])) {
            $path = '';
            break;
        }
    }

    $action = 'action' . ucfirst($path);

    if ($path === '' || !method_exists($controller, $action)) {
        $controller->actionIndex();
    } else {
        $controller->$action($_POST);
    }
