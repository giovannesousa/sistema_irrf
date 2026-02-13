<?php
// app/Controllers/OrgaoController.php

require_once __DIR__ . '/../Models/Orgao.php';
require_once __DIR__ . '/BaseController.php';

class OrgaoController extends BaseController {
    private $orgaoModel;

    public function __construct() {
        $this->orgaoModel = new Orgao();
    }

    public function listar() {
        try {
            $orgaos = $this->orgaoModel->listar();
            $this->jsonResponse(['success' => true, 'dados' => $orgaos]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function salvar() {
        try {
            // Verifica permissão (apenas admin deveria criar órgãos)
            if (session_status() === PHP_SESSION_NONE) {
                session_name('sistema_irrf_session'); // Garante o nome correto da sessão
                session_start();
            }
            
            if (($_SESSION['usuario']['nivel_acesso'] ?? '') !== 'admin') {
                throw new Exception("Acesso negado.");
            }

            $dados = $_POST;
            $id = $dados['id'] ?? null;

            // Validação básica
            $required = ['cnpj', 'nome_oficial'];
            $val = $this->validateRequiredFields($required, $dados);
            if (!$val['success']) {
                $this->jsonResponse($val, 400);
            }

            // Limpa CNPJ
            $dados['cnpj'] = preg_replace('/[^0-9]/', '', $dados['cnpj']);

            // Verifica duplicidade de CNPJ na inserção
            if (!$id) {
                $existente = $this->orgaoModel->buscarPorCnpj($dados['cnpj']);
                if ($existente) {
                    throw new Exception("Já existe um órgão cadastrado com este CNPJ.");
                }
            }

            // Upload de Logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/logos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $nomeArquivo = 'logo_' . $dados['cnpj'] . '.' . $ext;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $nomeArquivo)) {
                    $dados['caminho_logo'] = 'uploads/logos/' . $nomeArquivo;
                }
            }

            // Upload de Certificado
            if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] === UPLOAD_ERR_OK) {
                $certDir = __DIR__ . '/../../certificados/';
                if (!is_dir($certDir)) mkdir($certDir, 0777, true);
                
                $ext = pathinfo($_FILES['certificado']['name'], PATHINFO_EXTENSION);
                if (strtolower($ext) !== 'pfx') {
                    throw new Exception("O certificado deve ser um arquivo .pfx");
                }

                $nomeCert = 'cert_' . $dados['cnpj'] . '_' . time() . '.pfx';
                
                if (move_uploaded_file($_FILES['certificado']['tmp_name'], $certDir . $nomeCert)) {
                    $dados['certificado_arquivo'] = $nomeCert;
                }
            }

            // Se for edição e a senha não foi enviada, remove do array para não apagar a existente
            if ($id && empty($dados['certificado_senha'])) {
                unset($dados['certificado_senha']);
            }

            if ($id) {
                $this->orgaoModel->atualizar($id, $dados);
                $msg = "Órgão atualizado com sucesso!";
            } else {
                $id = $this->orgaoModel->salvar($dados);
                $msg = "Órgão cadastrado com sucesso!";
            }

            $this->jsonResponse(['success' => true, 'message' => $msg, 'id' => $id]);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function excluir() {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_name('sistema_irrf_session'); // Garante o nome correto da sessão
                session_start();
            }

            if (($_SESSION['usuario']['nivel_acesso'] ?? '') !== 'admin') {
                throw new Exception("Acesso negado.");
            }

            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID não informado.");

            // Impede excluir o próprio órgão logado (segurança básica)
            if ($id == $_SESSION['usuario']['id_orgao']) {
                throw new Exception("Não é possível excluir o órgão que você está utilizando no momento.");
            }

            $this->orgaoModel->excluir($id);
            $this->jsonResponse(['success' => true, 'message' => 'Órgão excluído com sucesso.']);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function buscar() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não informado.");

            $dados = $this->orgaoModel->buscarPorId($id);
            $this->jsonResponse(['success' => true, 'dados' => $dados]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}