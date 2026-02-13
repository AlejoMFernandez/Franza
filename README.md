# FRANZA - Sistema de Gestión de Obras

Sistema de gestión de obras civiles e industriales desarrollado en PHP nativo con arquitectura MVC.

## Requisitos

- PHP 8.0 o superior
- MySQL/MariaDB 5.7 o superior
- Apache con mod_rewrite habilitado
- XAMPP recomendado para desarrollo local

## Instalación

### 1. Base de Datos

```sql
-- Crear la base de datos
CREATE DATABASE franza;

-- Importar el esquema
-- Desde phpMyAdmin o desde consola:
mysql -u root -p franza < db/franza.sql
```

### 2. Configuración

Editar las credenciales de base de datos en:
`sitio/classes/BaseDeDatos/DBConexion.php`

```php
public const DB_HOST = '127.0.0.1';
public const DB_PORT = '3306';
public const DB_USER = 'root';
public const DB_PASS = '';
public const DB_SCHEMA = 'franza';
```

### 3. Permisos

Asegurar que la carpeta de imágenes tenga permisos de escritura:

```bash
chmod -R 755 sitio/img/
```

### 4. Dependencias Node (opcional)

```bash
npm install
```

## Estructura del Proyecto

```
sitio/
├── bootstrap/          # Inicialización y autoload
│   ├── init.php
│   └── autoload.php
├── classes/            # Clases del sistema
│   ├── Autenticacion/
│   ├── BaseDeDatos/
│   └── Modelos/
├── admin/              # Panel de administración
│   ├── acciones/
│   └── views/
├── views/              # Vistas del sitio público
├── img/                # Imágenes de obras
├── js/                 # JavaScript
└── styles/             # CSS
```

## Uso

### Acceso al Sitio

- **Frontend**: `http://localhost/Franza/sitio/`
- **Admin Panel**: `http://localhost/Franza/sitio/admin/`

### Crear Usuario Administrador

1. Registrarse desde el panel admin
2. Modificar manualmente el `rol_fk` en la base de datos:

```sql
UPDATE usuarios SET rol_fk = 1 WHERE email = 'tu@email.com';
```

## Características de Seguridad

- Preparación de consultas SQL (PDO)
- Validación de inputs
- Sanitización de outputs (htmlspecialchars)
- Validación de tipos de archivo
- Límite de tamaño de archivo (5MB)
- Hashing de contraseñas (password_hash)
- Configuración segura de sesiones
- Headers de seguridad (.htaccess)
- Validación de emails
- Control de acceso basado en roles
- **Protección CSRF** en todos los formularios (tokens single-use)
- **URLs amigables** con mod_rewrite

## URLs Amigables

El sitio soporta rutas limpias gracias a `.htaccess`:

| URL Amigable | Equivalente |
|---|---|
| `/Franza/sitio/inicio` | `index.php?seccion=inicio` |
| `/Franza/sitio/obras-civiles` | `index.php?seccion=obrasciviles` |
| `/Franza/sitio/obras-industriales` | `index.php?seccion=obrasindustriales` |
| `/Franza/sitio/obra/25` | `index.php?seccion=obra-ver&id=25` |
| `/Franza/sitio/iniciar-sesion` | `index.php?seccion=iniciar-sesion` |
| `/Franza/sitio/registrarse` | `index.php?seccion=registrarse` |

> Las URLs con query string (`?seccion=...`) siguen funcionando normalmente.

## Roles

- **ROL_ADMIN** (ID: 1): Acceso completo al panel de administración
- **ROL_USUARIO** (ID: 2): Acceso limitado

## Mantenimiento

### Logs de Errores

Los errores se registran en el log de PHP. Para verlos en XAMPP:
`C:\xampp\apache\logs\error.log`

### Backup de Base de Datos

```bash
mysqldump -u root -p franza > backup_franza_$(date +%Y%m%d).sql
```

## Contribución

Este es un proyecto privado. Para cambios:
1. Crear una rama feature
2. Hacer commits descriptivos
3. Solicitar revisión antes de merge

## Licencia

Todos los derechos reservados © FRANZA Construcciones
