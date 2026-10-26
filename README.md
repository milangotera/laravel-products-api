# Products API

API REST desarrollada como prueba técnica de backend con Laravel. Permite administrar productos, sus costos y precios en diferentes divisas. Incluye una bienvenida en `/`, documentación interactiva Swagger UI en `/docs` y la especificación OpenAPI en `/openapi.yaml`.

## Funcionalidades

- Crear, listar, consultar, actualizar y eliminar productos.
- Registrar y consultar precios adicionales por moneda.
- Persistencia con Eloquent, relaciones y claves foráneas.
- Validación mediante Form Requests y respuestas con API Resources.
- Autenticación Bearer con Laravel Sanctum y límite de 60 solicitudes por minuto.
- Pruebas funcionales con PHPUnit y SQLite en memoria.

## Requisitos

- PHP 8.3 o superior y Composer 2. Para ejecutar PHPUnit 12: PHP 8.3 o superior.
- Extensiones requeridas por Composer y el controlador PDO de la base elegida (`pdo_mysql` o `pdo_sqlite`).
- MySQL para la configuración de ejemplo; SQLite es una alternativa para evaluación local.
- Git.

La versión exacta de las dependencias está fijada en `composer.lock`. Compruébalas con `composer check-platform-reqs`.

Node.js no es necesario para servir la bienvenida ni Swagger: su CSS y JavaScript están versionados en `public/vendor/swagger-ui`. Solo se necesita npm para actualizar esos archivos o trabajar con Vite.

## Instalación local

```bash
git clone https://github.com/milangotera/laravel-products-api.git
cd laravel-products-api
composer install
cp .env.example .env
php artisan key:generate --no-interaction
```

El comando `cp` es para una instalación nueva. No sobrescribas un `.env` ya configurado. Si el repositorio es privado, necesitarás acceso en GitHub.

### Opción A: MySQL

Crea una base vacía y un usuario con permisos sobre ella. Edita `.env` con tus credenciales:

```dotenv
APP_NAME="Products API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_products_api
DB_USERNAME=tu_usuario
DB_PASSWORD="tu_clave"
```

### Opción B: SQLite para evaluación local

```bash
touch database/database.sqlite
```

En `.env` establece `DB_CONNECTION=sqlite` y elimina la línea `DB_DATABASE` para que Laravel utilice `database/database.sqlite`. Alternativamente, configura `DB_DATABASE` con la ruta absoluta al archivo. No dejes como valor el nombre de la base MySQL.

### Migraciones y datos iniciales

```bash
php artisan config:clear --no-interaction
php artisan migrate --seed --no-interaction
php artisan serve --host=127.0.0.1 --port=8001
```

En otra terminal puedes ejecutar los demás comandos. Direcciones locales:

- Bienvenida: http://127.0.0.1:8001/
- Swagger: http://127.0.0.1:8001/docs
- API: http://127.0.0.1:8001/api
- OpenAPI: http://127.0.0.1:8001/openapi.yaml

El seeder crea USD, EUR y VES. Sus tasas son **ilustrativas**, no cotizaciones vigentes. Repetir el seeder no duplica esas monedas, pero sí restablece sus valores de ejemplo.

Consulta los identificadores reales antes de probar:

```bash
php artisan tinker --execute 'dump(\App\Models\Currency::query()->get(["id", "name", "symbol", "exchange_rate"])->toArray());'
```

## Crear el token de prueba

```bash
php artisan api:token evaluador@example.com
```

El comando crea el usuario si no existe y emite un token con duración de **24 horas**. No hay un registro o login público: el acceso se aprovisiona desde la consola. No se necesita una API key externa, WhatsApp ni servicios adicionales.

Guarda el token que se muestra: su valor completo se entrega al crearlo. Cada ejecución genera un token adicional; no revoca los anteriores.

### Usarlo en Swagger

1. Abre `/docs`.
2. Pulsa **Authorize**.
3. Pega únicamente el token, sin añadir `Bearer`.
4. Selecciona un endpoint y pulsa **Try it out**.
5. Completa el cuerpo y ejecuta la solicitud.

Swagger agrega el encabezado `Authorization: Bearer <token>`. La autorización no se conserva al recargar la página. La interfaz y la especificación son públicas; los endpoints de datos requieren autenticación.

