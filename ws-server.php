<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

class Chat implements MessageComponentInterface {
    protected string $errorsLogsPath = __DIR__ . "/data/logs.txt";
    protected string $messagesPath = __DIR__ . "/data/messages.json";
    protected string $clientIPs = __DIR__ . "/data/clients.json";

    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn) {
        $conn->ip = explode(':', str_replace('tcp://', '', $conn->remoteAddress))[0];

        $this->clients->attach($conn);
        echo $conn->remoteAddress . " Новое подключение ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if(!$data || !isset($data['last_stamp'], $data['message'])) {
            $from->send(json_encode([
                "from" => "self",
                "type" => "error",
                "payload" => [
                    "code" => 400,
                    "message" => "Отсутствуют параметры"
                ]
            ]));
            return;
        }

        $ips = json_decode(file_get_contents($this->clientIPs), true);

        if(!isset($ips[$from->ip]['auth'])) {
            $from->send(json_encode([
                "from" => "self",
                "type" => "error",
                "payload" => [
                    "code" => 403,
                    "message" => "Не авторизирован"
                ]
            ]));
            return;
        }
        $message = $data['message'];
        $microtime = microtime(true);
        $time = date('H:i');

        $newMessage = [
            "message" => $message,
            "timestamp" => $microtime,
            "date" => $time,
            "client" => $from->ip,
        ];

        try {
            $messages = json_decode(file_get_contents($this->messagesPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') .  "    Ошибка кодирования, JSON: " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                $from->send(json_encode([
                    "from" => "self",
                    "type" => "error",
                    "payload" => [
                        "code" => 500,
                        "message" => "Ошибка сервера! (101)"
                    ]
                ]));
                return;
            }

            $messages[] = $newMessage;
            $jsonMessages = json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') . "    Ошибка кодирования JSON: " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                $from->send(json_encode([
                    "from" => "self",
                    "type" => "error",
                    "payload" => [
                        "code" => 500,
                        "message" => "Ошибка сервера! (102)"
                    ]
                ]));
                return;
            }
            if (file_put_contents($this->messagesPath, $jsonMessages) === false) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') . "    Ошибка записи в файл. " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                $from->send(json_encode([
                    "from" => "self",
                    "type" => "error",
                    "payload" => [
                        "code" => 500,
                        "message" => "Ошибка сервера! (103)"
                    ]
                ]));
                return;
            }

            $timeStampPoint = $microtime - 10;

            $response = [];

            for ($i = count($messages) - 1; $i >= 0; $i--) {
                if ($timeStampPoint > $messages[$i]['timestamp']) {
                    break;
                }
                $messages[$i]['client'] = $ips[$messages[$i]['client']]['color'] ?? 'rgb(46 46 46)';
                $response[] = $messages[$i];
            }

            $from->send(json_encode([
                "from" => "self",
                "type" => "success",
                "payload" => [
                    "code" => 200,
                    "message" => array_reverse($response)
                ]
            ]));

            $newMessage['client'] = $ips[$from->ip]['color'] ?? 'rgb(46 46 46)';

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send(json_encode([
                        "from" => "other",
                        "type" => "success",
                        "payload" => [
                            "code" => 200,
                            "message" => [$newMessage]
                        ]
                    ]));
                }
            }

            return;
        } catch (\Throwable $th) {
            $from->send(json_encode([
                "from" => "self",
                "type" => "error",
                "payload" => [
                    "code" => 500,
                    "message" => "Ошибка сервера! (104)"
                ]
            ]));

            return;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Отключился ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Ошибка: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sanitize(string $string): string {
        return htmlspecialchars(trim($string));
    }
}
$loop = Loop::get();

$socket = new SocketServer('0.0.0.0:8080', [], $loop);

$server = new IoServer(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    $socket,
    $loop
);

$loop->run();
