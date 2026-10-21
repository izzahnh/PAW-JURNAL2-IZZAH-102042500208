
<?php

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function valid_id($raw): ?int
{
    $id = filter_var(
        $raw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    return $id === false ? null : $id;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_valid(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function header_html($title)
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 30px;
                background: #f5f5f5;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                background: white;
            }

            th, td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }

            th {
                background: #e5e7eb;
            }

            .btn {
                display: inline-block;
                padding: 9px 12px;
                margin-top: 10px;
                text-decoration: none;
                background: #ddd;
                color: black;
                border: none;
                cursor: pointer;
            }

            .primary {
                background: #2563eb;
                color: white;
            }
        </style>
    </head>
    <body>
    <h1><?= e($title) ?></h1>
    <?php
}

function footer_html()
{
    ?>
    </body>
    </html>
    <?php
}