### Usarlo desde la terminal

Define estas variables en tu sesión local, sin guardarlas en archivos del repositorio:

```bash
API_BASE_URL='http://127.0.0.1:8001/api'
API_TOKEN='PEGA_AQUI_EL_TOKEN_GENERADO'

curl -i "$API_BASE_URL/products" \
  -H 'Accept: application/json' \
  -H "Authorization: Bearer $API_TOKEN"
```

Para crear un producto, sustituye `currency_id` por un ID existente:

```bash
curl -i -X POST "$API_BASE_URL/products" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $API_TOKEN" \
  --data '{"name":"Teclado","description":"Teclado para computadora","price":"25.50","currency_id":1,"tax_cost":"2.00","manufacturing_cost":"10.00"}'
```

Usa el `data.id` devuelto al consultar, modificar, eliminar o registrar otros precios. El detalle completo de los cuerpos y respuestas está en Swagger.

### Revocar los tokens de un usuario

Este comando revoca **todos** los tokens del correo indicado:

```bash
php artisan tinker --execute '\App\Models\User::where("email", "evaluador@example.com")->firstOrFail()->tokens()->delete();'
```

No publiques tokens ni credenciales en Git, capturas o ejemplos. El catálogo es compartido entre todos los usuarios autenticados: un token da acceso a todas sus operaciones. No se implementan roles ni separación de productos por propietario.

## Endpoints

Todos requieren Bearer token y usan el prefijo `/api`.

| Método | Ruta | Resultado |
| --- | --- | --- |
| GET | `/api/products` | Productos paginados, 15 por página |
| POST | `/api/products` | Crear producto |
| GET | `/api/products/{product}` | Producto, moneda base y precios adicionales |
| PUT / PATCH | `/api/products/{product}` | Actualizar campos del producto |
| DELETE | `/api/products/{product}` | Eliminar producto y sus precios |
| GET | `/api/products/{product}/prices` | Listar precios adicionales |
| POST | `/api/products/{product}/prices` | Registrar precio en otra moneda |

El contrato OpenAPI documenta PUT como método de actualización; Laravel también registra PATCH. La paginación utiliza `?page=2`.

Respuestas: `200` consulta/actualización; `201` creación; `204` eliminación sin cuerpo; `401` token ausente, inválido o vencido; `404` producto inexistente; `422` validación; `429` límite de solicitudes. Las respuestas de datos y errores son JSON; el 204 no contiene cuerpo.

## Modelo y reglas de negocio

- `currencies`: nombre, símbolo y tasa de cambio.
- `products`: nombre, descripción, precio base, moneda base, costo de impuestos y costo de fabricación.
- `product_prices`: producto, moneda y precio adicional. La pareja producto/moneda es única.
- Los importes se almacenan en `decimal(18,2)` y se devuelven como cadenas decimales. Se recomienda enviarlos como cadenas para conservar precisión. Se rechazan negativos, más de dos decimales, notación científica y más de 16 dígitos enteros.
- Los costos del producto están expresados en su moneda base. No se suman automáticamente al precio: son campos independientes.
- No se permite registrar un precio adicional en la moneda base ni repetir una divisa para un producto.
- Cambiar la moneda base requiere reenviar `price`, `tax_cost` y `manufacturing_cost`; se rechaza si la nueva moneda ya tiene un precio adicional.
- Los precios adicionales se registran manualmente. No hay conversión automática ni actualización de tasas desde servicios externos. Las tasas de ejemplo toman USD como referencia.
- Eliminar un producto elimina sus precios en cascada. Las divisas referenciadas no se pueden eliminar por las claves foráneas.
- Las operaciones de actualización de moneda y alta de precios usan transacciones y bloqueo del producto. MySQL/PostgreSQL ofrecen bloqueo por fila; SQLite no ofrece la misma semántica de concurrencia.
- No se incluyen CRUD de divisas ni edición/eliminación individual de precios adicionales: no forman parte de los endpoints pedidos.

## Organización

