<?php
// app/Views/orgaos.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Core/Session.php';

Session::start();
if (!Session::isLoggedIn()) {
    header('Location: /sistema_irrf/public/login.php');
    exit;
}

$usuarioLogado = Session::getUser();
if (($usuarioLogado['nivel_acesso'] ?? '') !== 'admin') {
    header('Location: /sistema_irrf/public/dashboard');
    exit;
}

$titulo = "Gerenciamento de Órgãos";
$pagina_atual = "orgaos";

require_once __DIR__ . '/layout/header.php';
?>

<style>
    .avatar-orgao {
        width: 40px;
        height: 40px;
        background-color: #e9ecef;
        color: var(--primary-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    /* Estilo para as abas do modal */
    .nav-tabs .nav-link {
        color: #6c757d; /* Cinza escuro para inativas */
    }
    .nav-tabs .nav-link.active {
        color: var(--primary-color); /* Azul para ativa */
        font-weight: 600;
    }
</style>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                <i class="bi bi-building me-2"></i>Órgãos Públicos
            </h1>
            <p class="text-muted mb-0">Gerencie as entidades cadastradas no sistema</p>
        </div>
        <button class="btn btn-primary" onclick="abrirModalNovo()">
            <i class="bi bi-plus-circle me-2"></i>Novo Órgão
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelaOrgaos">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Órgão</th>
                            <th>CNPJ</th>
                            <th>Cidade/UF</th>
                            <th>Responsável</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaOrgaosBody">
                        <!-- Carregado via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalOrgao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="modalOrgaoTitle">
                    <i class="bi bi-building me-2"></i>Novo Órgão
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formOrgao" enctype="multipart/form-data">
                    <input type="hidden" id="orgaoId" name="id">

                    <!-- Navegação em Abas -->
                    <ul class="nav nav-tabs mb-3" id="orgaoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button" role="tab">Dados Gerais</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="endereco-tab" data-bs-toggle="tab" data-bs-target="#endereco" type="button" role="tab">Endereço</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="certificado-tab" data-bs-toggle="tab" data-bs-target="#certificado" type="button" role="tab">Certificado & Config</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="orgaoTabsContent">
                        <!-- Aba Dados Gerais -->
                        <div class="tab-pane fade show active" id="dados" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">CNPJ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="cnpj" id="cnpj" required placeholder="00.000.000/0000-00">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nome Oficial <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nome_oficial" id="nome_oficial" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Responsável (Nome)</label>
                                    <input type="text" class="form-control" name="responsavel_nome" id="responsavel_nome">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Responsável (E-mail)</label>
                                    <input type="email" class="form-control" name="responsavel_email" id="responsavel_email">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Logo do Órgão</label>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Aba Endereço -->
                        <div class="tab-pane fade" id="endereco" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" class="form-control" name="cep" id="cep">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Logradouro</label>
                                    <input type="text" class="form-control" name="logradouro" id="logradouro">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Número</label>
                                    <input type="text" class="form-control" name="numero" id="numero">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" class="form-control" name="bairro" id="bairro">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" class="form-control" name="cidade" id="cidade">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">UF</label>
                                    <input type="text" class="form-control" name="uf" id="uf" maxlength="2">
                                </div>
                            </div>
                        </div>

                        <!-- Aba Certificado -->
                        <div class="tab-pane fade" id="certificado" role="tabpanel">
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle me-1"></i> Configurações para envio da EFD-Reinf.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Arquivo do Certificado (.pfx)</label>
                                    <input type="file" class="form-control" name="certificado" accept=".pfx">
                                    <small class="text-muted" id="certAtualHelp">Deixe em branco para manter o atual.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Senha do Certificado</label>
                                    <input type="password" class="form-control" name="certificado_senha" id="certificado_senha">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Classificação Tributária</label>
                                    <select class="form-select" name="classificacao_tributaria" id="classificacao_tributaria">
                                        <option value="99">99 - Pessoas Jurídicas em Geral</option>
                                        <option value="85">85 - Administração Pública</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvar">
                    <i class="bi bi-save me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    let modalOrgao;

    $(document).ready(function() {
        modalOrgao = new bootstrap.Modal(document.getElementById('modalOrgao'));
        
        // Máscaras
        $('#cnpj').mask('00.000.000/0000-00');
        $('#cep').mask('00000-000');

        carregarOrgaos();

        $('#btnSalvar').click(salvarOrgao);
    });

    function carregarOrgaos() {
        $.ajax({
            url: '/sistema_irrf/public/api/api-orgao.php?action=listar',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let html = '';
                    if (res.dados.length === 0) {
                        html = '<tr><td colspan="5" class="text-center py-4 text-muted">Nenhum órgão cadastrado.</td></tr>';
                    } else {
                        res.dados.forEach(orgao => {
                            html += `
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-orgao me-3">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">${orgao.nome_oficial}</div>
                                                <small class="text-muted">ID: ${orgao.id}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${orgao.cnpj}</td>
                                    <td>${orgao.cidade || '-'} / ${orgao.uf || '-'}</td>
                                    <td>${orgao.responsavel_nome || '-'}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-primary btn-sm me-1" onclick="editarOrgao(${orgao.id})" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="excluirOrgao(${orgao.id}, '${orgao.nome_oficial}')" title="Excluir">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#listaOrgaosBody').html(html);
                } else {
                    alert('Erro ao carregar órgãos: ' + res.error);
                }
            },
            error: function() {
                alert('Erro de conexão ao carregar órgãos.');
            }
        });
    }

    function abrirModalNovo() {
        $('#formOrgao')[0].reset();
        $('#orgaoId').val('');
        $('#modalOrgaoTitle').html('<i class="bi bi-building me-2"></i>Novo Órgão');
        $('#certAtualHelp').hide();
        
        // Reset tabs
        var firstTabEl = document.querySelector('#orgaoTabs button[data-bs-target="#dados"]');
        var tab = new bootstrap.Tab(firstTabEl);
        tab.show();

        modalOrgao.show();
    }

    function editarOrgao(id) {
        $.ajax({
            url: '/sistema_irrf/public/api/api-orgao.php?action=buscar&id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let d = res.dados;
                    $('#orgaoId').val(d.id);
                    $('#cnpj').val(d.cnpj).trigger('input'); // Trigger mask
                    $('#nome_oficial').val(d.nome_oficial);
                    $('#responsavel_nome').val(d.responsavel_nome);
                    $('#responsavel_email').val(d.responsavel_email);
                    
                    $('#cep').val(d.cep);
                    $('#logradouro').val(d.logradouro);
                    $('#numero').val(d.numero);
                    $('#bairro').val(d.bairro);
                    $('#cidade').val(d.cidade);
                    $('#uf').val(d.uf);

                    $('#classificacao_tributaria').val(d.classificacao_tributaria || '99');
                    $('#certificado_senha').val(''); // Não mostra a senha
                    $('#certAtualHelp').show().text(d.certificado_arquivo ? 'Certificado atual: ' + d.certificado_arquivo : 'Nenhum certificado configurado.');

                    $('#modalOrgaoTitle').html('<i class="bi bi-pencil-square me-2"></i>Editar Órgão');
                    modalOrgao.show();
                } else {
                    alert('Erro ao buscar dados: ' + res.error);
                }
            }
        });
    }

    function salvarOrgao() {
        let form = document.getElementById('formOrgao');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let formData = new FormData(form);
        let btn = $('#btnSalvar');
        let originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-orgao.php?action=salvar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    modalOrgao.hide();
                    carregarOrgaos();
                    alert(res.message);
                } else {
                    alert('Erro: ' + res.error);
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                alert('Erro de conexão ao salvar.');
            }
        });
    }

    function excluirOrgao(id, nome) {
        if (confirm(`Tem certeza que deseja excluir o órgão "${nome}"?\nEsta ação não pode ser desfeita.`)) {
            $.post('/sistema_irrf/public/api/api-orgao.php?action=excluir', { id: id }, function(res) {
                if (res.success) {
                    carregarOrgaos();
                } else {
                    alert('Erro: ' + res.error);
                }
            }, 'json');
        }
    }
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>