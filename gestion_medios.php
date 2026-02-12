<?php
/**
 * gestion_medios.php - Página de gestión de contenidos multimedia
 * MDKBoyacá - Sistema de Gestión de Contenidos
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';

// Verificar que el usuario es administrador
require_login('admin');

// ============================================
// ESCANEAR SECCIONES DESDE APRENDIZAJE.HTML
// ============================================
function obtenerSeccionesDesdeAprendizaje()
{
    $secciones = [];
    $archivo_html = __DIR__ . '/aprendizaje.html';

    if (!file_exists($archivo_html)) {
        return []; // Fallback vacío si no existe el archivo
    }

    $contenido = file_get_contents($archivo_html);

    // Buscar todas las etiquetas <a class="valor-card ..."> con href y h3
    // Patrón: <a href="archivo.php" class="valor-card ...">...<h3>Titulo</h3>...</a>
    preg_match_all('/<a\s+href="([^"]+\.php)"\s+class="valor-card[^"]*"[^>]*>.*?<h3>([^<]+)<\/h3>.*?<\/a>/is', $contenido, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $href = $match[1];
        $titulo = trim($match[2]);

        // Extraer nombre de sección del href (ej: taeguk.php → taeguk)
        $seccion_nombre = pathinfo($href, PATHINFO_FILENAME);

        // Excluir cinturones ya que tiene lógica especial
        if ($seccion_nombre !== 'cinturones') {
            $secciones[$seccion_nombre] = $titulo;
        }
    }

    return $secciones;
}

// Obtener secciones dinámicamente
$secciones_dinamicas = obtenerSeccionesDesdeAprendizaje();

// Secciones fijas (siempre disponibles)
$secciones_fijas = [
    'cinturones' => 'Cinturones de Taekwondo',
    'galeria' => 'Galería de Imágenes'
];

// Combinar: primero cinturones, luego dinámicas, luego galería
$todas_secciones = ['cinturones' => $secciones_fijas['cinturones']] + $secciones_dinamicas + ['galeria' => $secciones_fijas['galeria']];

// Configuración de subsecciones por sección
$subsecciones_config = [
    'cinturones' => [
        ['value' => 'blanco', 'label' => 'Blanco'],
        ['value' => 'franja_amarilla', 'label' => 'Franja Amarilla'],
        ['value' => 'amarillo', 'label' => 'Amarillo'],
        ['value' => 'franja_verde', 'label' => 'Franja Verde'],
        ['value' => 'verde', 'label' => 'Verde'],
        ['value' => 'franja_azul', 'label' => 'Franja Azul'],
        ['value' => 'azul', 'label' => 'Azul'],
        ['value' => 'franja_roja', 'label' => 'Franja Roja'],
        ['value' => 'rojo', 'label' => 'Rojo'],
        ['value' => 'franja_negra', 'label' => 'Franja Negra'],
        ['value' => 'negro', 'label' => 'Negro']
    ],
    'taeguk' => array_map(fn($i) => ['value' => "taeguk$i", 'label' => "Taeguk $i"], range(1, 8)),
    'palgwe' => array_map(fn($i) => ['value' => "palgwe$i", 'label' => "Palgwe $i"], range(1, 8)),
    'gibon' => [
        ['value' => 'posiciones', 'label' => 'Posiciones Básicas'],
        ['value' => 'patadas', 'label' => 'Patadas Básicas'],
        ['value' => 'defensas', 'label' => 'Defensas'],
        ['value' => 'golpes', 'label' => 'Golpes de Mano'],
        ['value' => 'combinaciones', 'label' => 'Combinaciones']
    ],
    'kukkiwon' => [
        ['value' => 'koryo', 'label' => 'Koryo'],
        ['value' => 'keumgang', 'label' => 'Keumgang'],
        ['value' => 'taeback', 'label' => 'Taeback'],
        ['value' => 'pyongwon', 'label' => 'Pyongwon'],
        ['value' => 'sipjin', 'label' => 'Sipjin'],
        ['value' => 'jitae', 'label' => 'Jitae'],
        ['value' => 'cheonkwon', 'label' => 'Cheonkwon'],
        ['value' => 'hansu', 'label' => 'Hansu'],
        ['value' => 'ilyeo', 'label' => 'Ilyeo']
    ],
    'pumses_superiores' => [
        ['value' => 'chonkwon', 'label' => 'Chon-Kwon'],
        ['value' => 'hansu', 'label' => 'Han-Su'],
        ['value' => 'ilyeo', 'label' => 'Il-Yeo']
    ],
    'galeria' => array_map(fn($i) => ['value' => "galeria$i", 'label' => "Imagen Galería $i"], range(1, 10))
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Contenido - MDKBoyacá</title>
    <!-- CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="tienda.css">
    <link rel="stylesheet" href="gestion_medios.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar_tienda">
        <a href="index.php" class="logo">
            <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" />
        </a>
        <ul class="nav-links">
            <li><a href="inicio_admin.php">Volver al Panel</a></li>
        </ul>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <br><br><br><br><br>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="main-container">
        <div class="gestion-box">
            <div class="gestion-header">
                <div class="gestion-title">
                    <i class="fa-solid fa-photo-film"></i>
                    Agregar Contenido Multimedia
                </div>

                <p class="gestion-subtitle">
                    Sube fotos o videos para el sitio web. Si el contenido ya existe, será reemplazado automáticamente.
                </p>
            </div>

            <!-- FORMULARIO DE SUBIDA -->
            <form id="uploadForm" enctype="multipart/form-data">

                <!-- LAYOUT DOS COLUMNAS -->
                <div class="gestion-grid">

                    <!-- COLUMNA IZQUIERDA: Formulario -->
                    <div class="gestion-col-left">
                        <!-- SELECCIÓN DE SECCIÓN (Generado dinámicamente) -->
                        <div class="form-group">
                            <label for="seccion">
                                <i class="fa-solid fa-folder"></i> Sección
                            </label>
                            <select name="seccion" id="seccion" required>
                                <option value="">-- Selecciona una sección --</option>
                                <?php foreach ($todas_secciones as $key => $label): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- SELECCIÓN DE SUBSECCIÓN (Dinámica) -->
                        <div class="form-group">
                            <label for="subseccion">
                                <i class="fa-solid fa-tag"></i> Subsección
                            </label>
                            <select name="subseccion" id="subseccion" required disabled>
                                <option value="">-- Primero selecciona una sección --</option>
                            </select>
                        </div>

                        <!-- DESCRIPCIÓN (Solo para cinturones) -->
                        <div class="form-group" id="descripcionGroup" style="display: none;">
                            <label for="descripcion">
                                <i class="fa-solid fa-align-left"></i> Descripción del Cinturón
                            </label>
                            <textarea name="descripcion" id="descripcion" rows="4"
                                placeholder="Ej: Pureza - Representa el inicio del camino marcial"></textarea>
                        </div>

                        <!-- BOTÓN DE SUBIDA (Solo visible en móvil dentro de columna izquierda) -->
                        <div class="btn-mobile-wrapper">
                            <button type="submit" class="btn-upload" id="btnUploadMobile">
                                <i class="fa-solid fa-upload"></i>
                                <span>Subir Contenido</span>
                            </button>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA: Drag & Drop + Preview -->
                    <div class="gestion-col-right">
                        <!-- ZONA DE DRAG & DROP -->
                        <div class="upload-zone" id="dropZone">
                            <input type="file" name="archivo" id="archivoInput" accept="image/*,video/*" required>
                            <div class="upload-icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="upload-text">Arrastra tu archivo aquí o <span>haz clic para seleccionar</span></p>
                            <p class="upload-hint">Formatos: JPG, PNG, GIF, WEBP, MP4, WEBM</p>
                            <p class="upload-hint">Máximo: 10MB imágenes, 50MB videos</p>
                        </div>

                        <!-- PREVIEW DE ARCHIVO -->
                        <div class="preview-container" id="previewContainer" style="display: none;">
                            <img id="imagePreview" src="" alt="Vista previa">
                            <video id="videoPreview" src="" controls style="display: none;"></video>
                            <button type="button" class="btn-remove" id="removeFile">
                                <i class="fa-solid fa-xmark"></i> Quitar archivo
                            </button>
                        </div>

                        <!-- BOTÓN DE SUBIDA (Visible en desktop) -->
                        <div class="btn-desktop-wrapper">
                            <button type="submit" class="btn-upload" id="btnUpload">
                                <i class="fa-solid fa-upload"></i>
                                <span>Subir Contenido</span>
                            </button>
                        </div>

                        <!-- ESTADO DE CARGA -->
                        <div class="upload-status" id="uploadStatus" style="display: none;">
                            <div class="spinner"></div>
                            <span>Subiendo archivo...</span>
                        </div>

                        <!-- MENSAJE DE RESULTADO -->
                        <div class="upload-result" id="uploadResult" style="display: none;"></div>
                    </div>

                </div>

            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- SECCIÓN: GESTIÓN Y ELIMINACIÓN DE CONTENIDO -->
    <!-- ============================================ -->
    <div class="main-container">
        <div class="gestion-box gallery-management-section">
            <div class="gestion-header">
                <h2 class="gestion-title">
                    <i class="fa-solid fa-images"></i>
                    Gestión y Eliminación de Contenido
                </h2>
                <p class="gestion-subtitle">
                    Visualiza y elimina el contenido multimedia existente
                </p>
            </div>

            <!-- SELECTOR DE SECCIÓN PARA FILTRAR -->
            <div class="form-group" style="max-width: 400px; margin-bottom: 30px;">
                <label for="gallerySeccion">
                    <i class="fa-solid fa-filter"></i> Filtrar por Sección
                </label>
                <select name="gallerySeccion" id="gallerySeccion">
                    <option value="">-- Selecciona una sección --</option>
                    <?php foreach ($todas_secciones as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CONTENEDOR DE LA GALERÍA -->
            <div id="content-gallery-display" class="content-gallery-grid">
                <div class="gallery-placeholder">
                    <i class="fa-solid fa-photo-film"></i>
                    <p>Selecciona una sección para ver el contenido</p>
                </div>
            </div>

            <!-- LOADING INDICATOR -->
            <div id="galleryLoading" class="gallery-loading" style="display: none;">
                <div class="spinner"></div>
                <span>Cargando contenido...</span>
            </div>

            <!-- CONTADOR DE ELEMENTOS -->
            <div id="galleryCount" class="gallery-count" style="display: none;">
                <span id="galleryCountText">0 elementos encontrados</span>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer-inicio-admin">
        <div class="footer-content">
            <div class="info-item">
                <i class="fab fa-instagram"></i>
                <a href="https://www.instagram.com/mdkboyaca/" target="_blank">@mdkboyacá</a>
            </div>
            <div class="info-item">
                <i class="fab fa-whatsapp"></i>
                <a href="https://wa.me/573124514555" target="_blank">+57 312 4514555</a>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Calle 21 No 11 - 66, Tunja</span>
            </div>
        </div>
        <div class="footer-credits">
            <p style="margin:0; font-weight:bold;">MDKBoyacá</p>
            <p style="margin:5px 0 0; font-size:13px;">Página Web by: Programador Juan Ramírez</p>
        </div>
    </footer>

    <script>
        // ============================================
        // CONFIGURACIÓN DE SUBSECCIONES (Generado desde PHP)
        // ============================================
        const subsecciones = <?= json_encode($subsecciones_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>;

        // ============================================
        // ELEMENTOS DEL DOM
        // ============================================
        const dropZone = document.getElementById('dropZone');
        const archivoInput = document.getElementById('archivoInput');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const videoPreview = document.getElementById('videoPreview');
        const removeFileBtn = document.getElementById('removeFile');
        const seccionSelect = document.getElementById('seccion');
        const subseccionSelect = document.getElementById('subseccion');
        const descripcionGroup = document.getElementById('descripcionGroup');
        const uploadForm = document.getElementById('uploadForm');
        const btnUpload = document.getElementById('btnUpload');
        const uploadStatus = document.getElementById('uploadStatus');
        const uploadResult = document.getElementById('uploadResult');

        // ============================================
        // DRAG & DROP
        // ============================================
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('highlight'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('highlight'), false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                archivoInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        // ============================================
        // SELECCIÓN DE ARCHIVO
        // ============================================
        archivoInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            // Validar tipo de archivo
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            if (!validTypes.includes(file.type)) {
                showResult('error', 'Tipo de archivo no válido. Use JPG, PNG, GIF, WEBP, MP4 o WEBM.');
                return;
            }

            // Mostrar preview
            const isVideo = file.type.startsWith('video/');
            const reader = new FileReader();

            reader.onload = (e) => {
                if (isVideo) {
                    videoPreview.src = e.target.result;
                    videoPreview.style.display = 'block';
                    imagePreview.style.display = 'none';
                } else {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    videoPreview.style.display = 'none';
                }
                previewContainer.style.display = 'block';
                dropZone.style.display = 'none';
            };

            reader.readAsDataURL(file);
        }

        // ============================================
        // QUITAR ARCHIVO
        // ============================================
        removeFileBtn.addEventListener('click', () => {
            archivoInput.value = '';
            imagePreview.src = '';
            videoPreview.src = '';
            previewContainer.style.display = 'none';
            dropZone.style.display = 'block';
            uploadResult.style.display = 'none';
        });

        // ============================================
        // CAMBIO DE SECCIÓN (Actualiza subsecciones)
        // ============================================
        seccionSelect.addEventListener('change', () => {
            const seccion = seccionSelect.value;

            // Limpiar subsecciones
            subseccionSelect.innerHTML = '<option value="">-- Selecciona una subsección --</option>';

            if (seccion && subsecciones[seccion]) {
                // Habilitar y llenar subsecciones
                subseccionSelect.disabled = false;
                subsecciones[seccion].forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.value;
                    option.textContent = sub.label;
                    subseccionSelect.appendChild(option);
                });

                // Mostrar/ocultar campo de descripción
                descripcionGroup.style.display = seccion === 'cinturones' ? 'block' : 'none';
            } else {
                subseccionSelect.disabled = true;
                descripcionGroup.style.display = 'none';
            }
        });

        // ============================================
        // ENVÍO DEL FORMULARIO
        // ============================================
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validar que hay archivo
            if (!archivoInput.files.length) {
                showResult('error', 'Por favor selecciona un archivo.');
                return;
            }

            // Mostrar estado de carga
            btnUpload.disabled = true;
            uploadStatus.style.display = 'flex';
            uploadResult.style.display = 'none';

            // Crear FormData
            const formData = new FormData(uploadForm);

            try {
                const response = await fetch('upload_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showResult('success', result.message);
                    // Limpiar formulario después de éxito
                    setTimeout(() => {
                        archivoInput.value = '';
                        imagePreview.src = '';
                        videoPreview.src = '';
                        previewContainer.style.display = 'none';
                        dropZone.style.display = 'block';
                        seccionSelect.value = '';
                        subseccionSelect.innerHTML = '<option value="">-- Primero selecciona una sección --</option>';
                        subseccionSelect.disabled = true;
                        descripcionGroup.style.display = 'none';
                        document.getElementById('descripcion').value = '';
                    }, 2000);
                } else {
                    showResult('error', result.message);
                }
            } catch (error) {
                showResult('error', 'Error de conexión. Intenta de nuevo.');
                console.error('Error:', error);
            } finally {
                btnUpload.disabled = false;
                uploadStatus.style.display = 'none';
            }
        });

        // ============================================
        // MOSTRAR RESULTADO
        // ============================================
        function showResult(type, message) {
            uploadResult.className = 'upload-result ' + type;
            uploadResult.innerHTML = `
        <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i>
        <span>${message}</span>
    `;
            uploadResult.style.display = 'flex';
        }

        // ============================================
        // HAMBURGER MENU
        // ============================================
        const hamburger = document.querySelector('.navbar_tienda .hamburger');
        const navLinks = document.querySelector('.navbar_tienda .nav-links');

        if (hamburger && navLinks) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('open');
            });
        }

        // ============================================
        // GESTIÓN DE GALERÍA - FETCH Y DELETE
        // ============================================
        const gallerySeccionSelect = document.getElementById('gallerySeccion');
        const galleryDisplay = document.getElementById('content-gallery-display');
        const galleryLoading = document.getElementById('galleryLoading');
        const galleryCount = document.getElementById('galleryCount');
        const galleryCountText = document.getElementById('galleryCountText');

        // Cargar contenido al cambiar sección
        if (gallerySeccionSelect) {
            gallerySeccionSelect.addEventListener('change', async function () {
                const seccion = this.value;

                if (!seccion) {
                    galleryDisplay.innerHTML = `
                        <div class="gallery-placeholder">
                            <i class="fa-solid fa-photo-film"></i>
                            <p>Selecciona una sección para ver el contenido</p>
                        </div>
                    `;
                    galleryCount.style.display = 'none';
                    return;
                }

                // Mostrar loading
                galleryLoading.style.display = 'flex';
                galleryDisplay.innerHTML = '';
                galleryCount.style.display = 'none';

                try {
                    const formData = new FormData();
                    formData.append('type', 'fetch_content_gallery');
                    formData.append('seccion', seccion);

                    const response = await fetch('ajax_handler.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (result.count === 0) {
                            galleryDisplay.innerHTML = `
                                <div class="gallery-placeholder">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <p>No hay contenido en esta sección</p>
                                </div>
                            `;
                        } else {
                            renderGallery(result.data);
                            galleryCountText.textContent = `${result.count} elemento${result.count !== 1 ? 's' : ''} encontrado${result.count !== 1 ? 's' : ''}`;
                            galleryCount.style.display = 'block';
                        }
                    } else {
                        galleryDisplay.innerHTML = `
                            <div class="gallery-placeholder error">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <p>${result.message || 'Error al cargar el contenido'}</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    galleryDisplay.innerHTML = `
                        <div class="gallery-placeholder error">
                            <i class="fa-solid fa-wifi"></i>
                            <p>Error de conexión</p>
                        </div>
                    `;
                } finally {
                    galleryLoading.style.display = 'none';
                }
            });
        }

        // Renderizar galería
        function renderGallery(items) {
            galleryDisplay.innerHTML = '';

            items.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'gallery-item';
                itemEl.dataset.id = item.id;

                let preview = '';
                if (item.tipo === 'imagen') {
                    preview = `<img src="${item.ruta}" alt="${item.subseccion}" loading="lazy">`;
                } else if (item.tipo === 'video') {
                    preview = `
                        <video muted preload="metadata">
                            <source src="${item.ruta}" type="video/mp4">
                        </video>
                        <div class="video-indicator"><i class="fa-solid fa-play"></i></div>
                    `;
                }

                itemEl.innerHTML = `
                    <div class="gallery-item-preview">
                        ${preview}
                    </div>
                    <div class="gallery-item-info">
                        <span class="gallery-item-subseccion">${item.subseccion}</span>
                        ${item.descripcion ? `<small class="gallery-item-desc">${item.descripcion}</small>` : ''}
                    </div>
                    <button class="gallery-delete-btn" data-id="${item.id}" title="Eliminar">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;

                galleryDisplay.appendChild(itemEl);
            });

            // Añadir event listeners a los botones de eliminar
            document.querySelectorAll('.gallery-delete-btn').forEach(btn => {
                btn.addEventListener('click', handleDeleteContent);
            });
        }

        // Manejar eliminación de contenido
        async function handleDeleteContent(e) {
            e.preventDefault();
            const btn = e.currentTarget;
            const id = btn.dataset.id;
            const item = btn.closest('.gallery-item');

            if (!confirm('¿Estás seguro de que deseas eliminar este contenido?\n\nEsta acción no se puede deshacer.')) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const formData = new FormData();
                formData.append('type', 'delete_content');
                formData.append('id', id);

                const response = await fetch('ajax_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Animación de eliminación
                    item.style.transform = 'scale(0.8)';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        // Actualizar contador
                        const remaining = document.querySelectorAll('.gallery-item').length;
                        if (remaining === 0) {
                            galleryDisplay.innerHTML = `
                                <div class="gallery-placeholder">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <p>No hay contenido en esta sección</p>
                                </div>
                            `;
                            galleryCount.style.display = 'none';
                        } else {
                            galleryCountText.textContent = `${remaining} elemento${remaining !== 1 ? 's' : ''} encontrado${remaining !== 1 ? 's' : ''}`;
                        }
                    }, 300);
                } else {
                    alert('Error: ' + (result.message || 'No se pudo eliminar'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-trash"></i>';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al eliminar');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-trash"></i>';
            }
        }
    </script>

</body>

</html>