<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="API REST para gestionar productos, costos y precios en distintas divisas. Consulta su documentación interactiva.">
    <title>Products API · Productos y divisas</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #17243b; background: #f7f9fc; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        main { min-height: 100svh; display: flex; align-items: center; padding: clamp(28px, 6vw, 88px); }
        .hero { width: 100%; max-width: 1120px; margin: auto; }
        .brand { display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 750; letter-spacing: -.02em; }
        .mark { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; color: white; background: #2862e9; font-family: monospace; font-size: 17px; }
        .layout { display: grid; grid-template-columns: 1.25fr 1fr; align-items: center; gap: clamp(32px, 6vw, 90px); margin: 54px 0 42px; }
        .eyebrow { color: #2862e9; font-size: 12px; font-weight: 750; letter-spacing: .15em; text-transform: uppercase; }
        h1 { font-size: clamp(38px, 5vw, 66px); line-height: 1.06; letter-spacing: -.055em; margin: 18px 0 24px; max-width: 650px; }
        h1 span { color: #2862e9; }
        .intro { font-size: 17px; line-height: 1.75; color: #526079; max-width: 520px; }
        .features { padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 10px; margin: 24px 0 32px; }
        .features li { font-size: 12px; font-weight: 600; border: 1px solid #dce4f0; background: white; padding: 8px 12px; border-radius: 8px; }
        .actions { display: flex; align-items: center; flex-wrap: wrap; gap: 20px; }
        a { color: #285bd0; text-underline-offset: 4px; }
        .primary { display: inline-flex; align-items: center; gap: 16px; background: #2862e9; color: #fff; text-decoration: none; padding: 15px 20px; border-radius: 11px; font-size: 14px; font-weight: 650; }
        .primary:hover { background: #1d4ed8; }
        .secondary { font-size: 13px; }
        a:focus-visible { outline: 3px solid #edac35; outline-offset: 5px; }
        .note { font-size: 12px; line-height: 1.7; color: #68768b; margin-top: 18px; }
        .code-card { background: #122039; color: #dce7fa; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 55px #17305216; min-width: 0; }
        .code-head { display: flex; gap: 10px; align-items: center; padding: 20px 24px; border-bottom: 1px solid #ffffff15; font-size: 12px; font-family: monospace; }
        .method { color: #8ce3c1; font-weight: bold; }
        pre { margin: 0; padding: 26px; font-size: 13px; line-height: 1.85; overflow-x: auto; }
        .key { color: #a8c5ff; } .value { color: #a6e5ce; }
        .code-foot { color: #96a8c3; padding: 0 26px 22px; font-size: 11px; }
        footer { border-top: 1px solid #dde4ef; padding-top: 20px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; color: #738096; font-size: 12px; }
        @media (max-width: 760px) { .layout { grid-template-columns: 1fr; margin: 32px 0; gap: 30px; } h1 { max-width: 520px; } pre { font-size: 12px; } }
    </style>
</head>
<body>
    <main>
        <section class="hero" aria-labelledby="title">
            <div class="brand"><span class="mark" aria-hidden="true">{ }</span> Products API</div>
            <div class="layout">
                <div>
                    <p class="eyebrow">Laravel · REST API</p>
                    <h1 id="title">Tu catálogo.<br>Precios en <span>distintas divisas.</span></h1>
                    <p class="intro">Crea, consulta y administra productos, sus costos y sus precios por moneda. Una API con respuestas JSON y acceso mediante tokens.</p>
                    <ul class="features" aria-label="Características">
                        <li>Productos y costos</li><li>Precios por divisa</li><li>Laravel Sanctum</li>
                    </ul>
                    <div class="actions">
                        <a class="primary" href="{{ route('docs') }}">Explorar documentación <span aria-hidden="true">↗</span></a>
                        <a class="secondary" href="{{ asset('openapi.yaml') }}">Ver especificación OpenAPI</a>
                    </div>
                    <p class="note">Prueba los endpoints desde Swagger con tu token de acceso.<br>Encontrarás los pasos de instalación y autenticación en el README.</p>
                </div>
                <div class="code-card" aria-label="Ejemplo de respuesta JSON de un producto">
                    <div class="code-head"><span class="method">GET</span> /api/products/1</div>
                    <pre><code>{
  <span class="key">"data"</span>: {
    <span class="key">"id"</span>: 1,
    <span class="key">"name"</span>: <span class="value">"Teclado"</span>,
    <span class="key">"price"</span>: <span class="value">"25.50"</span>,
    <span class="key">"currency"</span>: {
      <span class="key">"name"</span>: <span class="value">"USD"</span>,
      <span class="key">"symbol"</span>: <span class="value">"$"</span>
    },
    <span class="key">"tax_cost"</span>: <span class="value">"2.00"</span>,
    <span class="key">"manufacturing_cost"</span>: <span class="value">"10.00"</span>
  }
}</code></pre>
                    <div class="code-foot">Ejemplo abreviado · application/json</div>
                </div>
            </div>
            <footer><span>Prueba técnica de backend</span><span>Laravel · Eloquent · OpenAPI 3.0</span></footer>
        </section>
    </main>
</body>
</html>
