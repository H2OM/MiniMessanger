<?php
    include_once __DIR__ . '/Controllers/IndexController.php';
    spl_autoload('Controller/IndexController');
    use Controllers\IndexController;
    $client = (string)$_SERVER['REMOTE_ADDR'];
    $clientAgent = (string)$_SERVER['HTTP_USER_AGENT'];
    $ips = json_decode(file_get_contents(__DIR__ . "/data/clients.json"), true);

    $colors = [
        'rgb(46 46 46)',
        'rgb(66 66 66)',
        'rgb(66 47 47)',
        'rgb(64 48 59)',
        'rgb(47 49 66)',

    ];

    if(!isset($ips[$client])) {
        $ips[$client]['color'] = $colors[count($ips)] ?? $colors[0];
//        $ips[$client]['color'] = "rgb(".random_int(46, 70)." ".random_int(46, 70)." ".random_int(46, 70).")";
    }

    if (file_put_contents(__DIR__ . "/data/clients.json", json_encode($ips, JSON_PRETTY_PRINT)) === false) {
        file_put_contents(
            filename: __DIR__ . "/data/logs.txt",
            data: "Ошибка записи нового ip в файл. " . json_last_error_msg() . PHP_EOL,
            flags: FILE_APPEND
        );
    }

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
        file_put_contents(
            filename: __DIR__ . "/data/connectionsLogs.txt",
            data: date('Y-m-d H:i:s') . "    " . $client . "    " . $clientAgent . PHP_EOL,
            flags: FILE_APPEND
        );
        $controller->actionIndex();
    } else {
        $controller->$action($_POST);
    }
