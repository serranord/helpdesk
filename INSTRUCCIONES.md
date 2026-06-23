# ¿Cómo instalar HelpDesk DR?

## Paso 1 — Crear proyecto Laravel 12 limpio

```bash
cd C:\laragon\www
composer create-project laravel/laravel:^12.0 helpdesk
```

## Paso 2 — Copiar los archivos de este ZIP

Copia TODAS las carpetas de este ZIP dentro de `C:\laragon\www\helpdesk\`
Cuando Windows pregunte si deseas reemplazar, di que SÍ a todo.

## Paso 3 — Configurar

```bash
cd C:\laragon\www\helpdesk
copy .env.example .env
php artisan key:generate
```

## Paso 4 — Base de datos

```bash
php artisan migrate --force
php artisan db:seed --force
```

## Paso 5 — Levantar

```bash
php artisan serve --port=8001
```

Abre en el navegador: http://127.0.0.1:8001

## Usuarios iniciales

| Correo                   | Contraseña  | Rol           |
|--------------------------|-------------|---------------|
| admin@helpdesk.local     | admin123    | Administrador |
| tecnico@helpdesk.local   | tecnico123  | Técnico       |
| usuario@helpdesk.local   | usuario123  | Solicitante   |
