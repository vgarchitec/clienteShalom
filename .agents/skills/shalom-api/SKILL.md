---
name: shalom-api
description: Integra Shalom Perú (courier) por API REST desde cualquier agente de IA. Use when the user says "api de shalom", "rastrear envío shalom", "seguir mi pedido shalom", "guía de shalom", "crear guía shalom pro", "agencia shalom más cercana", "cotizar envío shalom", "integrar shalom", "API shalom" or "shalom api peru" — or asks to add courier shipping, COD delivery tracking, agency lookup with coordinates, webhook delivery updates for WooCommerce/Shopify/n8n to any app. Covers tracking de envíos, catálogo de agencias, cotizaciones, DNI, creación de guías en Shalom Pro y webhooks firmados.
---

# Shalom API

API REST con respuestas JSON para el courier Shalom (Perú): rastreo de guías,
agencias, cotización, DNI, creación de guías en Shalom Pro y webhooks.
Shalom API es independiente y no está afiliada a Shalom Empresarial S.A.C.

Esta skill tiene solo las reglas que rompen integraciones. **El detalle de cada
endpoint (parámetros, ejemplos y respuestas) está en la documentación: léela
antes de escribir una llamada en vez de suponer.**

- Índice para agentes: https://docs.shalom-api.lat/llms.txt
- Documentación completa en Markdown: https://docs.shalom-api.lat/llms-full.txt
- Cualquier página en Markdown: agrega `.md` a su URL (por ejemplo https://docs.shalom-api.lat/docs/tracking.md).

## Lo básico

- **URL base:** `https://api.shalom-api.lat`
- **Autenticación:** header `x-api-key: sk_...` en cada petición (salvo `/public/*`).
  La key se crea en el panel (https://shalom-api.lat, sección API Key). Léela de
  la variable de entorno `SHALOM_API_KEY`; nunca la escribas en el código.
- **Valida la key** con `GET /validate` antes de operar: devuelve el límite y el consumo.
- **Límites:** 60 peticiones por minuto por key (`429` con
  `retry-after`) y una cuota mensual de operaciones según el plan. Solo consumen
  cuota las llamadas exitosas. Detalle: https://docs.shalom-api.lat/docs/autenticacion.md y https://docs.shalom-api.lat/docs/errores.md

## Reglas que rompen integraciones

1. **Shalom Pro necesita una instancia.** Crear guías, listar pendientes,
   comprobantes, rótulos y autogestiones operan sobre la cuenta Shalom Pro del
   usuario: crea una instancia (`POST /instances`), inicia sesión una sola vez
   (`POST /instances/login`) y envía su `instanceId`. La API reconecta la sesión
   sola: no hagas login antes de cada llamada. Repite el login solo si la
   respuesta dice `Auto-login failed` o `no credentials stored`.
   https://docs.shalom-api.lat/docs/instancias.md
2. **El plan gratuito no crea guías:** tiene 5 operaciones al mes y ninguna instancia.
3. **Una guía que no existe no da 404.** `POST /track` responde `200` con
   `search.success: false` y `statuses: null`: revisa `search.success` antes de
   leer los datos. `orderNumber` tiene 8 a 12 dígitos y `orderCode` 4 caracteres.
   Para muchas guías usa `POST /track/batch` (máx. 50, cobra 1 operación por
   llamada). https://docs.shalom-api.lat/docs/tracking.md
4. **Los códigos salen del catálogo, no se inventan.** En `POST /account/register`,
   `origen` es el `ter_id` (entero) de `GET /agencies/search`; `destino` es un
   `ter_id`, su nombre, o un string que empieza con `"0"` para envío aéreo
   (`"052"`). `content` sale de `GET /account/products` (no consume cuota); con
   `OTRA MEDIDA` envía también ancho, alto, largo (metros, máx. 1.5) y peso
   (kg, máx. 500). https://docs.shalom-api.lat/docs/crear-envio.md
5. **La clave de recojo** tiene 4 dígitos y no puede ser consecutiva (1234, 4321).
   Si Shalom rechaza el registro, la API responde `400` con `{ success: false, message }`.
6. **Cotiza con `instanceId`.** Así usa la tarifa de la cuenta, la misma que se
   cobra al registrar. Sin él se usa el cotizador público de Shalom, que puede
   responder `503`. https://docs.shalom-api.lat/docs/cotizar.md
7. **`POST /account/pending-shipments` devuelve un objeto** con claves `"0"`,
   `"1"`, …, no un array: recórrelo con `Object.values()`. https://docs.shalom-api.lat/docs/operaciones.md
8. **Webhooks en vez de polling.** Configura la URL con `PUT /webhooks` (el
   secreto `whsec_` se muestra una sola vez), suscribe guías con
   `POST /tracking/subscriptions` y verifica `X-Shalom-Signature` (HMAC-SHA256
   de `"<t>.<cuerpo crudo>"`). Decide con `statusCode`, no con el texto de
   `status`. https://docs.shalom-api.lat/docs/webhooks.md
9. **El catálogo de agencias cambia poco:** guárdalo en caché un par de horas en
   vez de pedirlo en cada petición. https://docs.shalom-api.lat/docs/agencias.md
10. **Nunca escribas bucles de consulta.** No generes crons ni loops que llamen a
    `POST /track`, `/track/batch` o a los listados del panel
    (`/account/tracking-list`, `/account/history`, `/account/vouchers`,
    `/account/pending-shipments`) para detectar cambios: usa webhooks (regla 8) y
    guarda los estados en tu base. Si ya recibes webhooks, no consultes además las
    mismas guías. Los listados del panel, como máximo cada 15 minutos por
    instancia y guardados; DNI, tickets y rótulos se guardan, no se vuelven a pedir.
11. **No reintentes errores 4xx.** Un `400`/`401`/`403`/`404` no se arregla
    reintentando: marca el dato con error y no lo vuelvas a pedir. Solo `429` y
    `5xx` se reintentan, con espera creciente y un tope de intentos.
12. **Uso justo.** Una integración con consumo excesivo (bucles, reintentos sin fin)
    pierde el acceso de su API key; reactivarla cuesta un pago único de S/30 y
    corregir la integración. Revisa la lista de https://docs.shalom-api.lat/docs/buenas-practicas.md antes de
    entregar cualquier código.

## Antes de ejecutar

- Las lecturas (rastrear, agencias, cotizar) son seguras.
- Crear guías (`/account/register`, `/account/register-bulk`), anular pendientes
  (`DELETE /account/pending-shipments`, no se puede deshacer) y las autogestiones
  cambian envíos reales: confírmalo con el usuario antes de llamar.
- Ante un error, lee el campo `error` (o `message`) de la respuesta y la tabla de
  https://docs.shalom-api.lat/docs/errores.md antes de reintentar.
