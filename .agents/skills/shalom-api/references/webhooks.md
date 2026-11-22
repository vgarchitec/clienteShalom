# Webhooks de tracking (https://docs.shalom-api.lat/docs/webhooks)

En lugar de consultar el estado en bucle, registra tu URL y suscribe las guías que te interesan. Cuando una guía cambia de estado, la plataforma envía un POST JSON firmado con HMAC-SHA256 a tu URL. Tu endpoint solo tiene que validar la firma y responder 2xx.

## Qué necesitas para recibir eventos [#qué-necesitas-para-recibir-eventos]

Dos cosas: un webhook configurado en la cuenta (PUT /webhooks) y la guía suscrita (POST /tracking/subscriptions). Con una sola de las dos no llega nada. El evento es tracking.status_changed y se envía cuando cambia el código normalizado o el texto de estado de Shalom; cada cuánto se revisa una guía está en Cómo se actualiza el tracking.

## Estados que puedes recibir [#estados-que-puedes-recibir]

Registrado (REGISTERED): la guía existe pero todavía no la recepcionan en origen. En origen (EN_ORIGIN): recepcionada en la agencia de origen. En tránsito (IN_TRANSIT): despachada hacia el destino. En destino (AT_DESTINATION): llegó a la agencia destino. OUT_FOR_DELIVERY: salió a reparto a domicilio. Entregado (DELIVERED): entregada al destinatario. UNKNOWN es el valor inicial, antes de la primera consulta.

Demora de envíos es un aviso, no un hito: llega con delayed en true (y delayAt con su fecha) y el statusCode sigue siendo el último hito. Shalom mantiene ese texto incluso después de entregar, así que la entrega llega igual con statusCode DELIVERED. Entrar o salir de la demora también genera un evento.

El evento suma statusAt (fecha del hito en ISO UTC, si Shalom la informa) y mirrorMessage, que repite status por compatibilidad. Si Shalom deja de mostrar la línea de tiempo de la orden, status llega en null.

## Entregas, reintentos y pausas [#entregas-reintentos-y-pausas]

Cualquier respuesta 2xx cuenta como entregado. Cada intento espera hasta 10 segundos y las redirecciones no se siguen, así que configura la URL final. Si falla, se reintenta dos veces más en unos segundos (3 intentos en total); después la entrega queda como FAILED.

Si una URL acumula 5 entregas fallidas seguidas, se deja de enviar a esa URL durante 5 minutos. Los eventos de ese lapso no se envían ni aparecen en el historial, por eso conviene responder rápido y procesar después.

## Cuándo seguir usando polling [#cuándo-seguir-usando-polling]

Si solo necesitas el estado cuando el cliente abre la pantalla de seguimiento, una consulta puntual con POST /track es más simple que montar un endpoint público. El webhook tiene sentido cuando algo tiene que pasar sin que nadie esté mirando.

## Paso a paso [#paso-a-paso]

Así se configura el webhook, se suscriben las guías y se valida cada evento en tu servidor.

<Steps>
<Step>
### Registra tu URL [#registra-tu-url]

Llama a PUT /webhooks con la URL de tu servidor. La respuesta trae el secreto de firma (whsec_...) completo: guárdalo, porque después solo se muestra enmascarado. La URL debe ser http o https y apuntar a una IP pública.
</Step>

<Step>
### Suscribe cada guía [#suscribe-cada-guía]

Llama a POST /tracking/subscriptions con el orderNumber y el orderCode de cada guía, idealmente justo después de crearla. Solo las guías suscritas generan eventos.
</Step>

<Step>
### Recibe el evento [#recibe-el-evento]

Cada cambio de estado llega como un POST JSON con los headers X-Shalom-Event (tracking.status_changed), X-Shalom-Event-Id (identificador estable del cambio), X-Shalom-Delivery (id de la entrega) y X-Shalom-Signature. status es el texto de Shalom y statusCode su código normalizado; tracking trae los datos de la guía, igual que search.data en POST /track.

