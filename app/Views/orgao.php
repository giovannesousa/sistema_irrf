<?php
// app/Views/orgao.php

$titulo = "Órgão Público";
$pagina_atual = "orgao";

require_once __DIR__ . '/layout/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Dados do Órgão Público</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">CNPJ do Órgão</label>
                            <input type="text" class="form-control" value="00.000.000/0001-91" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Oficial</label>
                            <input type="text" class="form-control" value="PREFEITURA MUNICIPAL DE EXEMPLO">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">CEP</label>
                            <input type="text" class="form-control" placeholder="00000-000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logradouro</label>
                            <input type="text" class="form-control" placeholder="Rua, Avenida, etc.">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" class="form-control" placeholder="123">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Bairro</label>
                            <input type="text" class="form-control" placeholder="Centro">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input type="text" class="form-control" placeholder="Cidade Exemplo">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <select class="form-select">
                                <option>SP</option>
                                <!-- Outros estados -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Complemento</label>
                            <input type="text" class="form-control" placeholder="Sala 101">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Responsável</label>
                            <input type="text" class="form-control" placeholder="Nome do responsável">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" class="form-control" placeholder="responsavel@orgao.gov.br">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Salvar Alterações
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/layout/footer.php';