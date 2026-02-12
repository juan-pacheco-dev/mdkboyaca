# Academia de Taekwondo MDKBoyacá – Sistema de Gestión Administrativa

Sistema web integral desarrollado en PHP y MySQL para la gestión administrativa y académica de una escuela de Taekwondo. El sistema permite el control total de la vida deportiva de los estudiantes, desde su ingreso hasta su progresión de grados, junto con la gestión financiera y de contenidos institucionales.

## Funcionalidades Principales

*   **Gestión de Estudiantes:** Operaciones CRUD para el registro y administración de deportistas.
*   **Historial de Progresión:** Seguimiento detallado de cambios de cinturón y fechas de obtención.
*   **Control de Pagos:** Monitoreo automatizado de mensualidades y anualidades con alertas de deudores.
*   **Calendario de Actividades:** Visualización y gestión de eventos, torneos y entrenamientos programados.
*   **Gestión de Contenidos (CMS):** Panel para actualizar sliders principales, galerías de fotos y videos del sitio.
*   **Módulo de Feedback:** Sistema de recepción y gestión de sugerencias, quejas y felicitaciones de la comunidad.
*   **Programa Estudiante Atleta:** Seguimiento especial para la formación integral de los alumnos.
*   **Tienda Virtual:** Catálogo de uniformes e implementos deportivos para la academia.

## Tecnologías Utilizadas

*   **Backend:** PHP (Procedimental)
*   **Base de Datos:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript
*   **Librerías Visuales:** AOS (Animations On Scroll), Swiper (Slider), Font Awesome
*   **Entorno de Desarrollo:** XAMPP / Laragon

## Requisitos

*   PHP 8.x
*   MySQL o MariaDB
*   Servidor Apache (XAMPP recomendado)
*   Navegador web moderno

## Instalación y Configuración

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/juan-pacheco-dev/mdk-boyaca.git
    ```
2.  **Mover el proyecto:** Copia la carpeta del proyecto en `htdocs` (XAMPP) o `www` (WampServer).
3.  **Importar Base de Datos:**
    *   Crea una base de datos llamada `mdkboyac_mdk_boyaca`.
    *   Importa el archivo SQL correspondiente (solicitar exportación actual al administrador).
4.  **Configurar conexión:**
    Ajusta las credenciales en el archivo:
    `php/config.php`
5.  **Iniciar servicios:** Activa Apache y MySQL desde tu panel de control local.
6.  **Acceder al sistema:**
    `http://localhost/mdkboyaca/index.php`

## Accesos de Prueba

> [!NOTE]
> Las credenciales de acceso deben ser configuradas inicialmente en la base de datos por el administrador del sistema.

*   **Administrador:** Acceso total al panel de control (`admin.php`).
*   **Estudiantes:** Acceso a perfil personal y materiales de aprendizaje (`student.php`).

## Estructura del Proyecto

```text
mdkboyaca/
├── admin.php           # Panel central de administración
├── index.php           # Página principal pública
├── login.php           # Autenticación de usuarios
├── student.php         # Panel para estudiantes
├── php/                # Lógica del servidor y configuración
│   ├── config.php      # Conexión a la BD
│   └── auth.php        # Manejo de sesiones
├── js/                 # Scripts de frontend (Calendario, Ruleta, etc.)
├── css/                # Estilos personalizados (Luxury Design)
├── img/                # Recursos gráficos y multimedia
├── uploads/            # Archivos subidos (Fotos de perfil, Contenidos)
└── temp/               # Plantillas reutilizables (Header, Footer)
```

## Notas

*   Se priorizaron las buenas prácticas en el manejo de sesiones y seguridad de contraseñas (BCRYPT).

## Autor

**Juan Ramírez**
Programador y Desarrollador del Proyecto para MDK Boyacá.