```json
{
  "id": "0b8f2c1e-6a4d-4f7b-9c3e-2d1a5b7c9e01",
  "event": "tracking.status_changed",
  "eventId": "tracking.status_changed:66479331:3KTH:En tránsito->En destino",
  "createdAt": "2026-11-11T14:17:00.000Z",
  "data": {
    "orderNumber": "66479331",
    "orderCode": "3KTH",
    "status": "En destino",
    "statusCode": "AT_DESTINATION",
    "previousStatus": "En tránsito",
    "previousStatusCode": "IN_TRANSIT",
    "subscriptionId": "5f4e3d2c-1b0a-4988-8766-554433221100",
    "tracking": { "ose_id": 106620694, "...": "datos de la guía, como en POST /track" },
    "statusAt": "2026-11-11T14:10:00.000Z",
    "delayed": false,
    "mirrorMessage": "En destino"
  }
}
```
</Step>

<Step>
### Verifica la firma y responde 2xx [#verifica-la-firma-y-responde-2xx]

El header X-Shalom-Signature tiene la forma t=&lt;unix&gt;,v1=&lt;hex&gt;, donde v1 es el HMAC-SHA256 de "&lt;t&gt;.&lt;cuerpo crudo&gt;" con tu secreto. Calcúlalo sobre el cuerpo tal como llegó (antes de parsear el JSON), compáralo en tiempo constante y responde 2xx en cuanto lo valides. Descarta eventos con un t muy antiguo para evitar reenvíos maliciosos.

```js
import { createHmac, timingSafeEqual } from 'node:crypto';

function verifySignature(rawBody, header, secret) {
  const parts = Object.fromEntries(header.split(',').map((p) => p.split('=')));
  const expected = createHmac('sha256', secret)
    .update(`${parts.t}.${rawBody}`)
    .digest('hex');
  const a = Buffer.from(parts.v1 || '');
  const b = Buffer.from(expected);
  return a.length === b.length && timingSafeEqual(a, b);
}
```
</Step>
</Steps>

## Configurar webhook [#configurar-webhook]

```http
PUT /webhooks
```

Registra o reemplaza la URL de webhook de la cuenta. La primera vez genera el secreto de firma; después lo conserva salvo que pidas rotarlo. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `url` | Sí | URL de destino (http o https, que resuelva a una IP pública). Se recomienda https. |
| `rotateSecret` | No | Boolean, por defecto false. Con true genera un secreto nuevo y el anterior deja de valer. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X PUT "https://api.shalom-api.lat/webhooks" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "url": "https://tu-servidor.com/webhooks/shalom"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/webhooks", {
  method: "PUT",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "url": "https://tu-servidor.com/webhooks/shalom"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/webhooks");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "PUT",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["url" => "https://tu-servidor.com/webhooks/shalom"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.put(
    "https://api.shalom-api.lat/webhooks",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"url": "https://tu-servidor.com/webhooks/shalom"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Webhook configurado.",
  "webhook": {
    "url": "https://tu-servidor.com/webhooks/shalom",
    "secret": "whsec_...",
    "enabled": true
  }
}
```

<Callout>
El secreto solo se devuelve completo en esta respuesta; GET /webhooks lo muestra enmascarado.
</Callout>

## Consultar webhook [#consultar-webhook]

```http
GET /webhooks
```

Devuelve la configuración de webhook de la cuenta, con el secreto enmascarado. No consume cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/webhooks" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/webhooks", {
  method: "GET",
  headers: { "x-api-key": process.env.SHALOM_API_KEY },
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/webhooks");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => ["x-api-key: TU_API_KEY"],
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.get(
    "https://api.shalom-api.lat/webhooks",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "configured": true,
  "webhook": {
    "url": "https://tu-servidor.com/webhooks/shalom",
    "enabled": true,
    "secretPreview": "whsec_9f2b7c…a1b2"
  }
}
```

