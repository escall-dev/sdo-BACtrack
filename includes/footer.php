            </div><!-- .content-wrapper -->
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - BAC Procedural Timeline Tracking System<br>
                Based on RA 9184 IRR</p>
            </footer>
        </main>
    </div>

    <!-- Activity Detail Modal -->
    <div class="modal-overlay" id="activityModal">
        <div class="modal-container">
            <div class="modal-header">
                <h2 id="modalTitle">Activity Details</h2>
                <button class="modal-close" onclick="closeActivityModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        window.SDO_BACTRACK_APP_URL = <?php echo json_encode(APP_URL); ?>;
        window.SDO_BACTRACK_TOKEN_PARAM = <?php echo json_encode(defined('AUTH_TOKEN_PARAM') ? AUTH_TOKEN_PARAM : 'auth_token'); ?>;
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/auth-token.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/admin.js"></script>
    <?php if ($currentPage === 'calendar'): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <?php endif; ?>
</body>
</html>
