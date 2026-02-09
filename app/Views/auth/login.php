<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema IRRF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            max-width: 400px;
            margin: 0 auto;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .logo h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-login {
            background: linear-gradient(90deg, var(--primary-color), #0a58ca);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 0.9rem;
        }

        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <div class="logo-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1>Sistema IRRF</h1>
                <p>Cálculo de Tributos para Órgãos Públicos</p>
            </div>

            <div id="mensagemLogin" class="alert alert-danger d-none" role="alert"></div>

            <form id="formLogin">
                <div class="mb-3">
                    <label for="login" class="form-label">Usuário</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="login" name="login" placeholder="Digite seu usuário"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha"
                            placeholder="Digite sua senha" required>
                    </div>
                </div>

                <div class="loading-spinner" id="loadingSpinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-login" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                </button>
            </form>

            <div class="footer-links mt-4">
                <p>Precisa de ajuda? <a href="#">Contate o administrador</a></p>
                <p class="text-muted mt-2">v1.0.0</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#formLogin').on('submit', function (e) {
                e.preventDefault();

                var login = $('#login').val().trim();
                var senha = $('#senha').val();

                if (!login || !senha) {
                    mostrarMensagem('Preencha todos os campos', 'danger');
                    return;
                }

                $('#loadingSpinner').show();
                $('#btnLogin').prop('disabled', true);

                $.ajax({
                    url: '/sistema_irrf/app/Controllers/AuthController.php?action=login',
                    type: 'POST',
                    data: {
                        login: login,
                        senha: senha
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            mostrarMensagem('Login realizado com sucesso! Redirecionando...', 'success');

                            // Usar URL de redirecionamento do response ou padrão
                            var redirectUrl = response.redirect || '/sistema_irrf/public/';

                            setTimeout(function () {
                                window.location.href = redirectUrl;
                            }, 1000);
                        } else {
                            mostrarMensagem(response.message, 'danger');
                            $('#btnLogin').prop('disabled', false);
                        }
                    },
                    error: function (xhr, status, error) {
                        mostrarMensagem('Erro ao realizar login. Tente novamente.', 'danger');
                        console.error('Erro:', error);
                        $('#btnLogin').prop('disabled', false);
                    },
                    complete: function () {
                        $('#loadingSpinner').hide();
                    }
                });
            });

            function mostrarMensagem(mensagem, tipo) {
                var alertClass = 'alert-' + tipo;
                $('#mensagemLogin')
                    .removeClass('d-none alert-danger alert-success')
                    .addClass(alertClass)
                    .html('<i class="bi bi-exclamation-circle me-2"></i>' + mensagem);

                // Auto-esconder após 5 segundos
                setTimeout(function () {
                    $('#mensagemLogin').addClass('d-none');
                }, 5000);
            }

            // Foco no campo de login
            $('#login').focus();
        });
    </script>
</body>

</html>