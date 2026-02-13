<?php
// app/Views/reinf/dashboard.php

require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Core/Session.php';

Session::start();
if (!Session::isLoggedIn()) {
    header('Location: /sistema_irrf/public/login.php');
    exit;
}

$titulo = "EFD-Reinf - Gerenciamento";
$pagina_atual = "reinf";

require_once __DIR__ . '/../layout/header.php';
?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                <i class="bi bi-cloud-arrow-up me-2"></i>EFD-Reinf
            </h1>
            <p class="text-muted mb-0">Gerenciamento de eventos periódicos (R-4000)</p>
        </div>
        <div class="d-flex gap-2">
            <input type="month" id="filtroPeriodo" class="form-control" value="<?php echo date('Y-m'); ?>">
            <button class="btn btn-primary" onclick="carregarDados()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Navegação de Abas -->
    <ul class="nav nav-tabs mb-4" id="reinfTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pendencias-tab" data-bs-toggle="tab" data-bs-target="#pendencias" type="button">
                <i class="bi bi-exclamation-circle me-2"></i>Pendências de Envio
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="historico-tab" data-bs-toggle="tab" data-bs-target="#historico" type="button">
                <i class="bi bi-clock-history me-2"></i>Histórico de Lotes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="eventos-tab" data-bs-toggle="tab" data-bs-target="#eventos" type="button">
                <i class="bi bi-list-ul me-2"></i>Histórico de Eventos
            </button>
        </li>
    </ul>

    <div class="tab-content" id="reinfTabsContent">
        
        <!-- ABA 1: PENDÊNCIAS (Gerar R-4020) -->
        <div class="tab-pane fade show active" id="pendencias" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            Selecione os fornecedores abaixo para gerar e enviar os eventos R-4020 (Pagamentos/Créditos a Beneficiário Pessoa Jurídica).
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 40px;"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                    <th>Fornecedor</th>
                                    <th>CNPJ</th>
                                    <th class="text-center">Qtd. Pagamentos</th>
                                    <th class="text-end">Valor Bruto</th>
                                    <th class="text-end">Valor IR</th>
                                </tr>
                            </thead>
                            <tbody id="listaPendencias">
                                <tr><td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span> Carregando...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-outline-secondary" onclick="validarLote()">
                            <i class="bi bi-check-circle me-2"></i>Pré-Validar
                        </button>
                        <button class="btn btn-success" onclick="enviarLote()">
                            <i class="bi bi-send me-2"></i>Enviar Eventos Selecionados
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA 2: HISTÓRICO (Lotes Enviados) -->
        <div class="tab-pane fade" id="historico" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID Lote</th>
                                    <th>Data Envio</th>
                                    <th>Protocolo</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaHistorico">
                                <!-- Preenchido via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA 3: HISTÓRICO DE EVENTOS (Individual) -->
        <div class="tab-pane fade" id="eventos" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Evento</th>
                                    <th>Fornecedor</th>
                                    <th>Recibo</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="listaEventosHistorico">
                                <!-- Preenchido via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes do Lote -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-list-check me-2"></i>Detalhes do Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fornecedor</th>
                                <th>CNPJ</th>
                                <th>Status Evento</th>
                                <th>Recibo</th>
                                <th>Mensagem / Erro</th>
                                <th class="text-center" style="width: 80px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="listaEventosDetalhe">
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        carregarDados();

        // Checkbox "Selecionar Todos"
        $('#checkAll').change(function() {
            $('.check-fornecedor').prop('checked', $(this).prop('checked'));
        });
    });

    function carregarDados() {
        const periodo = $('#filtroPeriodo').val();
        
        $.ajax({
            url: '/sistema_irrf/public/api/api-reinf.php?action=listar_pendencias',
            type: 'GET',
            data: { periodo: periodo },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    renderPendencias(res.pendencias);
                    renderHistorico(res.historico);
                    renderEventos(res.eventos);
                } else {
                    alert('Erro ao carregar dados: ' + res.error);
                }
            },
            error: function() {
                $('#listaPendencias').html('<tr><td colspan="6" class="text-center text-danger">Erro de conexão.</td></tr>');
            }
        });
    }

    function renderPendencias(lista) {
        let html = '';
        if (lista.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhuma pendência encontrada para este período.</td></tr>';
        } else {
            lista.forEach(p => {
                html += `
                    <tr>
                        <td><input type="checkbox" class="form-check-input check-fornecedor" value="${p.id_fornecedor}"></td>
                        <td class="fw-bold">${p.razao_social}</td>
                        <td>${p.cnpj}</td>
                        <td class="text-center">${p.qtd_pagamentos}</td>
                        <td class="text-end">R$ ${parseFloat(p.total_bruto).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td class="text-end text-danger">R$ ${parseFloat(p.total_ir).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                    </tr>
                `;
            });
        }
        $('#listaPendencias').html(html);
    }

    function renderHistorico(lista) {
        let html = '';
        if (lista.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted py-4">Nenhum lote enviado.</td></tr>';
        } else {
            lista.forEach(l => {
                let badge = '';
                switch(l.status) {
                    case 'processado': badge = '<span class="badge bg-success">Processado</span>'; break;
                    case 'erro': badge = '<span class="badge bg-danger">Erro</span>'; break;
                    case 'enviado': badge = '<span class="badge bg-primary">Enviado</span>'; break;
                    case 'processando': badge = '<span class="badge bg-warning text-dark">Processando</span>'; break;
                    default: badge = `<span class="badge bg-secondary">${l.status}</span>`;
                }

                html += `
                    <tr>
                        <td>${l.id}</td>
                        <td>${new Date(l.created_at).toLocaleString('pt-BR')}</td>
                        <td><small class="text-monospace">${l.protocolo || '-'}</small></td>
                        <td>${badge}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="consultarLote(${l.id})" title="Atualizar Status">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="detalharLote(${l.id})" title="Ver Detalhes">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#listaHistorico').html(html);
    }

    function renderEventos(lista) {
        let html = '';
        if (!lista || lista.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted py-4">Nenhum evento encontrado.</td></tr>';
        } else {
            lista.forEach(e => {
                let badge = '';
                switch(e.status) {
                    case 'sucesso': badge = '<span class="badge bg-success">Sucesso</span>'; break;
                    case 'rejeitado': badge = '<span class="badge bg-danger">Rejeitado</span>'; break;
                    case 'excluido': badge = '<span class="badge bg-secondary">Excluído</span>'; break;
                    default: badge = `<span class="badge bg-warning text-dark">${e.status}</span>`;
                }
                
                let btnExcluir = '';
                let btnRetificar = '';

                if (e.status === 'sucesso') {
                    btnExcluir = `<button class="btn btn-sm btn-outline-danger" onclick="excluirEvento(${e.id})" title="Excluir Evento"><i class="bi bi-trash"></i></button>`;
                    if (e.tipo_evento === 'R-4020') {
                        btnRetificar = `<button class="btn btn-sm btn-outline-warning me-1" onclick="retificarEvento(${e.id_fornecedor}, '${e.per_apuracao}', '${e.razao_social.replace(/'/g, "\\'")}')" title="Retificar (Reenviar)"><i class="bi bi-pencil-square"></i></button>`;
                    }
                }

                html += `
                    <tr>
                        <td>${new Date(e.created_at).toLocaleString('pt-BR')}</td>
                        <td>${e.tipo_evento}</td>
                        <td>
                            <div class="fw-bold">${e.razao_social || 'N/A'}</div>
                            <small class="text-muted">${e.cnpj || ''}</small>
                        </td>
                        <td><small class="text-monospace">${e.numero_recibo || '-'}</small></td>
                        <td>${badge}</td>
                        <td class="text-end">${btnRetificar}${btnExcluir}</td>
                    </tr>
                `;
            });
        }
        $('#listaEventosHistorico').html(html);
    }

    // --- AÇÕES DE ENVIO ---

    function getSelecionados() {
        let ids = [];
        $('.check-fornecedor:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function enviarLote() {
        const ids = getSelecionados();
        if (ids.length === 0) {
            alert('Selecione pelo menos um fornecedor.');
            return;
        }

        if (!confirm(`Confirma o envio de eventos para ${ids.length} fornecedores?`)) return;

        const btn = $('button[onclick="enviarLote()"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enviando...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-reinf.php?action=enviar_lote',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                periodo: $('#filtroPeriodo').val(),
                fornecedores: ids
            }),
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    alert('Lote enviado com sucesso! Protocolo: ' + res.protocolo);
                    carregarDados();
                    // Muda para a aba de histórico
                    var tab = new bootstrap.Tab(document.querySelector('#historico-tab'));
                    tab.show();
                } else {
                    alert('Erro: ' + res.error);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Erro de conexão.');
            }
        });
    }

    function validarLote() {
        const ids = getSelecionados();
        if (ids.length === 0) {
            alert('Selecione pelo menos um fornecedor.');
            return;
        }

        const btn = $('button[onclick="validarLote()"]');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Validando...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-reinf.php?action=validar_lote',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                periodo: $('#filtroPeriodo').val(),
                fornecedores: ids
            }),
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    let msg = `Validação Concluída:\n\n✅ Válidos: ${res.validos.length}\n❌ Inválidos: ${res.invalidos.length}`;
                    
                    if (res.invalidos.length > 0) {
                        msg += '\n\nErros encontrados:\n';
                        res.invalidos.forEach(item => {
                            msg += `- ${item.fornecedor}: ${item.erros.join(', ')}\n`;
                        });
                    }
                    alert(msg);
                } else {
                    alert('Erro: ' + res.error);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Erro de conexão.');
            }
        });
    }

    // --- AÇÕES DE LOTE (HISTÓRICO) ---

    function consultarLote(idLote) {
        const btn = $(`button[onclick="consultarLote(${idLote})"]`);
        const icon = btn.find('i');
        icon.addClass('bi-spin'); // Animação

        $.post('/sistema_irrf/public/api/api-reinf.php?action=consultar_lote', { id_lote: idLote }, function(res) {
            icon.removeClass('bi-spin');
            if (res.success) {
                alert(res.mensagem);
                carregarDados(); // Recarrega a tabela
            } else {
                alert('Erro: ' + res.error);
            }
        }, 'json').fail(function() {
            icon.removeClass('bi-spin');
            alert('Erro de conexão.');
        });
    }

    let modalDetalhes;
    function detalharLote(idLote) {
        if (!modalDetalhes) modalDetalhes = new bootstrap.Modal(document.getElementById('modalDetalhes'));
        
        $('#listaEventosDetalhe').html('<tr><td colspan="6" class="text-center">Carregando...</td></tr>');
        modalDetalhes.show();

        $.get('/sistema_irrf/public/api/api-reinf.php?action=detalhar_lote', { id_lote: idLote }, function(res) {
            if (res.success) {
                let html = '';
                res.eventos.forEach(evt => {
                    let statusColor = 'secondary';
                    let btnExcluir = '';

                    if (evt.status === 'sucesso') {
                        statusColor = 'success';
                        // AQUI ESTÁ O BOTÃO DE EXCLUSÃO
                        btnExcluir = `
                            <button class="btn btn-sm btn-outline-danger" onclick="excluirEvento(${evt.id})" title="Excluir Evento (R-9000)">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    } else if (evt.status === 'rejeitado') {
                        statusColor = 'danger';
                    } else if (evt.status === 'processando') {
                        statusColor = 'warning text-dark';
                    }

                    html += `
                        <tr>
                            <td>${evt.razao_social}</td>
                            <td>${evt.cnpj}</td>
                            <td><span class="badge bg-${statusColor}">${evt.status}</span></td>
                            <td><small>${evt.numero_recibo || '-'}</small></td>
                            <td class="small text-muted" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                ${evt.mensagem_erro || '-'}
                            </td>
                            <td class="text-center">
                                ${btnExcluir}
                            </td>
                        </tr>
                    `;
                });
                $('#listaEventosDetalhe').html(html);
            } else {
                $('#listaEventosDetalhe').html(`<tr><td colspan="6" class="text-danger">${res.error}</td></tr>`);
            }
        }, 'json');
    }

    // --- RETIFICAÇÃO ---

    function retificarEvento(idFornecedor, periodo, razaoSocial) {
        if (!confirm(`Deseja RETIFICAR o evento de ${razaoSocial} para o período ${periodo}?\n\nATENÇÃO: O sistema irá gerar um novo evento contendo TODOS os pagamentos registrados atualmente para este fornecedor neste período, substituindo o evento anterior na Receita Federal.`)) {
            return;
        }

        // Feedback visual
        // Como não temos um botão específico clicado aqui (passado por parametro), usamos o overlay global
        $('#loadingOverlay').removeClass('d-none'); // Se houver overlay global, senão use alert
        
        $.ajax({
            url: '/sistema_irrf/public/api/api-reinf.php?action=enviar_lote',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                periodo: periodo,
                fornecedores: [idFornecedor]
            }),
            success: function(res) {
                $('#loadingOverlay').addClass('d-none');
                if (res.success) {
                    alert('Evento de retificação enviado com sucesso! Protocolo: ' + res.protocolo + '\n\nAguarde o processamento no histórico de lotes.');
                    carregarDados();
                } else {
                    alert('Erro ao enviar retificação: ' + res.error);
                }
            },
            error: function() {
                $('#loadingOverlay').addClass('d-none');
                alert('Erro de conexão ao tentar retificar.');
            }
        });
    }

    // --- EXCLUSÃO (R-9000) ---

    function excluirEvento(idEvento) {
        if (!confirm("ATENÇÃO: Deseja realmente excluir este evento da base da Receita Federal?\n\nEssa ação enviará um evento R-9000 solicitando a exclusão do evento original.")) {
            return;
        }

        // Feedback visual simples
        const btn = $('button[onclick="excluirEvento('+idEvento+')"]');
        if(btn.length) btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '/sistema_irrf/public/api/api-reinf.php?action=excluir_evento',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id_evento: idEvento }),
            success: function(res) {
                if (res.success) {
                    alert('Solicitação de exclusão enviada com sucesso!\nProtocolo: ' + res.protocolo + '\n\nConsulte o novo lote gerado no histórico para confirmar o processamento.');
                    if(typeof modalDetalhes !== 'undefined') modalDetalhes.hide();
                    carregarDados(); // Atualiza histórico para mostrar o novo lote de exclusão
                } else {
                    alert('Erro ao enviar exclusão: ' + res.error);
                    if(btn.length) btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            },
            error: function() {
                alert('Erro de conexão ao tentar excluir.');
                if(btn.length) btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>