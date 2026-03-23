            </div><!-- .content-wrapper -->
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> - BAC Procedural Timeline Tracking System<br>
                Aligned with the RA 12009 procurement timeline reference</p>
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
        window.APP_URL = <?php echo json_encode(APP_URL); ?>;
        window.SDO_BACTRACK_APP_URL = <?php echo json_encode(APP_URL); ?>;
        window.SDO_BACTRACK_TOKEN_PARAM = <?php echo json_encode(defined('AUTH_TOKEN_PARAM') ? AUTH_TOKEN_PARAM : 'auth_token'); ?>;
    </script>
    <script src="<?php echo APP_URL; ?>/assets/js/auth-token.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/admin.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/notifications.js"></script>
    <script>
    // Generalized Pagination for Data Tables and Activity Lists
    document.addEventListener('DOMContentLoaded', function() {
        function initPagination() {
            document.querySelectorAll('[data-paginate], .data-table').forEach(function(el) {
                if (el.hasAttribute('data-paginated')) return;
                
                var pageSize = parseInt(el.getAttribute('data-paginate')) || 15;
                var items;
                var isTable = el.classList.contains('data-table');
                
                if (isTable) {
                    items = Array.from(el.querySelectorAll('tbody tr'));
                } else {
                    // For activity-list or other containers
                    items = Array.from(el.children).filter(function(child) {
                        return !child.classList.contains('card-pagination') && 
                               !child.classList.contains('empty-state');
                    });
                }
                
                if (items.length <= pageSize) return;
                
                var page = 0;
                var totalPages = Math.ceil(items.length / pageSize);
                
                var nav = document.createElement('div');
                nav.className = 'card-pagination';
                
                var prevBtn = document.createElement('button');
                prevBtn.className = 'pagination-btn';
                prevBtn.type = 'button';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Prev';
                
                var pageInfo = document.createElement('span');
                pageInfo.className = 'pagination-info';
                
                var nextBtn = document.createElement('button');
                nextBtn.className = 'pagination-btn';
                nextBtn.type = 'button';
                nextBtn.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
                
                function showPage(p) {
                    items.forEach(function(item, i) {
                        item.style.display = (i >= p * pageSize && i < (p + 1) * pageSize) ? '' : 'none';
                    });
                    prevBtn.disabled = (p === 0);
                    nextBtn.disabled = (p === totalPages - 1);
                    pageInfo.textContent = (p + 1) + ' / ' + totalPages;
                }
                
                prevBtn.addEventListener('click', function(e) { 
                    e.preventDefault(); 
                    if (page > 0) showPage(--page); 
                });
                
                nextBtn.addEventListener('click', function(e) { 
                    e.preventDefault(); 
                    if (page < totalPages - 1) showPage(++page); 
                });
                
                nav.appendChild(prevBtn);
                nav.appendChild(pageInfo);
                nav.appendChild(nextBtn);
                
                el.after(nav);
                el.setAttribute('data-paginated', 'true');
                showPage(0);
            });
        }
        
        initPagination();
        
        // Expose to global if needed for dynamic content
        window.reinitPagination = initPagination;
    });
    </script>
    <?php if ($currentPage === 'calendar'): ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <?php endif; ?>
</body>
</html>
