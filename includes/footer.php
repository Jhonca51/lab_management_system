<?php if (isLoggedIn()): ?>
    </div> <!-- Cerrar container principal -->
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-flask me-2"></i><?php echo APP_NAME; ?></h6>
                    <p class="text-muted mb-0">Sistema de gestión de laboratorios médicos</p>
                    <small class="text-muted">Versión <?php echo APP_VERSION; ?></small>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-1">
                        <i class="fas fa-clock me-1"></i>
                        <?php echo date('d/m/Y H:i:s'); ?>
                    </p>
                    <?php if (isLoggedIn()): ?>
                        <small class="text-muted">
                            Conectado como: <?php echo htmlspecialchars(getCurrentUser()['username']); ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts inline si están definidos -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?php echo $inlineJS; ?>
        </script>
    <?php endif; ?>
</body>
</html>