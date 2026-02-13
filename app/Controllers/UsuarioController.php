<?php
// app/Controllers/UsuarioController.php

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Core/Session.php';

class UsuarioController extends BaseController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function listar() {
        try {
            Session::start();
            $usuarioLogado = Session::getUser();
            
            // Se for admin, lista todos. Se não, filtra pelo órgão.
            if (($usuarioLogado['nivel_acesso'] ?? '') === 'admin') {
                $usuarios = $this->usuarioModel->listarTodos();
            } else {
                $usuarios = $this->usuarioModel->listarPorOrgao(Session::getIdOrgao());
            }
            
            // Remove hash de senha da listagem por segurança
            foreach ($usuarios as &$user) {
                unset($user['senha_hash']);
            }

            $this->jsonResponse(['success' => true, 'dados' => $usuarios]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function salvar() {
        try {
            Session::start();
            $usuarioLogado = Session::getUser();
            
            if (($usuarioLogado['nivel_acesso'] ?? '') !== 'admin') {
                throw new Exception("Acesso negado. Apenas administradores podem gerenciar usuários.");
            }

            // Prioriza o órgão enviado no POST, senão usa o da sessão
            $idOrgao = !empty($_POST['id_orgao']) ? $_POST['id_orgao'] : Session::getIdOrgao();
            
            if (!$idOrgao) throw new Exception("É obrigatório vincular o usuário a um órgão.");

            $dados = $_POST;
            $id = $dados['id'] ?? null;

            // Validação básica
            $required = ['nome', 'login', 'nivel_acesso'];
            $val = $this->validateRequiredFields($required, $dados);
            if (!$val['success']) {
                $this->jsonResponse($val, 400);
            }

            // Verifica duplicidade de login
            $existente = $this->usuarioModel->buscarPorLogin($dados['login']);
            if ($existente && (!$id || $existente['id'] != $id)) {
                throw new Exception("Este login já está em uso por outro usuário.");
            }

            // Prepara dados para o model
            $dadosModel = [
                'id_orgao' => $idOrgao,
                'nome' => $dados['nome'],
                'login' => $dados['login'],
                'nivel_acesso' => $dados['nivel_acesso']
            ];

            // Tratamento de senha
            if ($id) {
                // Edição
                if (!empty($dados['senha'])) {
                    $dadosModel['senha_hash'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
                }
                $this->usuarioModel->atualizar($id, $dadosModel);
                $msg = "Usuário atualizado com sucesso!";
            } else {
                // Criação
                if (empty($dados['senha'])) {
                    throw new Exception("A senha é obrigatória para novos usuários.");
                }
                $dadosModel['senha_hash'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
                $id = $this->usuarioModel->salvar($dadosModel);
                $msg = "Usuário criado com sucesso!";
            }

            $this->jsonResponse(['success' => true, 'message' => $msg, 'id' => $id]);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function excluir() {
        try {
            Session::start();
            $usuarioLogado = Session::getUser();

            if (($usuarioLogado['nivel_acesso'] ?? '') !== 'admin') {
                throw new Exception("Acesso negado.");
            }

            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("ID não informado.");

            // Impede excluir o próprio usuário
            if ($id == $usuarioLogado['id']) {
                throw new Exception("Você não pode excluir seu próprio usuário.");
            }

            // Verifica se o usuário pertence ao mesmo órgão (segurança)
            $usuarioAlvo = $this->usuarioModel->buscarPorId($id);
            if (!$usuarioAlvo || $usuarioAlvo['id_orgao'] != Session::getIdOrgao()) {
                throw new Exception("Usuário não encontrado ou não pertence ao seu órgão.");
            }

            $this->usuarioModel->excluir($id);
            $this->jsonResponse(['success' => true, 'message' => 'Usuário excluído com sucesso.']);

        } catch (Throwable $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function buscar() {
        try {
            Session::start();
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception("ID não informado.");

            $usuario = $this->usuarioModel->buscarPorId($id);
            
            // Verifica permissão de visualização (mesmo órgão)
            if (!$usuario || $usuario['id_orgao'] != Session::getIdOrgao()) {
                throw new Exception("Usuário não encontrado.");
            }

            $this->jsonResponse(['success' => true, 'dados' => $usuario]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
