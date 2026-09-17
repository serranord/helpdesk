# Conectar una IA a HelpDesk DR

El servidor utiliza el paquete oficial `laravel/mcp` y expone seis herramientas:

| Herramienta | Función |
| --- | --- |
| `consultar_ticket` | Estado, SLA y última actualización pública por número. |
| `buscar_tickets` | Búsqueda por título/número, estado y prioridad; páginas de hasta 50 resultados. |
| `tickets_por_usuario` | Hasta cinco tickets recientes no cerrados por correo. |
| `estadisticas` | Nuevos, en proceso, pendientes, críticos, resueltos hoy y SLA vencidos. |
| `listar_categorias` | Categorías activas con sus identificadores. |
| `crear_ticket` | Crear para un solicitante registrado y activo, con categoría activa, SLA, historial y notificaciones habituales. |

La conexión permite consultar todos los tickets. Con OAuth, solo un **administrador o técnico activo** puede autorizarla y sus acciones quedan atribuidas a su cuenta. El token estático opcional conserva acceso de integración sin usuario individual. No expone contraseñas, consultas SQL, comandos del servidor ni comentarios internos.

## Instalación

```sh
composer install
```

OAuth utiliza Laravel Passport: requiere sus cuatro tablas y un par de claves RSA, según el despliegue indicado abajo. La creación conserva `origen=usuario`, porque actúa en nombre de un solicitante, y registra explícitamente **MCP** en el historial del ticket y en el registro de actividad. No crea ni reactiva cuentas. La API existente de Copilot conserva su comportamiento.

## Conexión recomendada: OAuth con Microsoft 365

En tu aplicación de IA agrega un servidor MCP remoto con estos datos:

- **URL:** `https://helpdesk.amcham.org.do/mcp/helpdesk`
- **Transporte:** Streamable HTTP.
- **Autenticación:** OAuth, con descubrimiento y registro dinámico de cliente (DCR).
- **Client ID / Secret:** vacíos cuando el cliente admite DCR; el servidor registra clientes públicos con PKCE S256.

La aplicación abre el HelpDesk, te lleva al inicio de sesión de Microsoft 365 y muestra una pantalla para autorizar la conexión. Después regresas al cliente de IA. No necesitas copiar `MCP_TOKEN` ni mantener una sesión del navegador abierta. Los tokens de acceso duran una hora; los de renovación, 30 días, y se rotan al renovarlos. La aplicación puede pedir que vuelvas a conectar cuando caduque la renovación.

Se reutilizan `AZURE_TENANT_ID`, `AZURE_CLIENT_ID`, `AZURE_CLIENT_SECRET` y la URL de regreso existente `AZURE_REDIRECT_URI`. Si el SSO ya funciona, no se requiere registrar cada aplicación de IA en Entra: **HelpDesk emite los tokens OAuth para MCP; Microsoft solo identifica a la persona**. El cliente debe admitir OAuth con DCR y PKCE, o disponer de un cliente registrado manualmente mediante `php artisan passport:client --public` con su URL de regreso exacta.

Solo administradores y técnicos activos tienen acceso, y se vuelven a verificar rol y estado en cada llamada MCP. Una cuenta solicitante no obtiene acceso global. El SSO ahora valida `state`, regenera la sesión y rechaza cuentas inactivas o eliminadas en lugar de reactivarlas.

Para revocar acceso, abre **Mi perfil → Mis conexiones de IA** o `https://helpdesk.amcham.org.do/mcp/conexiones`. La revocación invalida los tokens de acceso y renovación de esa aplicación para tu cuenta. No revoca un token estático compartido; ese se rota o elimina en `.env`.

Metadatos públicos (no exponen tickets):

- `/.well-known/oauth-protected-resource/mcp/helpdesk`
- `/.well-known/oauth-authorization-server`

El registro `/oauth/register` admite callbacks HTTPS sin credenciales ni fragmentos. `MCP_REDIRECT_DOMAINS=*` permite aplicaciones de distintos proveedores, siempre sujetas a consentimiento y PKCE. Puedes restringirlo a una lista de orígenes completos separados por coma. No se implementa CIMD; selecciona DCR si tu cliente ofrece ambas opciones. La disponibilidad de conexiones personalizadas depende de la aplicación y de tu cuenta: OAuth no añade esa función a chats que no admiten MCP.

## Cliente local (stdio)

Un cliente MCP que pueda ejecutar programas locales puede iniciar:

```sh
php C:/laragon/www/helpdesk/artisan mcp:start helpdesk
```

Ejemplo de configuración para clientes que utilizan `mcpServers` (ajusta las rutas a tu equipo):

```json
{
  "mcpServers": {
    "helpdesk": {
      "command": "C:/xampp/php/php.exe",
      "args": ["C:/laragon/www/helpdesk/artisan", "mcp:start", "helpdesk"]
    }
  }
}
```

El cliente debe poder acceder a la carpeta y a la base de datos configurada por Laravel. La conexión local hereda ese acceso; no utiliza el token HTTP. No ejecutes el comando esperando una interfaz: se comunica mediante mensajes MCP por entrada/salida estándar.

## Alternativa: token estático

1. Genera un secreto: `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.
2. Guarda ese valor en `MCP_TOKEN` en el `.env` del servidor. No lo publiques ni lo guardes en Git.
3. Si utilizas configuración cacheada, ejecuta `php artisan config:cache` después del cambio.
4. Configura tu cliente con la URL `https://TU-DOMINIO/mcp/helpdesk` y la cabecera `Authorization: Bearer TU_MCP_TOKEN`.

