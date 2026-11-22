# Shalom API

> Documentación de Shalom API: rastrea envíos, consulta agencias, cotiza, crea guías en Shalom Pro, recibe webhooks y conecta Claude o ChatGPT.

- Inicio: Primeros pasos, autenticación e integraciones
  - [Inicio rápido](https://docs.shalom-api.lat/docs.md): Qué es Shalom API y cómo hacer tu primera consulta en minutos.
  - [Autenticación y API keys](https://docs.shalom-api.lat/docs/autenticacion.md): Cómo autenticar tus peticiones a Shalom API Perú con el header x-api-key, validar tu API key, qué consume cuota y el rate limit de 60 peticiones por minuto.

  - **Integraciones**
  - [Conectar Claude o ChatGPT](https://docs.shalom-api.lat/docs/conectar-ia.md): Conecta tu cuenta de Shalom API a Claude o ChatGPT con OAuth y opera tus envíos desde el chat.
  - [Instalar la skill de IA](https://docs.shalom-api.lat/docs/instalar-skill.md): Instala la skill shalom-api en tu agente de IA (Claude Code, Cursor, Codex, OpenCode, VS Code) y enseña a la IA a rastrear envíos, buscar agencias, cotizar y crear guías de Shalom por API.
  - [Instalar el nodo de n8n](https://docs.shalom-api.lat/docs/instalar-n8n.md): Instala el nodo comunitario n8n-nodes-shalom en n8n self-hosted y conecta Shalom API a tus flujos: tracking, agencias, cotizaciones y creación de envíos sin escribir HTTP a mano.

  - **Conceptos**
  - [Cómo se actualiza el tracking](https://docs.shalom-api.lat/docs/optimizations.md): Cómo mantiene Shalom API frescos los estados de envío: solo las guías suscritas se actualizan en segundo plano, ritmo según actividad, pausa nocturna, guías inactivas y refresco en vivo al consultar /track.
  - [Buenas prácticas y uso justo](https://docs.shalom-api.lat/docs/buenas-practicas.md): Cómo integrar Shalom API sin consultas en bucle: webhooks en lugar de polling, POST /track/batch, listados del panel con caché, no reintentar errores 4xx y la política de uso justo que suspende las integraciones con consumo excesivo.
  - [Errores y límites](https://docs.shalom-api.lat/docs/errores.md): Códigos de error de Shalom API Perú (400, 401, 403, 404, 409, 422, 429, 500, 502 y 503), el rate limit de 60 peticiones por minuto, la cuota mensual y cómo reintentar.
- Referencia API: Todos los endpoints con ejemplos
  - [Referencia de la API](https://docs.shalom-api.lat/docs/referencia.md): URL base, autenticación, formato de las respuestas y lista de todos los endpoints de Shalom API.
  - Envíos
    - [Rastrear envíos](https://docs.shalom-api.lat/docs/tracking.md): Rastrea envíos de Shalom por API: consulta individual con POST /track, lote de hasta 50 guías, búsqueda por ose_id, ticket oficial en PDF o imagen y rótulo en PDF.
    - [Cotizar tarifas y consultar DNI](https://docs.shalom-api.lat/docs/cotizar.md): Cotiza el costo de un envío de Shalom entre dos agencias por API con la tarifa de tu cuenta Shalom Pro y consulta el nombre de un DNI antes de crear la guía.
    - [Crear envíos en Shalom Pro](https://docs.shalom-api.lat/docs/crear-envio.md): Crea guías reales en Shalom Pro por API: registro individual con destinatario automático, registro en lote, catálogo de contenidos, perfil del remitente y búsqueda de personas registradas.
    - [Operaciones de envíos](https://docs.shalom-api.lat/docs/operaciones.md): Gestiona los envíos ya creados en Shalom Pro: lista y anula pendientes, consulta el seguimiento en tránsito, filtra el historial de entregados y obtén los comprobantes con su enlace de descarga.
    - [Autogestiones: gestionar una guía ya creada](https://docs.shalom-api.lat/docs/autogestiones.md): Gestiona una guía ya creada en Shalom por API: cambia la clave de recojo, agrega un contacto, cambia el destino, registra una devolución, retiene o libera la carga y pide reparto a domicilio, con verificación por SMS o email.
  - Catálogo
    - [Agencias y cobertura](https://docs.shalom-api.lat/docs/agencias.md): Consulta el catálogo de agencias de Shalom por API: listado, búsqueda por texto, departamento, provincia, servicio aéreo y cercanía con coordenadas, y una versión pública sin API key.
    - [Ubicaciones (ubigeos)](https://docs.shalom-api.lat/docs/ubicaciones.md): Obtén los departamentos, provincias y distritos del Perú donde Shalom tiene agencias, con sus IDs, para armar selectores de dirección en tu checkout.
  - [Instancias (sesión Shalom Pro)](https://docs.shalom-api.lat/docs/instancias.md): Conecta tu cuenta Shalom Pro como instancia: crea, lista y elimina instancias, y gestiona la sesión de Shalom Pro (login, estado y logout) por API.
  - [Webhooks de tracking](https://docs.shalom-api.lat/docs/webhooks.md): Recibe un POST firmado con HMAC-SHA256 cada vez que un envío de Shalom cambia de estado: configura el webhook, suscribe guías, verifica la firma y revisa el historial de entregas.