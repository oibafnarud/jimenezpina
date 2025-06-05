<?php
/**
 * Footer del Panel Administrativo
 * Jiménez & Piña Survey Instruments
 */
?>
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start">
                            <p class="mb-0">
                                &copy; <?php echo date('Y'); ?> <strong>Jiménez & Piña Survey Instruments</strong>
                            </p>
                        </div>
                        <div class="col-6 text-end">
                            <ul class="list-inline">
                                <li class="list-inline-item">
                                    <a class="text-muted" href="<?php echo SITE_URL; ?>" target="_blank">Sitio Web</a>
                                </li>
                                <li class="list-inline-item">
                                    <a class="text-muted" href="<?php echo ADMIN_URL; ?>/ayuda.php">Ayuda</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Admin Scripts -->
    <script>
        // Sidebar toggle
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarToggle = document.querySelector(".js-sidebar-toggle");
            const sidebar = document.getElementById("sidebar");
            
            sidebarToggle.addEventListener("click", () => {
                sidebar.classList.toggle("collapsed");
                localStorage.setItem("sidebar-collapsed", sidebar.classList.contains("collapsed"));
            });
            
            // Restore sidebar state
            if (localStorage.getItem("sidebar-collapsed") === "true") {
                sidebar.classList.add("collapsed");
            }
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialize DataTables
        $(document).ready(function() {
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']]
                });
            }
            
            // Initialize Select2
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Seleccione una opción'
                });
            }
        });
        
        // Initialize TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '.tinymce',
                height: 400,
                language: 'es',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat | help',
                content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
                images_upload_url: '<?php echo ADMIN_URL; ?>/upload.php',
                automatic_uploads: true,
                file_picker_types: 'image',
                file_picker_callback: function(callback, value, meta) {
                    if (meta.filetype === 'image') {
                        var input = document.createElement('input');
                        input.setAttribute('type', 'file');
                        input.setAttribute('accept', 'image/*');
                        
                        input.onchange = function() {
                            var file = this.files[0];
                            var formData = new FormData();
                            formData.append('file', file);
                            
                            fetch('<?php echo ADMIN_URL; ?>/upload.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(result => {
                                callback(result.location, { title: file.name });
                            });
                        };
                        
                        input.click();
                    }
                }
            });
        }
        
        // Delete confirmation
        function confirmDelete(url, message = '¿Está seguro de eliminar este registro?') {
            Swal.fire({
                title: 'Confirmar eliminación',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
        
        // File preview
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById(previewId + '-container').style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Format currency input
        function formatCurrency(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('en-US');
                input.value = value;
            }
        }
        
        // Auto save draft
        let autoSaveTimer;
        function autoSaveDraft(formId) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const form = document.getElementById(formId);
                const formData = new FormData(form);
                formData.append('draft', '1');
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Borrador guardado automáticamente', 'info');
                    }
                });
            }, 5000); // Save after 5 seconds of inactivity
        }
    </script>
    
    <!-- Page specific scripts -->
    <?php if (isset($pageScripts)): ?>
    <?php echo $pageScripts; ?>
    <?php endif; ?>
</body>
</html>