<?php

namespace Controllers;

use DateTime;

class IndexController
{
    protected string $messagesPath = __DIR__ . "/../data/messages.json";
    protected string $errorsLogsPath = __DIR__ . "/../data/logs.txt";
    protected string $messageLogsPath = __DIR__ . "/../data/messageLogs.txt";
    protected string $clientIPs = __DIR__ . "/../data/clients.json";

    /** @var array|null $data */
    protected array|null $data;

    /**
     * Index
     *
     * @return void
     */
    #[Route(path: '/', name: 'index')]
    public function actionIndex(): void
    {
        $this->data['messages'] = json_decode(file_get_contents($this->messagesPath), true);
        $this->data['ips'] = json_decode(file_get_contents($this->clientIPs), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents(
                filename: $this->errorsLogsPath,
                data: date('Y-m-d H:i:s') . "    Ошибка кодирования, actionIndex JSON: " . json_last_error_msg() . PHP_EOL,
                flags: FILE_APPEND
            );
        }

        include __DIR__ . "/../view/index.php";
    }

    /**
     * Update
     *
     * @param $data
     * @return void
     */
    public function actionUpdate($data): void
    {
        if(empty($data) || empty($data['last_stamp'])) {
            header('HTTP/1.1 400 Bad Request');
            die;
        }

        $ips = json_decode(file_get_contents($this->clientIPs), true);
        $messages = json_decode(file_get_contents($this->messagesPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents(
                filename: $this->errorsLogsPath,
                data: date('Y-m-d H:i:s') . "    Ошибка кодирования, actionUpdate JSON: " . json_last_error_msg() . " timestamp: " . $data['last_stamp'] . PHP_EOL,
                flags: FILE_APPEND
            );
            header('HTTP/1.1 500 Internal Server Error');
            die;
        }
        $lastStamp = (float)$data['last_stamp'];
        $response = [];

        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if ($lastStamp >= $messages[$i]['timestamp']) {
                break;
            }
            $messages[$i]['client'] = $ips[$messages[$i]['client']]['color'] ?? 'rgb(46 46 46)';
            $response[] = $messages[$i];
        }

        exit(json_encode(array_reverse($response)));
    }

    /**
     * Presend
     *
     * @param $data
     * @return void
     */
    #[Route(path: '/presend', name: 'presend')]
    public function actionPresend($data): void
    {
        if (empty($data) || empty($data['message'])) {
            header('HTTP/1.1 400 Bad Request');
            die;
        }

        $message = $this->sanitize($data['message']);
        $currentDate = date('Y-d-m H:i:s');
        $clientIp = (string)$_SERVER['REMOTE_ADDR'];
        $clientAgent = (string)$_SERVER['HTTP_USER_AGENT'];
        $platform = (string)$_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? "";
        $clientAgentInfo = (string)$_SERVER['HTTP_SEC_CH_UA'] ?? "";

        file_put_contents(
            filename: $this->messageLogsPath,
            data: $currentDate . "  '".$clientIp."'     " . $message . "     '".$clientAgent."'  " . "  '".$clientAgentInfo."'  " . "  '".$platform."'  "  . PHP_EOL,
            flags: FILE_APPEND
        );
    }

    /**
     * Send
     *
     * @param $data
     * @return void
     */
    public function actionSend($data): void
    {
        if (empty($data) || empty($data['message'])) {
            header('HTTP/1.1 400 Bad Request');
            die;
        }

        $ips = json_decode(file_get_contents($this->clientIPs), true);

        if(isset($ips[$_SERVER['REMOTE_ADDR']]['block'])) {
            header('HTTP/1.1 403 Forbidden');
            die;
        }

        $message = $this->sanitize($data['message']);
        $clientIp = (string)$_SERVER['REMOTE_ADDR'];
        $microtime = microtime(true);
        $time = date('H:i');

        $newMessage = [
            "message" => $message,
            "timestamp" => $microtime,
            "date" => $time,
            "client" => $clientIp,
        ];

        try {
            $messages = json_decode(file_get_contents($this->messagesPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') .  "    Ошибка кодирования, JSON: " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                header('HTTP/1.1 500 Internal Server Error');
                die;
            }

            $messages[] = $newMessage;
            $jsonMessages = json_encode($messages, JSON_PRETTY_PRINT);

            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') . "    Ошибка кодирования JSON: " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                header('HTTP/1.1 500 Internal Server Error');
                die;
            }
            if (file_put_contents($this->messagesPath, $jsonMessages) === false) {
                file_put_contents(
                    filename: $this->errorsLogsPath,
                    data: date('Y-m-d H:i:s') . "    Ошибка записи в файл. " . json_last_error_msg() . " сообщение: " . $message . PHP_EOL,
                    flags: FILE_APPEND
                );
                header('HTTP/1.1 500 Internal Server Error');
                die;
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

            exit(json_encode(array_reverse($response)));
        } catch (\Throwable $th) {
            header('HTTP/1.1 500 Internal Server Error');
            die;
        }
    }

    /**
     * Error
     *
     * @param $data
     * @return void
     */
    public function actionError($data): void
    {
        if (empty($data) || !isset($data['error']) || !isset($data['message'])) {
            header('HTTP/1.1 400 Bad Request');
            die;
        }

        $error = $this->sanitize($data['error']);
        $message = $this->sanitize($data['message']);

        file_put_contents(
            filename: $this->errorsLogsPath,
            data: date('Y-m-d H:i:s') . "    /error - Ошибка во время записи сообщения. " . " сообщение: " . $message . " ошибка: " . $error . PHP_EOL,
            flags: FILE_APPEND
        );
    }

    private function sanitize(string $string): string
    {
        return htmlspecialchars(trim($string));
    }
}
