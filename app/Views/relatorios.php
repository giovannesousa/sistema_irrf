<?php
// app/Views/relatorios.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/NotaFiscal.php';
require_once __DIR__ . '/../Core/Session.php';

Session::start();

if (!Session::isLoggedIn()) {
    header('Location: /sistema_irrf/public/login.php');
    exit;
}

$usuarioLogado = Session::getUser();
$titulo = "Relatório de Notas Fiscais";
$pagina_atual = "relatorios";
$idOrgao = Session::getIdOrgao() ?? $usuarioLogado['id_orgao'] ?? null;

// Filtro de Situação (Ativas/Inativas)
$situacao = $_GET['situacao'] ?? 'ativas';
$statusDb = ($situacao === 'inativas') ? 0 : (($situacao === 'todas') ? -1 : 1);

// Instanciar modelo e buscar dados (Limite aumentado para relatórios)
$notaModel = new NotaFiscal();
$listaNotas = $notaModel->listarPorOrgao($idOrgao, 500, $statusDb);

require_once __DIR__ . '/layout/header.php';
?>

<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
    }
    
    .card-header-custom {
        background: linear-gradient(90deg, #17a2b8, #0dcaf0);
        color: white;
        padding: 1rem 1.5rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(23, 162, 184, 0.05);
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .action-btn:hover {
        transform: scale(1.1);
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .row-inativa {
        background-color: #f8f9fa;
    }
</style>

<div class="main-container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i>Relatório de Notas
            </h1>
            <p class="text-muted mb-0">Consulte, imprima e gerencie as notas fiscais emitidas</p>
        </div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Imprimir Listagem
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 border-0 shadow-sm d-print-none">
        <div class="card-body">
            <h6 class="card-title text-muted mb-3"><i class="bi bi-funnel me-1"></i> Filtros de Pesquisa</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="buscaTexto" placeholder="Nº Nota, Fornecedor ou CNPJ...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Situação Registro</label>
                    <select class="form-select" id="filtroSituacao" onchange="alterarSituacao(this.value)">
                        <option value="ativas" <?php echo $situacao === 'ativas' ? 'selected' : ''; ?>>Ativas</option>
                        <option value="inativas" <?php echo $situacao === 'inativas' ? 'selected' : ''; ?>>Inativas</option>
                        <option value="todas" <?php echo $situacao === 'todas' ? 'selected' : ''; ?>>Todas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select" id="filtroStatus">
                        <option value="">Todos</option>
                        <option value="pago">Paga</option>
                        <option value="pendente">Pendente</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Data Inicial</label>
                    <input type="date" class="form-control" id="dataInicio">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Data Final</label>
                    <input type="date" class="form-control" id="dataFim">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltros()">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Notas -->
    <div class="card border-0 shadow-sm">
        <div class="card-header card-header-custom rounded-top">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Notas Registradas</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelaNotas">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nº Nota</th>
                            <th>Fornecedor</th>
                            <th>Emissão</th>
                            <th>Valor Bruto</th>
                            <th>IRRF</th>
                            <th>Líquido</th>
                            <th>Status</th>
                            <th class="text-end pe-4 d-print-none">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaNotas)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    Nenhuma cálculo de IRRF encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaNotas as $nota): ?>
                                <?php $isInativa = isset($nota['nota_ativa']) && $nota['nota_ativa'] == 0; ?>
                                <tr class="nota-row <?php echo $isInativa ? 'row-inativa text-muted' : ''; ?>" 
                                    data-texto="<?php echo strtolower($nota['numero_nota'] . ' ' . $nota['razao_social'] . ' ' . ($nota['cnpj'] ?? '')); ?>"
                                    data-status="<?php echo $nota['status_pagamento']; ?>"
                                    data-data="<?php echo $nota['data_emissao']; ?>"
                                    id="row-<?php echo $nota['id']; ?>">
                                    
                                    <td class="ps-4 fw-bold text-primary">
                                        <?php echo htmlspecialchars($nota['numero_nota']); ?> 
                                        <?php if ($isInativa): ?>
                                            <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">INATIVA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($nota['razao_social']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($nota['cnpj'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($nota['data_emissao'])); ?>
                                    </td>
                                    <td>
                                        R$ <?php echo number_format($nota['valor_bruto'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-danger">
                                        R$ <?php echo number_format($nota['valor_irrf_retido'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="text-success fw-bold">
                                        R$ <?php echo number_format($nota['valor_liquido'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php if ($nota['status_pagamento'] === 'pago'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 status-badge">
                                                <i class="bi bi-check-circle-fill me-1"></i> Paga
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 status-badge">
                                                <i class="bi bi-clock-fill me-1"></i> Pendente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4 d-print-none">
                                        <button class="btn btn-outline-dark action-btn me-1" onclick="imprimirNota(<?php echo $nota['id']; ?>)" title="Imprimir Nota">
                                            <i class="bi bi-printer-fill" style="font-size: 0.9rem;"></i>
                                        </button>
                                        
                                        <?php if ($nota['status_pagamento'] !== 'pago' && !$isInativa): ?>
                                            <a href="/sistema_irrf/public/gerar-nota?id=<?php echo $nota['id']; ?>" class="btn btn-outline-primary action-btn me-1" title="Editar Nota">
                                                <i class="bi bi-pencil-fill" style="font-size: 0.9rem;"></i>
                                            </a>
                                            <button class="btn btn-outline-danger action-btn" onclick="inativarNota(<?php echo $nota['id']; ?>, '<?php echo $nota['numero_nota']; ?>')" title="Inativar/Excluir">
                                                <i class="bi bi-trash-fill" style="font-size: 0.9rem;"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary action-btn" disabled title="Notas pagas não podem ser excluídas">
                                                <i class="bi bi-trash" style="font-size: 0.9rem;"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <small class="text-muted">Total de registros: <span id="totalRegistros"><?php echo count($listaNotas); ?></span></small>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos de filtro
        const buscaTexto = document.getElementById('buscaTexto');
        const filtroStatus = document.getElementById('filtroStatus');
        const dataInicio = document.getElementById('dataInicio');
        const dataFim = document.getElementById('dataFim');
        
        // Função de filtragem
        function filtrarTabela() {
            const texto = buscaTexto.value.toLowerCase();
            const status = filtroStatus.value;
            const inicio = dataInicio.value;
            const fim = dataFim.value;
            
            const linhas = document.querySelectorAll('.nota-row');
            let visiveis = 0;
            
            linhas.forEach(linha => {
                const rowTexto = linha.dataset.texto;
                const rowStatus = linha.dataset.status;
                const rowData = linha.dataset.data; // YYYY-MM-DD
                
                const matchTexto = rowTexto.includes(texto);
                const matchStatus = status === '' || rowStatus === status;
                
                let matchData = true;
                if (inicio && rowData < inicio) matchData = false;
                if (fim && rowData > fim) matchData = false;
                
                if (matchTexto && matchStatus && matchData) {
                    linha.style.display = '';
                    visiveis++;
                } else {
                    linha.style.display = 'none';
                }
            });
            
            document.getElementById('totalRegistros').textContent = visiveis;
        }
        
        // Event Listeners
        buscaTexto.addEventListener('keyup', filtrarTabela);
        filtroStatus.addEventListener('change', filtrarTabela);
        dataInicio.addEventListener('change', filtrarTabela);
        dataFim.addEventListener('change', filtrarTabela);
    });

    function limparFiltros() {
        document.getElementById('buscaTexto').value = '';
        document.getElementById('filtroStatus').value = '';
        document.getElementById('dataInicio').value = '';
        document.getElementById('dataFim').value = '';
        
        // Disparar evento para atualizar
        const event = new Event('change');
        document.getElementById('filtroStatus').dispatchEvent(event);
    }

    function alterarSituacao(valor) {
        // Recarrega a página com o novo filtro de situação
        const url = new URL(window.location.href);
        url.searchParams.set('situacao', valor);
        window.location.href = url.toString();
    }

    function imprimirNota(id) {
        // Abre a página de impressão em uma nova janela/aba
        const url = `/sistema_irrf/public/print-nota.php?id=${id}`;
        window.open(url, '_blank', 'width=900,height=800,scrollbars=yes');
    }

    function inativarNota(id, numero) {
        if (confirm(`ATENÇÃO: Deseja realmente inativar o cálculo nº ${numero}?\n\nEsta ação removerá a nota das listagens e relatórios.`)) {
            
            // Feedback visual
            const btn = document.querySelector(`#row-${id} .btn-outline-danger`);
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            // Chamada AJAX
            $.ajax({
                url: '/sistema_irrf/public/api/nota.php?action=inativar_nota',
                type: 'POST',
                data: { id_nota: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Remove a linha da tabela com animação
                        $(`#row-${id}`).fadeOut(500, function() {
                            $(this).remove();
                            // Atualiza contador
                            let total = parseInt(document.getElementById('totalRegistros').textContent);
                            document.getElementById('totalRegistros').textContent = total - 1;
                        });
                        alert('Nota inativada com sucesso!');
                    } else {
                        alert('Erro ao inativar nota: ' + response.error);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                },
                error: function() {
                    alert('Erro de conexão com o servidor.');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
        }
    }
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>