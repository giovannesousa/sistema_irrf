<?php
// app/Views/usuarios.php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Core/Session.php';

Session::start();

// Verificação de segurança
if (!Session::isLoggedIn()) {
    header('Location: /sistema_irrf/public/login.php');
    exit;
}

$usuarioLogado = Session::getUser();

// Apenas admin pode acessar
if (($usuarioLogado['nivel_acesso'] ?? '') !== 'admin') {
    // Redirecionar para dashboard se não for admin
    header('Location: /sistema_irrf/public/dashboard');
    exit;
}

$titulo = "Gerenciamento de Usuários";
$pagina_atual = "usuarios";
$idOrgao = Session::getIdOrgao() ?? $usuarioLogado['id_orgao'] ?? null;

// Instanciar modelo e buscar dados
$usuarioModel = new Usuario();
if (($usuarioLogado['nivel_acesso'] ?? '') === 'admin') {
    $listaUsuarios = $usuarioModel->listarTodos();
} else {
    $listaUsuarios = $usuarioModel->listarPorOrgao($idOrgao);
}

require_once __DIR__ . '/layout/header.php';
?>

<style>
    :root {
        --primary-color: #0d6efd;
        --secondary-color: #6c757d;
    }
    
    .card-header-custom {
        background: linear-gradient(90deg, var(--primary-color), #0a58ca);
        color: white;
        padding: 1rem 1.5rem;
    }
    
    .btn-custom-primary {
        background: linear-gradient(90deg, var(--primary-color), #0a58ca);
        color: white;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-custom-primary:hover {
        background: linear-gradient(90deg, #0a58ca, #084298);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }

    .avatar-initials {
        width: 35px;
        height: 35px;
        background-color: #e9ecef;
        color: var(--primary-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
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
</style>

<div class="main-container">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color: var(--primary-color); font-weight: 700;">
                <i class="bi bi-people-fill me-2"></i>Gerenciamento de Usuários
            </h1>
            <p class="text-muted mb-0">Cadastre e gerencie os usuários do sistema</p>
        </div>
        <button class="btn btn-custom-primary" onclick="abrirModalNovo()">
            <i class="bi bi-person-plus-fill me-2"></i>Novo Usuário
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h6 class="card-title text-muted mb-3"><i class="bi bi-funnel me-1"></i> Filtros de Pesquisa</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="buscaTexto" placeholder="Buscar por nome ou login...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroNivel">
                        <option value="">Todos os Níveis</option>
                        <option value="admin">Administrador</option>
                        <option value="operador">Operador</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-outline-secondary" onclick="limparFiltros()">
                        <i class="bi bi-x-circle me-1"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class="card border-0 shadow-sm">
        <div class="card-header card-header-custom rounded-top">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Usuários Cadastrados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelaUsuarios">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Usuário</th>
                            <th>Órgão</th>
                            <th>Login</th>
                            <th>Nível de Acesso</th>
                            <th>Data Cadastro</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listaUsuarios)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-4 d-block mb-3"></i>
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listaUsuarios as $user): ?>
                                <tr class="user-row" data-nome="<?php echo strtolower($user['nome']); ?>" data-login="<?php echo strtolower($user['login']); ?>" data-nivel="<?php echo $user['nivel_acesso']; ?>">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initials me-3">
                                                <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($user['nome']); ?></div>
                                                <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($user['orgao_nome'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-secondary"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($user['login']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['nivel_acesso'] === 'admin'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 rounded-pill px-3">
                                                <i class="bi bi-shield-lock-fill me-1"></i> Administrador
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10 rounded-pill px-3">
                                                <i class="bi bi-person-badge me-1"></i> Operador
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-outline-primary action-btn me-1" onclick='editarUsuario(<?php echo json_encode($user); ?>)' title="Editar">
                                            <i class="bi bi-pencil-fill" style="font-size: 0.9rem;"></i>
                                        </button>
                                        <?php if ($user['id'] != $usuarioLogado['id']): ?>
                                            <button class="btn btn-outline-danger action-btn" onclick="excluirUsuario(<?php echo $user['id']; ?>, '<?php echo addslashes($user['nome']); ?>')" title="Excluir">
                                                <i class="bi bi-trash-fill" style="font-size: 0.9rem;"></i>
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
            <small class="text-muted">Total de usuários: <span id="totalUsuarios"><?php echo count($listaUsuarios); ?></span></small>
        </div>
    </div>
</div>

<!-- Modal Cadastro/Edição -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="modalUsuarioTitle">
                    <i class="bi bi-person-plus me-2"></i>Novo Usuário
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formUsuario">
                    <input type="hidden" id="usuarioId" name="id">
                    
                    <div class="mb-3">
                        <label for="id_orgao" class="form-label fw-semibold">Órgão Vinculado <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                            <select class="form-select" id="id_orgao" name="id_orgao" required></select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nome" class="form-label fw-semibold">Nome Completo <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="nome" name="nome" required placeholder="Ex: João da Silva">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="login" class="form-label fw-semibold">Login de Acesso <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                            <input type="text" class="form-control" id="login" name="login" required placeholder="Ex: joao.silva">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label fw-semibold">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="******">
                        </div>
                        <div class="form-text text-muted small mt-1" id="senhaHelp">
                            <i class="bi bi-info-circle me-1"></i>Na edição, deixe em branco para manter a senha atual.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nivel_acesso" class="form-label fw-semibold">Nível de Acesso <span class="text-danger">*</span></label>
                        <select class="form-select" id="nivel_acesso" name="nivel_acesso" required>
                            <option value="operador">Operador (Acesso padrão)</option>
                            <option value="admin">Administrador (Acesso total)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary px-4" id="btnSalvar">
                    <i class="bi bi-save me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal instance
    let modalUsuario;

    document.addEventListener('DOMContentLoaded', function() {
        modalUsuario = new bootstrap.Modal(document.getElementById('modalUsuario'));
        
        // Filtro de pesquisa
        const buscaTexto = document.getElementById('buscaTexto');
        const filtroNivel = document.getElementById('filtroNivel');
        
        function filtrarTabela() {
            const texto = buscaTexto.value.toLowerCase();
            const nivel = filtroNivel.value;
            const linhas = document.querySelectorAll('.user-row');
            let visiveis = 0;
            
            linhas.forEach(linha => {
                const nome = linha.dataset.nome;
                const login = linha.dataset.login;
                const nivelRow = linha.dataset.nivel;
                
                const matchTexto = nome.includes(texto) || login.includes(texto);
                const matchNivel = nivel === '' || nivelRow === nivel;
                
                if (matchTexto && matchNivel) {
                    linha.style.display = '';
                    visiveis++;
                } else {
                    linha.style.display = 'none';
                }
            });
            
            document.getElementById('totalUsuarios').textContent = visiveis;
        }
        
        buscaTexto.addEventListener('keyup', filtrarTabela);
        filtroNivel.addEventListener('change', filtrarTabela);
        
        // Botão Salvar
        document.getElementById('btnSalvar').addEventListener('click', salvarUsuario);
        carregarOrgaosSelect();
    });

    function limparFiltros() {
        document.getElementById('buscaTexto').value = '';
        document.getElementById('filtroNivel').value = '';
        
        // Disparar evento para atualizar tabela
        const event = new Event('change');
        document.getElementById('filtroNivel').dispatchEvent(event);
    }

    function carregarOrgaosSelect() {
        $.ajax({
            url: '/sistema_irrf/public/api/api-orgao.php?action=listar',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let options = '<option value="">Selecione um órgão...</option>';
                    res.dados.forEach(orgao => {
                        options += `<option value="${orgao.id}">${orgao.nome_oficial}</option>`;
                    });
                    $('#id_orgao').html(options);
                }
            }
        });
    }

    function abrirModalNovo() {
        document.getElementById('formUsuario').reset();
        document.getElementById('usuarioId').value = '';
        // Define o órgão atual como padrão se disponível
        <?php if ($idOrgao): ?>
        $('#id_orgao').val('<?php echo $idOrgao; ?>');
        <?php endif; ?>
        document.getElementById('modalUsuarioTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Novo Usuário';
        document.getElementById('senha').setAttribute('required', 'required');
        document.getElementById('senhaHelp').style.display = 'none';
        
        modalUsuario.show();
    }

    function editarUsuario(user) {
        // Preencher formulário
        document.getElementById('usuarioId').value = user.id;
        $('#id_orgao').val(user.id_orgao || ''); // Seleciona o órgão do usuário
        document.getElementById('nome').value = user.nome;
        document.getElementById('login').value = user.login;
        document.getElementById('nivel_acesso').value = user.nivel_acesso;
        document.getElementById('senha').value = '';
        
        // Ajustes visuais para edição
        document.getElementById('modalUsuarioTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Usuário';
        document.getElementById('senha').removeAttribute('required');
        document.getElementById('senhaHelp').style.display = 'block';
        
        modalUsuario.show();
    }

    function salvarUsuario() {
        const form = document.getElementById('formUsuario');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        
        // Feedback visual
        const btn = $('#btnSalvar');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Salvando...');

        $.ajax({
            url: '/sistema_irrf/public/api/api-usuario.php?action=salvar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    modalUsuario.hide();
                    alert(res.message);
                    location.reload(); // Recarrega para atualizar a lista
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

    function excluirUsuario(id, nome) {
        if (confirm(`Tem certeza que deseja excluir o usuário "${nome}"?\nEsta ação não pode ser desfeita.`)) {
            $.post('/sistema_irrf/public/api/api-usuario.php?action=excluir', { id: id }, function(res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Erro: ' + res.error);
                }
            }, 'json').fail(function() {
                alert('Erro de conexão ao excluir.');
            });
        }
    }
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>