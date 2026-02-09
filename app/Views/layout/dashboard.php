<?php
// app/Views/home.php

$titulo = "Dashboard";
$pagina_atual = "dashboard";

require_once __DIR__ . '/layout/header.php';
?>

<!-- Conteúdo do Dashboard -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Visão Geral</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Card 1 -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon text-primary">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="stat-number">42</div>
                            <div class="stat-label">Notas Geradas</div>
                        </div>
                    </div>
                    
                    <!-- Card 2 -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon text-success">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="stat-number">R$ 85.420,50</div>
                            <div class="stat-label">Total Retido</div>
                        </div>
                    </div>
                    
                    <!-- Card 3 -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon text-warning">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="stat-number">12</div>
                            <div class="stat-label">Pendentes de Pagamento</div>
                        </div>
                    </div>
                    
                    <!-- Card 4 -->
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon text-info">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-number">24</div>
                            <div class="stat-label">Fornecedores Ativos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimas Notas -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Últimas Notas</h5>
                <a href="/sistema_irrf/public/gerar-nota.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i>Nova Nota
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nº Nota</th>
                                <th>Fornecedor</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>IRRF</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NF-00123</td>
                                <td>TELESTO SISTEMAS LTDA</td>
                                <td>10/02/2026</td>
                                <td>R$ 5.000,00</td>
                                <td>R$ 240,00</td>
                                <td><span class="badge bg-success">Paga</span></td>
                            </tr>
                            <tr>
                                <td>NF-00122</td>
                                <td>CONSULTORIA ABC LTDA</td>
                                <td>09/02/2026</td>
                                <td>R$ 3.200,00</td>
                                <td>R$ 153,60</td>
                                <td><span class="badge bg-warning">Pendente</span></td>
                            </tr>
                            <tr>
                                <td>NF-00121</td>
                                <td>SERVIÇOS XYZ ME</td>
                                <td>08/02/2026</td>
                                <td>R$ 1.800,00</td>
                                <td>R$ 86,40</td>
                                <td><span class="badge bg-success">Paga</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Ações Rápidas -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/sistema_irrf/public/gerar-nota.php" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus me-2"></i>Gerar Nova Nota
                    </a>
                    <a href="/sistema_irrf/public/pagar-nota.php" class="btn btn-success">
                        <i class="bi bi-cash-stack me-2"></i>Registrar Pagamentos Pendentes
                    </a>
                    <a href="/sistema_irrf/public/relatorios.php" class="btn btn-info">
                        <i class="bi bi-graph-up me-2"></i>Gerar Relatório
                    </a>
                    <a href="/sistema_irrf/public/fornecedores.php" class="btn btn-warning">
                        <i class="bi bi-people me-2"></i>Cadastrar Fornecedor
                    </a>
                </div>
                
                <hr>
                
                <h6><i class="bi bi-calendar-check me-2"></i>Próximos Vencimentos</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        NF-00122
                        <span class="badge bg-warning">Hoje</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        NF-00125
                        <span class="badge bg-info">Amanhã</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        NF-00130
                        <span class="badge bg-light text-dark">Em 3 dias</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';