```text
app/Http/Controllers/Api/   Endpoints de productos y precios
app/Http/Requests/          Validación de entradas
app/Http/Resources/         Representación JSON
app/Models/                Modelos y relaciones Eloquent
app/Support/MoneyRules.php  Reglas compartidas de importes
app/Console/Commands/      Emisión de tokens
public/openapi.yaml        Contrato OpenAPI
resources/views/           Bienvenida y Swagger UI
tests/Feature/             Pruebas funcionales
```

## Pruebas y formato

```bash
php artisan test --compact
vendor/bin/pint --dirty
git diff --check
```

`phpunit.xml` configura SQLite en memoria para las pruebas. No apuntes las pruebas a una base de producción. La suite actual cubre CRUD, validación de importes, acceso sin autenticación, precios duplicados, moneda base y eliminación en cascada. No demuestra concurrencia real de MySQL ni constituye una prueba de carga. La comprobación con un Bearer token real puede hacerse desde Swagger.

## Despliegue en producción

### Primera instalación

1. Configura un dominio con HTTPS, PHP-FPM y una base MySQL vacía con un usuario propio.
2. Clona el repositorio en la carpeta elegida y entra en ella.
3. Instala dependencias:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
cp .env.example .env
```

4. Edita `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio.com` y las credenciales reales de base de datos. No uses el usuario root para la aplicación.
5. Inicializa la aplicación:

```bash
php artisan key:generate --no-interaction
php artisan config:clear --no-interaction
php artisan migrate --force --no-interaction
php artisan db:seed --class=CurrencySeeder --force --no-interaction
php artisan api:token evaluador@example.com
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
```

6. Configura la raíz pública del servidor como `/ruta/al/proyecto/public`, nunca como la raíz del repositorio. El usuario de PHP debe poder escribir en `storage` y `bootstrap/cache`; no uses permisos 777.
7. En Nginx, la regla de enrutamiento debe enviar las rutas no estáticas a Laravel:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Este fragmento no reemplaza el virtual host completo: también necesita la configuración PHP-FPM y TLS de tu servidor.

No utilices `php artisan serve` en producción. La bienvenida y Swagger no requieren `npm run build`. No se necesitan workers ni un enlace de storage para las funciones actuales.

### Actualizar una instalación existente

Realiza primero un respaldo de la base de datos y conserva el `.env` y el `APP_KEY` existentes. Ejecuta desde la raíz del proyecto, comprobando cada paso:

```bash
php artisan down --no-interaction
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear --no-interaction
php artisan migrate --force --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan up --no-interaction
```

Si falla un paso, resuelve el error antes de ejecutar `up`. No ejecutes `migrate:fresh`, no regeneres `APP_KEY` y no repitas los seeders en una actualización ordinaria: podrían restablecer las tasas de ejemplo.

### Comprobación posterior

- `/` presenta el proyecto y enlaza a `/docs`.
- `/docs` carga Swagger y `/openapi.yaml` responde.
- `/api/products` sin token responde 401 JSON.
- Con token válido, la misma ruta responde 200 JSON.
- Verifica creación, consulta, actualización, precio adicional y eliminación con datos de prueba.
- Revisa `storage/logs/laravel.log` si ocurre un error; no habilites `APP_DEBUG` públicamente.

## Actualizar Swagger UI

El contrato se edita en `public/openapi.yaml`; no hay un comando de generación de anotaciones. Para volver a copiar los recursos de la versión fijada en `package-lock.json`:

```bash
npm ci
cp node_modules/swagger-ui-dist/swagger-ui.css public/vendor/swagger-ui/
cp node_modules/swagger-ui-dist/swagger-ui-bundle.js public/vendor/swagger-ui/
cp node_modules/swagger-ui-dist/LICENSE public/vendor/swagger-ui/
```

Conserva la licencia y versiona los archivos copiados. Al cambiar endpoints o reglas, actualiza el YAML y prueba los ejemplos.

## Alcance de la entrega

La implementación incluye los siete endpoints requeridos, Eloquent, JSON, registro manual de precios por divisa, documentación Swagger y medidas de acceso/validación. La protección usa un catálogo compartido y aprovisionamiento por consola; no pretende ser una plataforma multiempresa. Las mejoras futuras pueden incluir roles, gestión de divisas y pruebas específicas de concurrencia y expiración de tokens.
