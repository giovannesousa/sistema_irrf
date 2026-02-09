<?php
// app/Views/errors/404.php
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - Sistema IRRF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 100px;
            font-weight: bold;
            color: #dc3545;
            margin: 0;
        }
        .error-message {
            font-size: 24px;
            color: #333;
            margin: 20px 0;
        }
        .back-link {
            display: inline-block;
            padding: 10px 30px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .back-link:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Página não encontrada</div>
        <p>A página que você está procurando não existe ou foi movida.</p>
        <a href="/sistema_irrf/public/" class="back-link">Voltar para o Dashboard</a>
    </div>
</body>
</html>