<?php
// app/Controllers/BaseController.php

class BaseController {
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function validateRequiredFields($fields, $data) {
        $missing = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return ["success" => false, "error" => "Campos obrigatórios faltando: " . implode(', ', $missing)];
        }
        
        return ["success" => true];
    }
}