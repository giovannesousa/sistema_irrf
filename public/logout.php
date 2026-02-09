<?php
// public/logout.php

require_once __DIR__ . '/../app/Core/Session.php';

// Iniciar sessão
Session::start();

// Destruir sessão
Session::destroy();

// Redirecionar para login
header('Location: /sistema_irrf/public/login.php');
exit;
?>