<?php
// app/Views/pagar-nota.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/NotaFiscal.php';
require_once __DIR__ . '/../Core/Session.php';

Session::start();

if (!Session::isLoggedIn()) {
    header('Location: /sistema_irrf/public/login.php');
    exit;
}

$usuario = Session::getUser();
$titulo = "Registrar pagamentos";
$pagina_atual = "pagar-nota";

$idOrgao = Session::getIdOrgao() ?? $usuario['id_orgao'] ?? null;

if (!$idOrgao) {
    die("Erro: ID do órgão não encontrado na sessão. Faça login novamente.");
}

require_once __DIR__ . '/layout/header.php';
?>

<style>
    /* Estilos específicos para a página de pagamento */
    .card-resumo {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    
    .card-resumo .card-body {
        padding: 20px;
    }
    
    .valor-destaque {
        font-size: 2rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .badge-status {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 20px;
    }
    
    .badge-pendente {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .badge-pago {
        background-color: #d4edda;
        color: #155724;
    }
    
    .btn-pagar {
        background: linear-gradient(90deg, #28a745, #20c997);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s;
    }
    
    .btn-pagar:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .btn-detalhes {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.3s;
    }
    
    .btn-detalhes:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .table-notas {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    }
    
    .table-notas th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 15px;
        font-weight: 600;
        color: #495057;
    }
    
    .table-notas td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
    }
    
    .table-notas tr:hover {
        background-color: #f8f9fa;
    }
    
    .modal-content {
        border-radius: 15px;
        border: none;
    }
    
    .modal-header {
        background: linear-gradient(90deg, var(--primary-color), #0a58ca);
        color: white;
        border-radius: 15px 15px 0 0;
    }
    
    .modal-footer {
        border-top: 1px solid #eee;
        padding: 20px;
    }
    
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }
    
    .spinner-border {
        width: 2rem;
        height: 2rem;
    }
</style>

<div class="main-container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                <i class="bi bi-cash-stack me-2"></i> Registrar pagamentos
            </h1>
            <p class="text-muted mb-0">Gerencie o pagamento das notas fiscais por competência</p>
        </div>
        
        <!-- Seletor de Competência -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-2 d-flex align-items-center gap-2">
                <label for="filtroCompetencia" class="fw-bold text-secondary mb-0">Competência:</label>
                <input type="month" id="filtroCompetencia" class="form-control form-control-sm border-primary" style="width: 160px;">
                <button onclick="carregarDadosCompetencia()" class="btn btn-primary btn-sm">
                    <i class="bi bi-filter me-1"></i> Filtrar
                </button>
            </div>
        </div>
    </div>

    <!-- 1. Notas A PAGAR (Pendentes) -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-warning"><i class="bi bi-hourglass-split me-2"></i> A Pagar (Pendentes)</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success btn-sm" id="btnPagarMultiplas">
                    <i class="bi bi-wallet2 me-1"></i> Pagar Várias
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-notas">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50"><input type="checkbox" id="selecionarTodos" class="form-check-input"></th>
                            <th>Nº Nota</th>
                            <th>Fornecedor</th>
                            <th>Emissão</th>
                            <th>Valor Bruto</th>
                            <th>IRRF</th>
                            <th>Líquido</th>
                            <th width="150">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaPendentes">
                        <tr><td colspan="8" class="text-center py-4 text-muted">Carregando...</td></tr>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="4" class="text-end"><strong>Totais Selecionados:</strong></td>
                            <td id="totalBrutoSelecionado">R$ 0,00</td>
                            <td id="totalIrrfSelecionado">R$ 0,00</td>
                            <td id="totalLiquidoSelecionado">R$ 0,00</td>
                            <td>
                                <button class="btn btn-sm btn-success w-100" id="btnPagarSelecionadas" disabled>
                                    <i class="bi bi-check2-all"></i> Pagar
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Notas PAGAS (Realizados na Competência) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Pagamentos Realizados (Nesta Competência)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive table-notas">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nº Nota</th>
                            <th>Fornecedor</th>
                            <th>Data Pagto</th>
                            <th>Valor Bruto</th>
                            <th>IRRF Retido</th>
                            <th>Valor Pago</th>
                            <th width="100">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaPagas">
                        <tr><td colspan="7" class="text-center py-4 text-muted">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes da Nota -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i> Detalhes do Cálculo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalhesConteudo">
                <!-- Conteúdo carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnImprimirDetalhes">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Pagamento -->
<div class="modal fade" id="modalConfirmarPagamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i> Confirmar Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Atenção:</strong> Ao confirmar o pagamento, a nota será marcada como "PAGA" e não poderá ser alterada.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Data do Pagamento</label>
                    <input type="date" class="form-control" id="dataPagamento" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Observações (opcional)</label>
                    <textarea class="form-control" id="observacoesPagamento" rows="3" placeholder="Observações sobre o pagamento..."></textarea>
                </div>
                
                <div id="resumoPagamento">
                    <!-- Resumo será carregado aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarPagamento">
                    <i class="bi bi-check-circle me-1"></i> Confirmar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Data Pagamento -->
<div class="modal fade" id="modalEditarPagamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Editar Data do Pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editNotaId">
                <div class="alert alert-warning small">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Alterar a data pode mudar a competência do pagamento.
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nova Data do Pagamento</label>
                    <input type="date" class="form-control" id="editDataPagamento">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEdicao">
                    <i class="bi bi-save me-1"></i> Salvar Alterações
                </button>
            </div>
        </div>
    </div>
</div>

<!--
// app/Views/pagar-nota.php
// ATUALIZAR APENAS A PARTE DO SCRIPT
-->

<script>
$(document).ready(function() {
    let notaIdParaPagar = null;
    let notasIdsParaPagar = [];

    // ==================== FUNÇÃO SIMPLES PARA PAGAR UMA NOTA ====================
    window.pagarNota = function(idNota) {
        notaIdParaPagar = idNota;
        notasIdsParaPagar = [];
        
        // Resetar campos do modal
        $('#dataPagamento').val(new Date().toISOString().split('T')[0]);
        $('#observacoesPagamento').val('');
        
        // Texto padrão do modal
        $('#modalConfirmarPagamento .modal-title').html('<i class="bi bi-check-circle me-2"></i> Confirmar Pagamento');
        $('#modalConfirmarPagamento .alert-info').html('<i class="bi bi-info-circle me-2"></i> <strong>Atenção:</strong> Ao confirmar o pagamento, a nota será marcada como "PAGA" e não poderá ser alterada.');
        
        // Abrir modal
        var modal = new bootstrap.Modal(document.getElementById('modalConfirmarPagamento'));
        modal.show();
    };

    // ==================== CONFIRMAR PAGAMENTO NO MODAL (UNIFICADO) ====================
    $('#btnConfirmarPagamento').click(function() {
        if (!notaIdParaPagar && notasIdsParaPagar.length === 0) return;

        const dataPagamento = $('#dataPagamento').val();
        const observacoes = $('#observacoesPagamento').val();

        if (!dataPagamento) {
            alert('Por favor, informe a data do pagamento.');
            return;
        }

        // Fechar modal
        const modalEl = document.getElementById('modalConfirmarPagamento');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        if (notaIdParaPagar) {
            // Mostrar loading
            var btn = $('tr[data-id="' + notaIdParaPagar + '"]').find('.btn-pagar');
            var originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processando...');
            
            $.ajax({
                url: '/sistema_irrf/public/api/pagamento.php?action=registrar',
                type: 'POST',
                data: {
                    id_nota: notaIdParaPagar,
                    data_pagamento: dataPagamento,
                    observacoes: observacoes || 'Pagamento realizado via sistema'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta do pagamento:', response);
                    
                    if (response.success) {
                        // Remover linha da tabela
                        $('tr[data-id="' + notaIdParaPagar + '"]').fadeOut(300, function() {
                            $(this).remove();
                            
                            // Recarregar a página para atualizar estatísticas
                            carregarDadosCompetencia();
                        });
                        
                        // Mostrar mensagem de sucesso
                        mostrarAlerta('Nota paga com sucesso! ID: ' + response.id_pagamento, 'success');
                    } else {
                        mostrarAlerta('Erro: ' + response.error, 'danger');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro AJAX:', textStatus, errorThrown);
                    mostrarAlerta('Erro ao conectar com o servidor. Verifique o console.', 'danger');
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        } else if (notasIdsParaPagar.length > 0) {
            var btn = $('#btnPagarSelecionadas');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processando...');
            
            $.ajax({
                url: '/sistema_irrf/public/api/pagamento.php?action=registrar_multiplo',
                type: 'POST',
                data: {
                    ids_notas: JSON.stringify(notasIdsParaPagar),
                    data_pagamento: dataPagamento,
                    observacoes: observacoes
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Resposta do pagamento múltiplo:', response);
                    
                    if (response.success) {
                        // Remover linhas das notas pagas
                        notasIdsParaPagar.forEach(function(id) {
                            $('tr[data-id="' + id + '"]').fadeOut(300);
                        });
                        
                        carregarDadosCompetencia();
                        
                        mostrarAlerta(response.message + ' (' + response.total_pagas + ' notas pagas)', 'success');
                        
                        // Mostrar erros se houver
                        if (response.erros && response.erros.length > 0) {
                            console.log('Erros durante o processamento:', response.erros);
                        }
                    } else {
                        mostrarAlerta('Erro: ' + response.error, 'danger');
                    }
                    
                    btn.prop('disabled', false).html('<i class="bi bi-wallet2 me-1"></i> Pagar Selecionadas');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Erro AJAX:', textStatus, errorThrown);
                    mostrarAlerta('Erro ao conectar com o servidor.', 'danger');
                    btn.prop('disabled', false).html('<i class="bi bi-wallet2 me-1"></i> Pagar Selecionadas');
                }
            });
        }
    });
    
    // ==================== FUNÇÃO PARA PAGAR MÚLTIPLAS NOTAS (AGORA ABRE MODAL) ====================
    $('#btnPagarSelecionadas').click(function() {
        var selecionadas = [];
        $('.selecionar-nota:checked').each(function() {
            selecionadas.push(parseInt($(this).val()));
        });
        
        if (selecionadas.length === 0) {
            mostrarAlerta('Selecione pelo menos uma nota para pagar.', 'warning');
            return;
        }
        
        // Configura variáveis para pagamento múltiplo
        notaIdParaPagar = null;
        notasIdsParaPagar = selecionadas;
        
        // Resetar campos do modal
        $('#dataPagamento').val(new Date().toISOString().split('T')[0]);
        $('#observacoesPagamento').val('Pagamento em lote via sistema');
        
        // Texto específico para múltiplo
        $('#modalConfirmarPagamento .modal-title').html('<i class="bi bi-wallet2 me-2"></i> Pagar ' + selecionadas.length + ' Notas');
        $('#modalConfirmarPagamento .alert-info').html('<i class="bi bi-info-circle me-2"></i> <strong>Atenção:</strong> A data selecionada abaixo será aplicada a <strong>todos os ' + selecionadas.length + ' registros</strong> selecionados.');
        
        // Abrir modal
        var modal = new bootstrap.Modal(document.getElementById('modalConfirmarPagamento'));
        modal.show();
    });
    
    // ==================== FUNÇÕES AUXILIARES ====================
    
    function mostrarAlerta(mensagem, tipo) {
        var alertClass = 'alert-' + tipo;
        var icon = '';
        
        switch(tipo) {
            case 'success': icon = '<i class="bi bi-check-circle-fill me-2"></i>'; break;
            case 'danger': icon = '<i class="bi bi-exclamation-circle-fill me-2"></i>'; break;
            case 'warning': icon = '<i class="bi bi-exclamation-triangle-fill me-2"></i>'; break;
            default: icon = '<i class="bi bi-info-circle-fill me-2"></i>';
        }
        
        // Remover alertas anteriores
        $('.alert-dismissible').alert('close');
        
        // Criar novo alerta
        var alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${icon}${mensagem}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        
        $('body').append(alertHtml);
        
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }
    
    // ==================== LÓGICA DE SELEÇÃO ====================
    var notasSelecionadas = [];
    
    $('#selecionarTodos').change(function() {
        var isChecked = $(this).prop('checked');
        $('.selecionar-nota').prop('checked', isChecked);
        atualizarSelecao();
    });
    
    $('.selecionar-nota').change(function() {
        atualizarSelecao();
    });
    
    function atualizarSelecao() {
        notasSelecionadas = [];
        $('.selecionar-nota:checked').each(function() {
            notasSelecionadas.push(parseInt($(this).val()));
        });
        
        $('#btnPagarSelecionadas').prop('disabled', notasSelecionadas.length === 0);
        atualizarTotaisSelecionados();
    }
    
    function atualizarTotaisSelecionados() {
        var totalBruto = 0;
        var totalIrrf = 0;
        var totalLiquido = 0;
        
        $('.selecionar-nota:checked').each(function() {
            var linha = $(this).closest('tr');
            var valorBruto = parseFloat(linha.find('td:nth-child(5)').text().replace('R$ ', '').replace(/\./g, '').replace(',', '.'));
            var valorIrrf = parseFloat(linha.find('td:nth-child(6)').text().replace('R$ ', '').replace(/\./g, '').replace(',', '.'));
            var valorLiquido = parseFloat(linha.find('td:nth-child(7)').text().replace('R$ ', '').replace(/\./g, '').replace(',', '.'));
            
            totalBruto += valorBruto;
            totalIrrf += valorIrrf;
            totalLiquido += valorLiquido;
        });
        
        $('#totalBrutoSelecionado').text('R$ ' + totalBruto.toFixed(2).replace('.', ','));
        $('#totalIrrfSelecionado').text('R$ ' + totalIrrf.toFixed(2).replace('.', ','));
        $('#totalLiquidoSelecionado').text('R$ ' + totalLiquido.toFixed(2).replace('.', ','));
    }
    
    // ==================== CARREGAMENTO DE DADOS ====================
    window.carregarDadosCompetencia = function() {
        const periodo = $('#filtroCompetencia').val();
        
        // Feedback de carregamento
        $('#listaPendentes, #listaPagas').html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>');

        $.ajax({
            url: '/sistema_irrf/public/api/pagamento.php?action=listar_competencia',
            type: 'GET',
            data: { periodo: periodo },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Atualiza o input de data se veio vazio (primeira carga)
                    if (!periodo) {
                        $('#filtroCompetencia').val(res.periodo_selecionado);
                    }
                    
                    renderPendentes(res.pendentes);
                    renderPagas(res.pagas, res.is_fechado);
                } else {
                    mostrarAlerta('Erro ao carregar dados: ' + res.error, 'danger');
                }
            },
            error: function() {
                mostrarAlerta('Erro de conexão ao carregar dados.', 'danger');
            }
        });
    };

    // Carrega dados iniciais (após a definição da função)
    carregarDadosCompetencia();

    function renderPendentes(lista) {
        let html = '';
        if (lista.length === 0) {
            html = '<tr><td colspan="8" class="text-center py-4 text-muted">Nenhuma nota pendente para esta competência (ou anteriores).</td></tr>';
        } else {
            lista.forEach(nota => {
                html += `
                    <tr data-id="${nota.id}">
                        <td><input type="checkbox" class="form-check-input selecionar-nota" value="${nota.id}"></td>
                        <td><strong>${nota.numero_nota}</strong></td>
                        <td>
                            <div>${nota.razao_social}</div>
                            <small class="text-muted">${formatCnpj(nota.cnpj)}</small>
                        </td>
                        <td>${formatDate(nota.data_emissao)}</td>
                        <td>R$ ${formatMoney(nota.valor_bruto)}</td>
                        <td class="text-danger">R$ ${formatMoney(nota.valor_irrf_retido)}</td>
                        <td class="text-success fw-bold">R$ ${formatMoney(nota.valor_liquido)}</td>
                        <td>
                            <button class="btn btn-pagar btn-sm me-1" onclick="pagarNota(${nota.id})" title="Pagar">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="verDetalhes(${nota.id})" title="Detalhes">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#listaPendentes').html(html);
        // Reatribuir eventos aos novos checkboxes
        $('.selecionar-nota').change(atualizarSelecao);
        $('#selecionarTodos').prop('checked', false);
        atualizarSelecao();
    }

    function renderPagas(lista, isFechado) {
        let html = '';
        if (lista.length === 0) {
            html = '<tr><td colspan="7" class="text-center py-4 text-muted">Nenhum pagamento realizado nesta competência.</td></tr>';
        } else {
            lista.forEach(nota => {
                let btnEditar = '';
                if (isFechado) {
                    btnEditar = `<button class="btn btn-outline-secondary btn-sm me-1" disabled title="Competência Fechada (Reinf)"><i class="bi bi-lock-fill"></i></button>`;
                } else {
                    btnEditar = `<button class="btn btn-outline-primary btn-sm me-1" onclick="abrirModalEditarData(${nota.id}, '${nota.data_pagamento}')" title="Editar Data"><i class="bi bi-pencil"></i></button>`;
                }

                html += `
                    <tr>
                        <td><strong>${nota.numero_nota}</strong></td>
                        <td>
                            <div>${nota.razao_social}</div>
                            <small class="text-muted">${formatCnpj(nota.cnpj)}</small>
                        </td>
                        <td>${formatDate(nota.data_pagamento)}</td>
                        <td>R$ ${formatMoney(nota.valor_bruto)}</td>
                        <td class="text-danger">R$ ${formatMoney(nota.valor_irrf_retido)}</td>
                        <td class="text-success fw-bold">R$ ${formatMoney(nota.valor_liquido)}</td>
                        <td>
                            ${btnEditar}
                            <button class="btn btn-outline-secondary btn-sm" onclick="verDetalhes(${nota.id})" title="Detalhes">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#listaPagas').html(html);
    }
    
    // Botão Pagar Múltiplas
    $('#btnPagarMultiplas').click(function() {
        $('#btnPagarSelecionadas').click();
    });

    // ==================== EDITAR DATA PAGAMENTO ====================
    window.abrirModalEditarData = function(id, dataAtual) {
        $('#editNotaId').val(id);
        $('#editDataPagamento').val(dataAtual);
        var modal = new bootstrap.Modal(document.getElementById('modalEditarPagamento'));
        modal.show();
    };

    $('#btnSalvarEdicao').click(function() {
        var id = $('#editNotaId').val();
        var novaData = $('#editDataPagamento').val();
        var btn = $(this);
        
        if(!novaData) { 
            alert('Por favor, informe a nova data.'); 
            return; 
        }

        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Salvando...');

        $.ajax({
            url: '/sistema_irrf/public/api/pagamento.php?action=editar_data',
            type: 'POST',
            data: { id_nota: id, nova_data: novaData },
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalEditarPagamento')).hide();
                    mostrarAlerta(res.message, 'success');
                    carregarDadosCompetencia(); // Recarrega a lista
                } else {
                    alert('Erro: ' + res.error);
                }
                btn.prop('disabled', false).html(originalHtml);
            },
            error: function() {
                alert('Erro de conexão.');
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});

// ==================== FUNÇÃO PARA VER DETALHES ====================
function verDetalhes(idNota) {
    console.log('Abrindo detalhes da nota:', idNota);
    
    $.ajax({
        url: '/sistema_irrf/public/api/nota.php?action=buscar_nota&id=' + idNota,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var nota = response.nota;
                
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-building me-2"></i> Órgão</h6>
                            <p><strong>${nota.orgao_nome || 'Não informado'}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-person-badge me-2"></i> Fornecedor</h6>
                            <p><strong>${nota.razao_social}</strong><br>
                            CNPJ: ${nota.cnpj || 'Não informado'}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="bi bi-receipt me-2"></i> Dados da Nota</h6>
                            <p><strong>Número:</strong> ${nota.numero_nota}<br>
                            <strong>Data Emissão:</strong> ${nota.data_emissao_formatada || 'Não informada'}<br>
                            <strong>Natureza:</strong> ${nota.natureza_descricao || 'Não informada'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="bi bi-cash-stack me-2"></i> Valores</h6>
                            <p><strong>Valor Bruto:</strong> ${nota.valor_bruto_formatado}<br>
                            <strong>Alíquota IRRF:</strong> ${nota.aliquota_aplicada}%<br>
                            <strong>IRRF Retido:</strong> ${nota.valor_irrf_retido_formatado}<br>
                            <strong>Valor Líquido:</strong> ${nota.valor_liquido_formatado}</p>
                        </div>
                    </div>
                    
                    ${nota.descricao_servico ? `<hr><div><h6>Descrição do Serviço</h6><p>${nota.descricao_servico}</p></div>` : ''}
                `;
                
                $('#detalhesConteudo').html(html);
                $('#modalDetalhes').modal('show');
            } else {
                mostrarAlerta('Erro ao carregar detalhes: ' + response.error, 'danger');
            }
        },
        error: function() {
            mostrarAlerta('Erro ao conectar com o servidor.', 'danger');
        }
    });
}

function formatMoney(value) {
    return parseFloat(value).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}

function formatCnpj(v) {
    if(!v) return '';
    v = v.replace(/\D/g, "");
    return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
}
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>