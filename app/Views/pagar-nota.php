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

$notaModel = new NotaFiscal();
$notasPendentes = $notaModel->listarPendentesPagamento($idOrgao);
$estatisticas = $notaModel->estatisticasPagamentos($idOrgao);

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
            <p class="text-muted mb-0">Gerencie o pagamento das notas fiscais pendentes</p>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-resumo">
                <div class="card-body text-center">
                    <i class="bi bi-receipt display-4 mb-3"></i>
                    <h5>Notas Pendentes</h5>
                    <div class="valor-destaque"><?php echo $estatisticas['pendentes'] ?? 0; ?></div>
                    <p class="small mb-0">Aguardando pagamento</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-resumo" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin display-4 mb-3"></i>
                    <h5>Total a Pagar</h5>
                    <div class="valor-destaque">R$ <?php echo number_format($estatisticas['total_liquido'] ?? 0, 2, ',', '.'); ?></div>
                    <p class="small mb-0">Valor líquido total</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-resumo" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar display-4 mb-3"></i>
                    <h5>IRRF Retido</h5>
                    <div class="valor-destaque">R$ <?php echo number_format($estatisticas['total_irrf_retido'] ?? 0, 2, ',', '.'); ?></div>
                    <p class="small mb-0">Total retido na fonte</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-resumo" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-3"></i>
                    <h5>Notas Pagas</h5>
                    <div class="valor-destaque"><?php echo $estatisticas['pagas'] ?? 0; ?></div>
                    <p class="small mb-0">Pagamentos realizados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Notas Pendentes -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Notas Pendentes de Pagamento</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" id="btnAtualizar">
                    <i class="bi bi-arrow-clockwise me-1"></i> Atualizar
                </button>
                <button class="btn btn-outline-success btn-sm" id="btnPagarMultiplas">
                    <i class="bi bi-wallet2 me-1"></i> Pagar Várias
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando notas...</p>
            </div>

            <?php if (empty($notasPendentes)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                    <h4>Todas as notas estão pagas!</h4>
                    <p class="text-muted">Não há notas pendentes de pagamento no momento.</p>
                    <a href="/sistema_irrf/public/gerar-nota" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Gerar Nova Nota
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive table-notas">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selecionarTodos" class="form-check-input">
                                </th>
                                <th>Nº Nota</th>
                                <th>Fornecedor</th>
                                <th>Data Emissão</th>
                                <th>Valor Bruto</th>
                                <th>IRRF Retido</th>
                                <th>Valor Líquido</th>
                                <th>Status</th>
                                <th width="180">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaNotas">
                            <?php foreach ($notasPendentes as $nota): ?>
                                <tr data-id="<?php echo $nota['id']; ?>">
                                    <td>
                                        <input type="checkbox" class="form-check-input selecionar-nota" value="<?php echo $nota['id']; ?>">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($nota['numero_nota']); ?></strong>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($nota['razao_social']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($nota['cnpj']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($nota['data_emissao'])); ?>
                                    </td>
                                    <td>
                                        <strong class="text-primary">R$ <?php echo number_format($nota['valor_bruto'], 2, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="text-danger">R$ <?php echo number_format($nota['valor_irrf_retido'], 2, ',', '.'); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-success">R$ <?php echo number_format($nota['valor_liquido'], 2, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-pendente badge-status">
                                            <i class="bi bi-clock me-1"></i> Pendente
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-pagar btn-sm" onclick="pagarNota(<?php echo $nota['id']; ?>)">
                                                <i class="bi bi-check-circle me-1"></i> Pagar
                                            </button>
                                            <button class="btn btn-detalhes btn-sm" onclick="verDetalhes(<?php echo $nota['id']; ?>)">
                                                <i class="bi bi-eye me-1"></i> Detalhes
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Totais Selecionados:</strong></td>
                                <td id="totalBrutoSelecionado">R$ 0,00</td>
                                <td id="totalIrrfSelecionado">R$ 0,00</td>
                                <td id="totalLiquidoSelecionado">R$ 0,00</td>
                                <td colspan="2">
                                    <button class="btn btn-success" id="btnPagarSelecionadas" disabled>
                                        <i class="bi bi-wallet2 me-1"></i> Pagar Selecionadas
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
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

<!--
// app/Views/pagar-nota.php
// ATUALIZAR APENAS A PARTE DO SCRIPT
-->

<script>
$(document).ready(function() {
    // ==================== FUNÇÃO SIMPLES PARA PAGAR UMA NOTA ====================
    window.pagarNota = function(idNota) {
        if (!confirm('Deseja realmente marcar esta nota como paga?')) {
            return;
        }
        
        // Mostrar loading
        var btn = $('tr[data-id="' + idNota + '"]').find('.btn-pagar');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processando...');
        
        $.ajax({
            url: '/sistema_irrf/public/api/pagamento.php?action=registrar',
            type: 'POST',
            data: {
                id_nota: idNota,
                data_pagamento: new Date().toISOString().split('T')[0],
                observacoes: 'Pagamento realizado via sistema'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Resposta do pagamento:', response);
                
                if (response.success) {
                    // Remover linha da tabela
                    $('tr[data-id="' + idNota + '"]').fadeOut(300, function() {
                        $(this).remove();
                        
                        // Recarregar a página para atualizar estatísticas
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    });
                    
                    // Mostrar mensagem de sucesso
                    mostrarAlerta('Nota paga com sucesso! ID: ' + response.id_pagamento, 'success');
                } else {
                    mostrarAlerta('Erro: ' + response.error, 'danger');
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Pagar');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erro AJAX:', textStatus, errorThrown);
                mostrarAlerta('Erro ao conectar com o servidor. Verifique o console.', 'danger');
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Pagar');
            }
        });
    };
    
    // ==================== FUNÇÃO PARA PAGAR MÚLTIPLAS NOTAS ====================
    $('#btnPagarSelecionadas').click(function() {
        var selecionadas = [];
        $('.selecionar-nota:checked').each(function() {
            selecionadas.push(parseInt($(this).val()));
        });
        
        if (selecionadas.length === 0) {
            mostrarAlerta('Selecione pelo menos uma nota para pagar.', 'warning');
            return;
        }
        
        if (!confirm('Deseja pagar ' + selecionadas.length + ' nota(s) selecionada(s)?')) {
            return;
        }
        
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processando...');
        
        $.ajax({
            url: '/sistema_irrf/public/api/pagamento.php?action=registrar_multiplo',
            type: 'POST',
            data: {
                ids_notas: JSON.stringify(selecionadas),
                data_pagamento: new Date().toISOString().split('T')[0],
                observacoes: 'Pagamento em lote via sistema'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Resposta do pagamento múltiplo:', response);
                
                if (response.success) {
                    // Remover linhas das notas pagas
                    selecionadas.forEach(function(id) {
                        $('tr[data-id="' + id + '"]').fadeOut(300);
                    });
                    
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                    
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
    
    // Botão Atualizar
    $('#btnAtualizar').click(function() {
        location.reload();
    });
    
    // Botão Pagar Múltiplas
    $('#btnPagarMultiplas').click(function() {
        $('#btnPagarSelecionadas').click();
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
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>