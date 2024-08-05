<?php
$messages = $this->data['messages'];
$clients = $this->data['ips'];
$lastTimeStamp = count($messages) > 0 ? $messages[count($messages) - 1]['timestamp'] : 0;
$months = [
    1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
    5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
    9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
];

function formatDate(float|int $timestamp, array $months): string
{
    $date = getdate((int)$timestamp);

    return $date['mday'] . ' ' . $months[$date['mon']];
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0 viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        * {
            border: 0;
            outline: 0;
            background-color: unset;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            color: #fff;
            font-family: "Roboto", -apple-system, BlinkMacSystemFont, "Apple Color Emoji", "Segoe UI", Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }

        html, body {
            overflow: hidden;
        }

        body {
            position: relative;
            background-color: rgb(33, 33, 33);
            padding: 0 10px;
            padding-top: 20px;
            height: calc(var(--vh, 1vh) * 100);

        }

        .content {
            overflow-y: auto;
            height: calc(100% - 80px);
        }

        .content__separator {
            font-size: 20px;
            margin-bottom: 10px;
            text-align: center;
            width: 500px;
        }

        .content::-webkit-scrollbar-button {
            display: none;
        }

        .content::-webkit-scrollbar {
            background-color: unset;
            width: 8px;
        }

        .content::-webkit-scrollbar-thumb {
            background-color: lightgray;
        }

        .message {
            font-size: 20px;
            font-weight: 300;
            position: relative;
            margin-bottom: 10px;
            max-width: 700px;
        }

        .message:before {
            content: attr(data-date);
            position: absolute;
            right: 24px;
            color: rgb(118, 118, 118);
            font-size: 14px;
            bottom: 6px;
        }

        form {
            position: absolute;
            left: 10px;
            bottom: 20px;
            display: flex;
            z-index: 50;
            gap: 10px;
            max-width: 500px;
            justify-content: space-between;
            align-items: flex-end;
            width: calc(100vw - 20px);
        }

        form .cloud {
            width: 100%;
        }

        .cloud {
            background-color: rgb(46 46 46);
            width: fit-content;
            border-radius: 25px;
            padding: 10px 28px;
        }

        .message.cloud {
            padding-right: 66px;
            padding-bottom: 14px;
            transform: scale(0);
            animation: manifestCloud 0.2s forwards;
        }
        @keyframes manifestCloud {from {transform: scale(0)} to {transform: scale(1)} }
        textarea {
            resize: none;
            width: 100%;
            font-size: 18px;
            border-radius: 0;
            height: fit-content;
            transition: 0.2s all;
        }

        button {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 49px;
            background-color: rgb(46 46 46);
            border-radius: 100%;
            flex-shrink: 0;
            transition: 0.2s all;
            width: 0;
            transform: scale(0);
        }

        .error {
            position: absolute;
            width: 70vw;
            height: 60px;
            font-size: 16px;
            color: white;
            top: 60px;
            left: calc(50% - 35vw);
            background: linear-gradient(180deg, rgba(191, 0, 0, 0) 0%, rgba(179, 0, 0, 1) 60%);
            opacity: 0;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: manifest 0.2s forwards;
            transition: 0.2s all;
            text-align: center;
        }

        @keyframes manifest {
            from {
                opacity: 0
            }
            to {
                opacity: 1
            }
        }

        button.open {
            width: 49px;
            transform: scale(1);
            transition: transform 0.2s 0.2s, width 0.2s;
        }

        button.close {
            width: 0;
            transform: scale(0);
            transition: transform 0.2s, width 0.2s 0.2s;
        }

        @media (max-width: 500px) {
            .content__separator {
                font-size: 14px;
                width: 100%;
            }

            .message {
                font-size: 4vw;
            }

            .message.cloud {
                padding-right: 60px;
            }

            .message:before {
                font-size: 12px;
                right: 16px;
            }

            button {
                height: 45px;
            }

            button.open {
                width: 45px;
            }

            textarea {
                font-size: 16px;
            }
        }
    </style>
    <title>⠧</title>
</head>
<body>
<div class="content">
    <?php
    setlocale(LC_TIME, 'ru_RU.UTF-8');

    $lastDate = 0;

    foreach ($messages as $message):
        ?>
        <?php
        if (date('Y-m-d', (int)$lastDate) !== date('Y-m-d', (int)$message['timestamp'])) {
            $lastDate = $message['timestamp'];
            echo '<div class="content__separator">' . formatDate($lastDate, $months) . '</div>';
        }
        ?>
        <div class="message cloud"
             style="background-color: <?= $clients[$message['client']]['color'] ?? 'rgb(46 46 46)' ?>"
             data-date="<?= $message['date'] ?>">
            <?= $message['message'] ?>
        </div>
    <?php endforeach; ?>
</div>
<form>
    <div class="cloud"><textarea name="name" rows="1" placeholder="Сообщение..."></textarea></div>
    <button type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#fff"
             style="transform: rotate(44deg) translateY(8%) translateX(-8%)" class="bi bi-send" viewBox="0 0 16 16">
            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
        </svg>
    </button>
</form>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const MONTHS = [
            'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
            'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
        ];
        const TIME_TO_UPDATE = 4000;
        const CONTENT = document.querySelector('.content');
        const TEXTAREA = document.querySelector('textarea');
        const BUTTON = document.querySelector('button');
        const FORM = document.querySelector('form');
        let lastMessageTimeStamp = <?= floor($lastTimeStamp) ?> + <?= ($lastTimeStamp - floor($lastTimeStamp)) * 1e6 ?> / 1e6;
        let preSendData = '';
        let preSendTimeOut;
        let updateInterval;
        let isSending = false;
        CONTENT.style.height = `calc(100% - ${TEXTAREA.scrollHeight}px - 60px)`;

        const adjustViewportHeight = () => {
            document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
        };

        const appendMessage = ({message, date, timestamp, client}) => {
            let prevDate = new Date(lastMessageTimeStamp * 1000);
            let newDate = new Date(timestamp * 1000);

            if (prevDate.getDate() !== newDate.getDate() || prevDate.getMonth() !== newDate.getMonth()) {
                const dayTab = document.createElement('div');
                dayTab.classList.add('content__separator');
                dayTab.textContent = `${newDate.getDate()} ${MONTHS[newDate.getMonth()]}`;

                CONTENT.append(dayTab);
            }

            const messageAppend = document.createElement('div');
            messageAppend.style.backgroundColor = client;
            messageAppend.classList.add('message', 'cloud');
            messageAppend.setAttribute('data-date', date);
            messageAppend.textContent = message;

            CONTENT.append(messageAppend);

            CONTENT.scrollTo({top: CONTENT.scrollHeight, left: 0, behavior: 'smooth'});
        };

        const messagesUpdater = () => {
            const formData = new FormData();

            formData.set('last_stamp', lastMessageTimeStamp);

            fetch('/update', {method: "POST", body: formData})
                .then((response) => {
                    if (!response.ok) {
                        throw new Error();
                    }

                    return response.json();
                }).then(data => {

                if (data.length === 0) return;

                data.forEach(each => {
                    if (Number(each.timestamp) > lastMessageTimeStamp) {
                        appendMessage({message: each.message, date: each.date, timestamp: each.timestamp, client: each.client});
                    }
                });

                lastMessageTimeStamp = Number(data[data.length - 1].timestamp);
            }).catch((e) => {
                isSending = false;
                const elem = document.createElement('div');
                elem.classList.add('error');
                elem.innerHTML = 'Ошибка загрузки новых сообщений';
                document.body.append(elem);

                setTimeout(() => {
                    elem.style.transform = 'scale(0)';
                    setTimeout(() => elem.remove(), 200);
                }, 4000);
                const formData = new FormData();
                formData.set('error', e);
                formData.set('message', "Ошибка загрузки новых сообщений");

                fetch('/error', {method: "POST", body: formData});
            });
        }

        const preSend = (message) => {
            const formData = new FormData();
            formData.set('message', message);

            fetch('/presend', {method: "POST", body: formData});
        };

        const send = (e) => {
            e.preventDefault();

            if (isSending || TEXTAREA.value.length < 1) return;

            const formData = new FormData();
            const message = TEXTAREA.value;

            formData.set('message', message);

            isSending = true;

            fetch('/send', {method: "POST", body: formData})
                .then((response) => {
                    if (!response.ok) {
                        throw new Error();
                    }

                    return response.json();
                }).then(data => {
                isSending = false;

                FORM.reset();
                BUTTON.classList.add('close');
                BUTTON.classList.remove('open');

                clearInterval(updateInterval);
                updateInterval = setInterval(() => messagesUpdater(), TIME_TO_UPDATE);

                data.forEach(each => {
                    if (Number(each.timestamp) > lastMessageTimeStamp) {
                        appendMessage({message: each.message, date: each.date, timestamp: each.timestamp, client: each.client});
                    }
                });

                lastMessageTimeStamp = Number(data[data.length - 1].timestamp);
            }).catch((e) => {
                isSending = false;
                const elem = document.createElement('div');
                elem.classList.add('error');
                elem.innerHTML = 'Что то не так';
                document.body.append(elem);

                setTimeout(() => {
                    elem.style.transform = 'scale(0)';
                    setTimeout(() => elem.remove(), 200);
                }, 4000);
                const formData = new FormData();
                formData.set('error', e);
                formData.set('message', message);

                fetch('/error', {method: "POST", body: formData});
            });
        }

        adjustViewportHeight();

        CONTENT.scrollTop = CONTENT.scrollHeight;

        window.addEventListener('resize', adjustViewportHeight);

        TEXTAREA.addEventListener('input', ({target}) => {
            target.style.height = 'auto'; // Сбрасываем высоту, чтобы корректно измерить новый размер
            target.style.height = target.scrollHeight + 'px'; // Устанавливаем высоту на основе scrollHeight
            CONTENT.style.height = `calc(100% - ${TEXTAREA.scrollHeight}px - 60px)`;

            if (target.value.length > 0) {
                BUTTON.classList.remove('close');
                BUTTON.classList.add('open');
                if (preSendData !== '') {
                    if (target.value.length >= preSendData.length) {
                        clearTimeout(preSendTimeOut);
                        let message = target.value;
                        preSendTimeOut = setTimeout(() => {
                            preSend(message);
                        }, 500);
                    }
                } else if (preSendData === '') {
                    preSend(target.value);
                }
                preSendData = target.value;
            } else {
                BUTTON.classList.add('close');
                BUTTON.classList.remove('open');
            }
        });

        TEXTAREA.addEventListener('keypress', (e) => {
            if (isSending) {
                e.preventDefault();
            }
            if (e.code === 'Enter') {
                e.preventDefault();
                BUTTON.click();
            }
        });

        FORM.addEventListener('submit', send);

        updateInterval = setInterval(() => messagesUpdater(), TIME_TO_UPDATE);
    });
</script>
</body>
</html>
