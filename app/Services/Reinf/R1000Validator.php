<?php
// app/Services/Reinf/R1000Validator.php

class R1000Validator
{
    /**
     * Valida os dados do contribuinte antes de gerar o XML
     */
    public function validarDados($dados)
    {
        $erros = [];

        // Valida CNPJ do contribuinte
        if (empty($dados['cnpj'])) {
            $erros[] = "CNPJ do contribuinte é obrigatório";
        } else {
            $cnpj = preg_replace('/[^0-9]/', '', $dados['cnpj']);
            if (strlen($cnpj) != 14) {
                $erros[] = "CNPJ deve ter 14 dígitos";
            }
            if (strlen($dados['nrInsc'] ?? '') != 8) {
                $erros[] = "Número de inscrição (raiz do CNPJ) deve ter 8 dígitos";
            }
        }

        // Valida classificação tributária
        if (empty($dados['classificacao_tributaria'])) {
            $erros[] = "Classificação tributária é obrigatória";
        } elseif (!preg_match('/^\d{2}$/', $dados['classificacao_tributaria'])) {
            $erros[] = "Classificação tributária deve ter 2 dígitos";
        }

        // Valida indicadores
        if (!in_array($dados['indicador_ecd'] ?? null, [0, 1], true)) {
            $erros[] = "Indicador ECD deve ser 0 ou 1";
        }
        if (!in_array($dados['indicador_desoneracao'] ?? null, [0, 1], true)) {
            $erros[] = "Indicador de desoneração deve ser 0 ou 1";
        }

        // Valida contato
        if (empty($dados['contato']['nome'])) {
            $erros[] = "Nome do contato é obrigatório";
        } elseif (strlen($dados['contato']['nome']) > 70) {
            $erros[] = "Nome do contato deve ter no máximo 70 caracteres";
        }

        if (empty($dados['contato']['cpf'])) {
            $erros[] = "CPF do contato é obrigatório";
        } else {
            $cpf = preg_replace('/[^0-9]/', '', $dados['contato']['cpf']);
            if (strlen($cpf) != 11) {
                $erros[] = "CPF deve ter 11 dígitos";
            }
        }

        if (empty($dados['contato']['telefone'])) {
            $erros[] = "Telefone do contato é obrigatório";
        } else {
            $tel = preg_replace('/[^0-9]/', '', $dados['contato']['telefone']);
            if (strlen($tel) < 10 || strlen($tel) > 13) {
                $erros[] = "Telefone deve ter entre 10 e 13 dígitos";
            }
        }

        if (empty($dados['contato']['email'])) {
            $erros[] = "E-mail do contato é obrigatório";
        } elseif (!filter_var($dados['contato']['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido";
        } elseif (strlen($dados['contato']['email']) > 60) {
            $erros[] = "E-mail deve ter no máximo 60 caracteres";
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    /**
     * Valida o XML contra o schema XSD
     */
    public function validarXML($xml)
    {
        $erros = [];
        
        $xsdPath = __DIR__ . '/../../Schemas/R-1000-evtInfoContribuinte-v2_01_02f.xsd';
        $xsdPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $xsdPath);
        
        if (!file_exists($xsdPath)) {
            $erros[] = "Arquivo XSD não encontrado: " . basename($xsdPath);
            return ['valido' => false, 'erros' => $erros];
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        
        if (!$dom->loadXML($xml)) {
            $erros[] = "Erro ao carregar XML: documento mal formado";
            return ['valido' => false, 'erros' => $erros];
        }

        libxml_use_internal_errors(true);
        
        if (!$dom->schemaValidate($xsdPath)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                // Ignora erros relacionados à assinatura digital
                if (strpos($error->message, 'Signature') === false && 
                    strpos($error->message, 'xmldsig') === false) {
                    $erros[] = "Linha {$error->line}: " . trim($error->message);
                }
            }
            libxml_clear_errors();
        }

        return [
            'valido' => empty($erros),
            'erros' => $erros
        ];
    }

    /**
     * Valida se o CNPJ/CPF do contato é válido (cálculo dos dígitos verificadores)
     */
    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Elimina CPFs inválidos conhecidos
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Valida 1º dígito
        for ($i = 0, $j = 10, $soma = 0; $i < 9; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        $dv1 = $resto < 2 ? 0 : 11 - $resto;
        
        if ($cpf[9] != $dv1) {
            return false;
        }
        
        // Valida 2º dígito
        for ($i = 0, $j = 11, $soma = 0; $i < 10; $i++, $j--) {
            $soma += $cpf[$i] * $j;
        }
        $resto = $soma % 11;
        $dv2 = $resto < 2 ? 0 : 11 - $resto;
        
        return $cpf[10] == $dv2;
    }
}