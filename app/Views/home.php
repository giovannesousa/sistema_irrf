<?php
// app/Views/home.php - Dashboard

if (!isset($titulo)) $titulo = "Dashboard";
if (!isset($pagina_atual)) $pagina_atual = "dashboard";
if (!isset($usuario)) $usuario = $_SESSION['usuario'] ?? null;

require_once __DIR__ . '/layout/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h5>
            </div>
            <div class="card-body">
                <h4>Bem-vindo, <?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?>!</h4>
                <p class="lead">Sistema de Cálculo de IRRF para Órgãos Públicos</p>
                
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-file-earmark-text display-4 text-primary"></i>
                                <h5 class="mt-3">Calcular IRRF</h5>
                                <p>Calcule e gere novas notas fiscais</p>
                                <a href="/sistema_irrf/public/gerar-nota" class="btn btn-primary">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-cash-stack display-4 text-success"></i>
                                <h5 class="mt-3">Registrar Pagamento</h5>
                                <p>Gerencie pagamentos de notas</p>
                                <a href="#" class="btn btn-success">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-graph-up display-4 text-info"></i>
                                <h5 class="mt-3">Relatórios</h5>
                                <p>Relatórios e estatísticas</p>
                                <a href="#" class="btn btn-info">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-people display-4 text-warning"></i>
                                <h5 class="mt-3">Fornecedores</h5>
                                <p>Cadastro de fornecedores</p>
                                <a href="#" class="btn btn-warning">
                                    Acessar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';