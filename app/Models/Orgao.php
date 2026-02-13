<?php
// app/Models/Orgao.php

require_once __DIR__ . '/../Core/Database.php';

class Orgao {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function listar() {
        $stmt = $this->db->query("SELECT * FROM orgaos ORDER BY nome_oficial");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM orgaos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCnpj($cnpj) {
        $stmt = $this->db->prepare("SELECT * FROM orgaos WHERE cnpj = ?");
        $stmt->execute([$cnpj]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar($dados) {
        // Campos permitidos baseados na estrutura da tabela
        $camposPermitidos = [
            'cnpj', 'nome_oficial', 'cep', 'logradouro', 'numero', 'bairro', 
            'cidade', 'uf', 'complemento', 'responsavel_nome', 'responsavel_email',
            'caminho_logo', 'texto_cabecalho', 'texto_rodape',
            'classificacao_tributaria', 'indicador_ecd', 'indicador_desoneracao',
            'contato_nome', 'contato_cpf', 'contato_telefone', 'contato_email',
            'certificado_arquivo', 'certificado_senha'
        ];

        $campos = [];
        $valores = [];
        $params = [];

        foreach ($dados as $chave => $valor) {
            if (in_array($chave, $camposPermitidos)) {
                $campos[] = $chave;
                $valores[] = "?";
                $params[] = $valor;
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "INSERT INTO orgaos (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function atualizar($id, $dados) {
        $camposPermitidos = [
            'cnpj', 'nome_oficial', 'cep', 'logradouro', 'numero', 'bairro', 
            'cidade', 'uf', 'complemento', 'responsavel_nome', 'responsavel_email',
            'caminho_logo', 'texto_cabecalho', 'texto_rodape',
            'classificacao_tributaria', 'indicador_ecd', 'indicador_desoneracao',
            'contato_nome', 'contato_cpf', 'contato_telefone', 'contato_email',
            'certificado_arquivo', 'certificado_senha',
            'r1000_enviado', 'r1000_recibo', 'r1000_data_envio'
        ];

        $sets = [];
        $params = [];

        foreach ($dados as $chave => $valor) {
            if (in_array($chave, $camposPermitidos)) {
                $sets[] = "$chave = ?";
                $params[] = $valor;
            }
        }

        if (empty($sets)) return true; // Nada a atualizar

        $params[] = $id;
        $sql = "UPDATE orgaos SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function excluir($id) {
        // Verifica se existem registros vinculados antes de excluir para manter integridade
        
        // Verifica usuários vinculados
        $stmtUsers = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_orgao = ?");
        $stmtUsers->execute([$id]);
        if ($stmtUsers->fetchColumn() > 0) {
            throw new Exception("Não é possível excluir o órgão pois existem usuários vinculados.");
        }

        // Verifica notas fiscais vinculadas
        $stmtNotas = $this->db->prepare("SELECT COUNT(*) FROM notas_fiscais WHERE id_orgao = ?");
        $stmtNotas->execute([$id]);
        if ($stmtNotas->fetchColumn() > 0) {
            throw new Exception("Não é possível excluir o órgão pois existem notas fiscais vinculadas.");
        }

        $stmt = $this->db->prepare("DELETE FROM orgaos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}