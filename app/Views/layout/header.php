<?php
$usuario = $_SESSION['usuario'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Sistema IRRF'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-logo i {
            font-size: 1.5rem;
        }

        .sidebar-user {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-orgao {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
        }

        .sidebar .nav-link .badge {
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }

        .top-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
        }

        .content-wrapper {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            padding: 15px 20px;
        }

        .card-body {
            padding: 20px;
        }

        /* Stats Cards */
        .stat-card {
            text-align: center;
            padding: 20px;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }

            .sidebar.active {
                width: var(--sidebar-width);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block !important;
            }
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #333;
        }

        /* Footer */
        .footer {
            margin-left: var(--sidebar-width);
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 15px 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/sistema_irrf/public/index.php" class="sidebar-logo">
                <i class="bi bi-calculator-fill"></i>
                <span>Sistema IRRF</span>
            </a>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-name"><?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?></div>
            <div class="user-orgao"><?php echo htmlspecialchars($usuario['orgao_nome'] ?? 'Órgão'); ?></div>
        </div>

        <div class="sidebar-menu">
            <nav class="nav flex-column">
                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'dashboard') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/index.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'orgao') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/orgao.php">
                        <i class="bi bi-building"></i>
                        <span>Órgão</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'gerar-nota') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/gerar-nota">
                        <i class="bi bi-file-earmark-plus"></i>
                        <span>Calcular IRRF</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'pagar-nota') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/pagar-nota">
                        <i class="bi bi-cash-stack"></i>
                        <span>Registrar Pagamentos</span>
                        <?php if (isset($estatisticas) && $estatisticas['pendentes'] > 0): ?>
                            <span
                                class="badge bg-warning notification-badge"><?php echo $estatisticas['pendentes']; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'reinf') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/reinf">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>EFD-Reinf</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'r1000') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/enviar-r1000.php">
                        <i class="bi bi-building-fill-up"></i> 
                        <span>R-1000 - Cadastro</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'relatorios') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/relatorios.php">
                        <i class="bi bi-graph-up"></i>
                        <span>Relatórios</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a class="nav-link <?php echo ($pagina_atual == 'fornecedores') ? 'active' : ''; ?>"
                        href="/sistema_irrf/public/fornecedores.php">
                        <i class="bi bi-people"></i>
                        <span>Fornecedores</span>
                    </a>
                </div>

                <?php if (($usuario['nivel_acesso'] ?? '') === 'admin'): ?>
                    <div class="nav-item">
                        <a class="nav-link <?php echo ($pagina_atual == 'configuracoes') ? 'active' : ''; ?>"
                            href="/sistema_irrf/public/configuracoes.php">
                            <i class="bi bi-gear"></i>
                            <span>Configurações</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link <?php echo ($pagina_atual == 'orgaos') ? 'active' : ''; ?>" href="/sistema_irrf/public/geren-orgaos.php">
                            <i class="bi bi-building-gear"></i>
                            <span>Gerenciar Órgãos</span>
                        </a>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link <?php echo ($pagina_atual == 'usuarios') ? 'active' : ''; ?>"
                            href="/sistema_irrf/public/usuarios.php">
                            <i class="bi bi-person-badge"></i>
                            <span>Usuários</span>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="nav-item mt-4">
                    <a class="nav-link text-danger" href="/sistema_irrf/public/logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <button class="menu-toggle" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>

            <div class="page-title">
                <h1><?php echo $titulo ?? 'Dashboard'; ?></h1>
            </div>

            <div class="header-actions">
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger notification-badge">5</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">Notificações</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Nova nota para pagamento</a></li>
                        <li><a class="dropdown-item" href="#">Relatório mensal pronto</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-primary" href="#">Ver todas</a></li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                        data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo htmlspecialchars(explode(' ', $usuario['nome'] ?? 'Usuário')[0]); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header"><?php echo htmlspecialchars($usuario['nome'] ?? ''); ?></h6>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configurações</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/sistema_irrf/public/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Sair
                            </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Wrapper -->
        <div class="content-wrapper">