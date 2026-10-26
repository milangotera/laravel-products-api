<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentación de Productos API</title>

    <link
        rel="stylesheet"
        href="{{ asset('vendor/swagger-ui/swagger-ui.css') }}"
    >
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="{{ asset('vendor/swagger-ui/swagger-ui-bundle.js') }}"></script>

    <script>
        window.onload = function () {
            SwaggerUIBundle({
                url: @json(asset('openapi.yaml')),
                dom_id: '#swagger-ui',
                deepLinking: true,
                persistAuthorization: false,
                validatorUrl: null,
            });
        };
    </script>
</body>
</html>