<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0 viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <style>
        /* cyrillic */
        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 100;
            font-display: swap;
            src: url(/KFOkCnqEu92Fr1MmgVxMIzIFKw.woff2) format('woff2');
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
        }
        /* latin */
        @font-face {
            font-family: 'Roboto';
            font-style: normal;
            font-weight: 100;
            font-display: swap;
            src: url(/KFOkCnqEu92Fr1MmgVxIIzI.woff2) format('woff2');
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
    </style>
    <style>
        * {
            border: 0;
            outline: 0;
            background-color: unset;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            color: #fff;
            font-weight: 100;
            font-family: "Roboto", sans-serif;
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
        .banner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 10px;
            width: 700px;
            background-color: rgb(24 24 24);
            box-shadow: 0 0 11px 0 #0000001f;
            border: 1px solid #2e2e2e;
            font-size: 20px;
            font-weight: 300;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 35px;
            transition: 0.4s all;
        }
        .banner p {
            margin: 25px 0;
            text-align: center;
        }
        .blackout {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 500;
            width: 100vw;
            height: 100vh;
            background-color: #0000008f;
            transition: 0.4s all;
        }
        @media (max-width: 750px) {
            .banner {
                width: 90vw;
            }
        }
        @media (max-width: 580px) {
            .banner {
                padding: 25px 18px;
            }
            .banner p:last-child {
                font-size: 16px;
            }
            .banner p {
                margin: 18px 0;
            }
        }
    </style>
    <title>⠧</title>
</head>
<body>
    <div class="blackout"></div>
    <div class="banner">
        <p>А секретный код?</p>
        <p>Для Валерии: зайди в наш чат и перейди по ссылке от туда</p>
    </div>
</body>
</html>
