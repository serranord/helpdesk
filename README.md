# HelpDesk DR 🎫

Sistema de soporte TI construido en Laravel 12 + Blade.

## Instalación

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Crear base de datos y datos iniciales
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

# 4. Assets (opcional para desarrollo)
npm install && npm run build

# 5. Levantar servidor
php artisan serve --port=8001
```

## Acceso inicial

| Usuario | Correo | Contraseña | Rol |
|---|---|---|---|
| Administrador TI | admin@helpdesk.local | admin123 | Administrador |
| Técnico | tecnico@helpdesk.local | tecnico123 | Técnico |
| Demo | usuario@helpdesk.local | usuario123 | Solicitante |

## Roles

- **Administrador**: acceso total, gestiona usuarios y categorías
- **Técnico**: atiende y gestiona tickets, crea tickets internos
- **Solicitante**: crea tickets y sigue su estado

## Para MySQL

Actualiza en `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_dr
DB_USERNAME=root
DB_PASSWORD=tu_password
```

## Estructura en servidor Linux

```
/var/www/
├── TrackDR/       ← Control de activos (puerto 8000)
└── HelpDeskDR/    ← Sistema de soporte TI (puerto 8001)
```
