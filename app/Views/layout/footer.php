<?php
// app/Views/layout/footer.php
?>
</div> <!-- Fecha content-wrapper -->
</div> <!-- Fecha main-content -->

<!-- Footer -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                Sistema IRRF v1.0.0 &copy; <?php echo date('Y'); ?> - Todos os direitos reservados
            </div>
            <div class="col-md-6 text-end">
                Desenvolvido por <a href="#" class="text-primary">Contabilidade</a>
            </div>
        </div>
    </div>
</footer>

<script>
    $(document).ready(function () {
        // Toggle sidebar em mobile
        $('#menuToggle').click(function () {
            $('#sidebar').toggleClass('active');
        });

        // Fechar sidebar ao clicar fora em mobile
        $(document).click(function (event) {
            var sidebar = $('#sidebar');
            var toggle = $('#menuToggle');

            if ($(window).width() <= 768) {
                if (!sidebar.is(event.target) && sidebar.has(event.target).length === 0 &&
                    !toggle.is(event.target) && toggle.has(event.target).length === 0) {
                    sidebar.removeClass('active');
                }
            }
        });

        /*
        // A verificação de sessão a cada 5 minutos foi desativada para impedir o redirecionamento automático.
        //
        // setInterval(function () {
        //     $.ajax({
        //         url: '/sistema_irrf/app/Controllers/AuthController.php?action=verificar',
        //         type: 'GET',
        //         error: function () {
        //             window.location.href = '/sistema_irrf/public/login.php?session=expired';
        //         }
        //     });
        // }, 300000); // 5 minutos
        */
    });
</script>
</body>

</html>