En desarrollo local puedes usar `http://localhost:8001/mcp/helpdesk` si ejecutas `php artisan serve --port=8001`. Un servicio de IA externo necesita una URL HTTPS que pueda alcanzar; `localhost` de este equipo no es accesible desde ese servicio.

El endpoint exige un token OAuth válido o el `MCP_TOKEN` estático opcional. Deja `MCP_TOKEN` vacío si solo usarás OAuth. El token estático es exclusivo de MCP, distinto de `API_TOKEN`. No acepta secretos en la URL. Hay un límite de 60 peticiones por minuto por IP. Si el cliente envía `Origin`, autoriza su origen exacto en `MCP_ALLOWED_ORIGINS` (separados por coma). Esto valida el origen; no habilita por sí solo CORS para aplicaciones de navegador.

Esta alternativa admite clientes que soportan cabeceras Bearer configurables y no pasa por Microsoft 365.

## Despliegue en Ubuntu mediante Git

Incluye en tu commit los archivos nuevos y los cambios en `composer.json` y `composer.lock`. En el servidor, desde la carpeta del proyecto:

```sh
git pull
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
```

Verifica que PHP CLI y PHP-FPM/Apache tengan habilitada la extensión **Sodium** (`php -m`). Composer comprobará los requisitos. Configura en el `.env` **de Ubuntu**:

```env
APP_URL=https://helpdesk.amcham.org.do
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
MCP_TOKEN=
MCP_REDIRECT_DOMAINS=*
```

Conserva la configuración Microsoft existente. El `.env` no viaja por Git. Ejecuta una sola vez las nuevas migraciones y genera las claves (sin `--force` en `passport:keys`, para no sustituir claves existentes):

```sh
php artisan migrate --force
php artisan passport:keys
```

Las claves `storage/oauth-private.key` y `storage/oauth-public.key` están excluidas de Git; consérvalas entre despliegues y asegúrate de que el usuario PHP pueda leerlas. Nunca publiques la clave privada ni cambies `APP_KEY` como parte de esta instalación. Después:

```sh
php artisan config:cache
php artisan route:cache
php artisan route:list --path=mcp
php artisan route:list --path=oauth
```

No hace falta abrir un puerto nuevo ni mantener otro proceso MCP: el endpoint HTTP funciona dentro del Laravel que ya sirve Apache/Nginx y PHP. Si hay un proxy inverso, debe conservar `Authorization`, el host y el esquema HTTPS. Configura los proxies de confianza de Laravel si termina TLS delante de PHP. Permite las rutas `/mcp/helpdesk`, `/oauth/*` y `/.well-known/*` sin una capa externa que obligue a iniciar sesión; Laravel aplica la autenticación correspondiente.

Comprueba el descubrimiento sin credenciales:

```sh
curl https://helpdesk.amcham.org.do/.well-known/oauth-authorization-server
curl https://helpdesk.amcham.org.do/.well-known/oauth-protected-resource/mcp/helpdesk
```

Las URLs devueltas deben empezar por `https://helpdesk.amcham.org.do`. Añade luego el conector en tu cliente de IA y completa Microsoft 365 y el consentimiento. Esta prueba real requiere tu cuenta Microsoft y acceso al servidor desplegado; las pruebas automatizadas simulan Microsoft.

Solo si configuraste la alternativa de token estático, puedes comprobar también el protocolo desde Ubuntu (introduce el token sin guardarlo en el historial de Bash):

```bash
read -rsp 'Token MCP: ' HELPDESK_MCP_TOKEN; echo
curl --request POST 'https://TU-DOMINIO/mcp/helpdesk' \
  --header "Authorization: Bearer ${HELPDESK_MCP_TOKEN}" \
  --header 'Content-Type: application/json' \
  --header 'Accept: application/json, text/event-stream' \
  --data '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-11-25","capabilities":{},"clientInfo":{"name":"deployment-check","version":"1.0"}}}'
unset HELPDESK_MCP_TOKEN
```

La respuesta debe incluir `serverInfo.name` con el valor `HelpDesk DR`. Esta prueba no consulta ni modifica tickets. Completa la conexión en tu cliente de IA con la URL y el token; el formato exacto depende del cliente.

## Ejemplos de uso

- «Consulta el ticket TKT-00025».
- «Busca tickets críticos en proceso».
- «Muéstrame los tickets de persona@empresa.com».
- «Dame las estadísticas del helpdesk».
- «Crea un ticket por una impresora sin conexión para persona@empresa.com» (la IA obtiene antes la categoría).

Para crear, se requieren `titulo`, `descripcion`, `solicitante_email` y `categoria_id`; `prioridad` es opcional y vale `media` por defecto. La creación puede enviar correo y notificar a Teams según la configuración existente. No es idempotente: ante una desconexión, busca primero si se creó el ticket antes de repetir la operación. La numeración comparte el mecanismo existente del HelpDesk; una creación simultánea desde otros canales puede producir un conflicto que se debe comprobar antes de reintentar.

## Verificación

```sh
php artisan route:list --path=mcp
php vendor/bin/phpunit --filter=Mcp
```

Las pruebas usan SQLite en memoria, claves RSA temporales en memoria y notificaciones simuladas; no modifican los tickets reales. Cubren también registro dinámico, PKCE, emisión real de tokens, renovación, revocación, permisos y regreso desde Microsoft. Cargan las migraciones base necesarias porque el historial del proyecto contiene cambios de esquema específicos de MySQL. En este entorno Windows Sodium se carga para las pruebas con `php -d extension=sodium vendor/bin/phpunit --filter=Mcp`; en Ubuntu debe habilitarse también para el PHP que atiende la web.