<Callout>
Sin webhook configurado responde \{ success: true, configured: false, webhook: null \}.
</Callout>

## Eliminar webhook [#eliminar-webhook]

```http
DELETE /webhooks
```

Borra la URL y el secreto de la cuenta. Dejas de recibir eventos aunque tengas guías suscritas. No consume cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X DELETE "https://api.shalom-api.lat/webhooks" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/webhooks", {
  method: "DELETE",
  headers: { "x-api-key": process.env.SHALOM_API_KEY },
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/webhooks");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => ["x-api-key: TU_API_KEY"],
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.delete(
    "https://api.shalom-api.lat/webhooks",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Webhook eliminado."
}
```

## Historial de entregas del webhook [#historial-de-entregas-del-webhook]

```http
GET /webhooks/deliveries
```

Lista las entregas del webhook de la cuenta, de la más reciente a la más antigua, con su estado, intentos, código de respuesta y último error. Sirve para comprobar si un aviso llegó y qué contestó tu servidor. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `page` | No | Página (entero, por defecto 1). |
| `per_page` | No | Entregas por página (1 a 50, por defecto 20). |
| `status` | No | Filtra por SUCCESS, FAILED o PENDING. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/webhooks/deliveries?per_page=5" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/webhooks/deliveries?per_page=5", {
  method: "GET",
  headers: { "x-api-key": process.env.SHALOM_API_KEY },
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/webhooks/deliveries?per_page=5");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => ["x-api-key: TU_API_KEY"],
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.get(
    "https://api.shalom-api.lat/webhooks/deliveries?per_page=5",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "total": 38,
  "page": 1,
  "per_page": 5,
  "data": [
    {
      "id": "0b8f2c1e-6a4d-4f7b-9c3e-2d1a5b7c9e01",
      "event": "tracking.status_changed",
      "eventId": "tracking.status_changed:66479331:3KTH:En tránsito->En destino",
      "orderNumber": "66479331",
      "orderCode": "3KTH",
      "transition": "En tránsito->En destino",
      "status": "SUCCESS",
      "attempts": 1,
      "responseCode": 200,
      "lastError": null,
      "targetUrl": "https://tu-servidor.com/webhooks/shalom",
      "createdAt": "2026-11-11T14:17:00.000Z",
      "deliveredAt": "2026-11-11T14:17:01.000Z"
    }
  ]
}
```

<Callout>
Con FAILED, lastError dice qué pasó (por ejemplo "HTTP 500" o "timeout"). PENDING es una entrega que todavía se está intentando.
</Callout>

## Suscribir una guía [#suscribir-una-guía]

```http
POST /tracking/subscriptions
```

Suscribe la cuenta a los cambios de estado de una guía y la pone en la actualización en segundo plano. Si ya estaba suscrita, la reactiva. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `orderNumber` | Sí | Número de orden (8 a 12 dígitos). |
| `orderCode` | Sí | Código de seguridad (4 caracteres). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/tracking/subscriptions" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "orderNumber": "66479331",
  "orderCode": "3KTH"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/tracking/subscriptions", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "orderNumber": "66479331",
  "orderCode": "3KTH"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/tracking/subscriptions");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["orderNumber" => "66479331", "orderCode" => "3KTH"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/tracking/subscriptions",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"orderNumber": "66479331", "orderCode": "3KTH"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Suscripción creada.",
  "subscription": {
    "id": "5f4e3d2c-1b0a-4988-8766-554433221100",
    "orderNumber": "66479331",
    "orderCode": "3KTH",
    "lastStatus": null,
    "lastMirrorStatus": null,
    "active": true,
    "createdAt": "2026-11-11T12:00:00.000Z"
  }
}
```

<Callout>
La API no comprueba en este paso que la guía exista. Verifícala antes con POST /track si el número o el código vienen de un formulario.
</Callout>

## Listar suscripciones [#listar-suscripciones]

```http
GET /tracking/subscriptions
```

Lista las suscripciones de la cuenta, de la más reciente a la más antigua, con el último estado avisado. No consume cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/tracking/subscriptions" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/tracking/subscriptions", {
  method: "GET",
  headers: { "x-api-key": process.env.SHALOM_API_KEY },
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/tracking/subscriptions");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => ["x-api-key: TU_API_KEY"],
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.get(
    "https://api.shalom-api.lat/tracking/subscriptions",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "total": 1,
  "data": [
    {
      "id": "5f4e3d2c-1b0a-4988-8766-554433221100",
      "orderNumber": "66479331",
      "orderCode": "3KTH",
      "lastStatus": "IN_TRANSIT",
      "lastMirrorStatus": "En tránsito",
      "active": true,
      "createdAt": "2026-11-11T12:00:00.000Z"
    }
  ]
}
```

## Cancelar suscripción [#cancelar-suscripción]

```http
DELETE /tracking/subscriptions
```

Cancela la suscripción de la cuenta a una guía. Los datos van en el querystring. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `orderNumber` | Sí | Número de orden (8 a 12 dígitos). |
| `orderCode` | Sí | Código de seguridad (4 caracteres). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X DELETE "https://api.shalom-api.lat/tracking/subscriptions?orderNumber=66479331&orderCode=3KTH" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/tracking/subscriptions?orderNumber=66479331&orderCode=3KTH", {
  method: "DELETE",
  headers: { "x-api-key": process.env.SHALOM_API_KEY },
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/tracking/subscriptions?orderNumber=66479331&orderCode=3KTH");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => ["x-api-key: TU_API_KEY"],
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.delete(
    "https://api.shalom-api.lat/tracking/subscriptions?orderNumber=66479331&orderCode=3KTH",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Suscripción eliminada."
}
```

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | La URL del webhook no es válida, no es http(s) o apunta a una dirección privada o local; o la guía está mal formada. |
| `401` | Falta la API key o es inválida. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Cómo compruebo que el POST viene de Shalom API?">
Verificando el header X-Shalom-Signature con el secreto que devuelve PUT /webhooks. Sin esa comprobación, cualquiera que conozca tu URL podría enviarte eventos falsos.
</Accordion>

<Accordion title="¿Puedo cambiar la URL del webhook?">
Sí, volviendo a llamar a PUT /webhooks con la nueva url. El secreto se mantiene salvo que envíes rotateSecret en true.
</Accordion>

<Accordion title="¿Qué significa status y qué statusCode en el evento?">
status es el texto literal de Shalom (por ejemplo "En destino") y statusCode su código normalizado (UNKNOWN, REGISTERED, EN_ORIGIN, IN_TRANSIT, AT_DESTINATION, OUT_FOR_DELIVERY, DELIVERED). Usa statusCode para decidir flujos y status para mostrarle al cliente lo mismo que ve en Shalom.
</Accordion>

<Accordion title="¿Qué pasa si mi servidor está caído cuando llega un evento?">
Se reintenta hasta 3 veces en pocos segundos. Si los tres fallan, la entrega queda como FAILED en GET /webhooks/deliveries y no se vuelve a enviar sola. Tras 5 entregas fallidas seguidas, tu URL se pausa 5 minutos y los eventos de ese lapso no se envían.
</Accordion>

<Accordion title="¿Me puede llegar el mismo evento dos veces?">
El mismo cambio de una guía no se entrega dos veces a la misma cuenta. Aun así, procesa por eventId de forma idempotente.
</Accordion>

<Accordion title="¿Cómo dejo de recibir eventos de una guía?">
Con DELETE /tracking/subscriptions, enviando su orderNumber y su orderCode en el querystring.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Cómo se actualiza el tracking" href="/docs/optimizations" />

<Card title="Consultar el estado puntual por API" href="/docs/tracking" />

<Card title="Errores y límites" href="/docs/errores" />
</Cards>