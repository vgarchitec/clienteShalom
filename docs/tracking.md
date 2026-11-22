# Rastrear envíos (https://docs.shalom-api.lat/docs/tracking)

El rastreo consulta el estado de una guía de Shalom con su número de orden y su código de seguridad. La respuesta es el espejo del rastreo público de Shalom. Los documentos del envío (ticket y rótulo) salen de tu cuenta de Shalom Pro y requieren una instancia.

## Qué datos necesitas [#qué-datos-necesitas]

El número de orden (8 a 12 dígitos) y el código de seguridad (4 caracteres) que recibe el cliente al registrar el envío. Sin el código la guía no se puede consultar, aunque el número exista. Si solo tienes el ose_id (el identificador interno de la orden), conviértelo primero con GET /track/by-ose.

## Qué significa que la respuesta sea un espejo [#qué-significa-que-la-respuesta-sea-un-espejo]

La API devuelve lo que publica Shalom, no un estado propio. Los campos cambian según la etapa del envío y no todos los hitos están siempre presentes: un hito que todavía no ocurrió llega en null.

El estado que muestra Shalom está en statuses.message ("Registrado", "En origen", "En tránsito", "En destino", "Demora de envíos" o "Entregado"). Los webhooks agregan además un código normalizado, que es el que conviene usar para decidir flujos.

## Individual, lote o webhook [#individual-lote-o-webhook]

POST /track resuelve una guía. Para revisar muchas a la vez usa POST /track/batch, que acepta hasta 50 y consume una sola operación.

Si necesitas reaccionar a los cambios (avisar al comprador, cerrar un pedido contra entrega), no consultes en bucle: suscribe las guías y recibe un webhook cuando cambien. Cada cuánto se actualiza una guía se explica en Cómo se actualiza el tracking.

## Rastrear un envío [#rastrear-un-envío]

```http
POST /track
```

Devuelve el resultado de búsqueda (search) y la línea de tiempo (statuses) de la guía, tal como los publica el rastreo de Shalom. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `orderNumber` | Sí | Número de orden. String de 8 a 12 dígitos, solo números (las guías nuevas de Shalom tienen 9). |
| `orderCode` | Sí | Código de seguridad. String de exactamente 4 caracteres. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/track" \
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
const res = await fetch("https://api.shalom-api.lat/track", {
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
$ch = curl_init("https://api.shalom-api.lat/track");
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
    "https://api.shalom-api.lat/track",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"orderNumber": "66479331", "orderCode": "3KTH"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "search": {
    "success": true,
    "message": "Guia encontrada.",
    "data": {
      "ose_id": 106620694,
      "numero_orden": "66479331",
      "codigo_orden": "3KTH",
      "entregado": false,
      "contenido": "1 PAQUETE XS",
      "origen": { "id": 7, "nombre": "LIMA" },
      "destino": { "id": 582, "nombre": "PIURA" },
      "...": "más campos del espejo: monto, pago, remitente, destinatario, comprobante"
    }
  },
  "statuses": {
    "message": "En tránsito",
    "data": {
      "registrado": { "fecha": "2026-11-10 09:12:00" },
      "origen": { "fecha": "2026-11-10 11:40:00" },
      "transito": { "fecha": "2026-11-10 20:05:00" },
      "destino": null,
      "reparto": null,
      "entregado": null
    }
  }
}
```

<Callout>
Si Shalom no encuentra la guía (número o código equivocados), la respuesta es 200 con search.success en false y statuses en null: revisa search.success antes de leer los datos. statuses.message es el texto del estado que muestra Shalom y statuses.data trae un nodo por hito, con su fecha en hora de Perú. Si el dato guardado tiene más de 10 minutos, se pide el estado en vivo y se espera hasta 8 segundos; si no llega a tiempo, se responde el último dato guardado.
</Callout>

## Rastrear en lote [#rastrear-en-lote]

```http
POST /track/batch
```

Rastrea hasta 50 guías en una sola llamada (se consultan de 5 en 5). Una guía que falla no corta el lote. Consume 1 operación por llamada, sin importar cuántas guías envíes.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `orders` | Sí | Array de 1 a 50 objetos \{ orderNumber, orderCode \}, con las mismas reglas que POST /track. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/track/batch" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "orders": [
    { "orderNumber": "66479331", "orderCode": "3KTH" },
    { "orderNumber": "66479332", "orderCode": "9ABC" }
  ]
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/track/batch", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "orders": [
    { "orderNumber": "66479331", "orderCode": "3KTH" },
    { "orderNumber": "66479332", "orderCode": "9ABC" }
  ]
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/track/batch");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["orders" => [["orderNumber" => "66479331", "orderCode" => "3KTH"], ["orderNumber" => "66479332", "orderCode" => "9ABC"]]]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/track/batch",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"orders": [{"orderNumber": "66479331", "orderCode": "3KTH"}, {"orderNumber": "66479332", "orderCode": "9ABC"}]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
[
  {
    "orderNumber": "66479331",
    "orderCode": "3KTH",
    "search": { "success": true, "...": "igual que POST /track" },
    "statuses": { "message": "En tránsito", "...": "igual que POST /track" }
  },
  {
    "orderNumber": "66479332",
    "orderCode": "9ABC",
    "success": false,
    "error": "Shalom buscar API returned status 503"
  }
]
```

<Callout>
La respuesta es un array directo, en el mismo orden que enviaste. Cada elemento repite su orderNumber y orderCode; los que fallaron traen success en false y error.
</Callout>

## Resolver ose_id a número de orden y código [#resolver-ose_id-a-número-de-orden-y-código]

```http
GET /track/by-ose
```

Convierte un ose_id (service_order_id) en número de orden y código de seguridad, con el estado y los datos del envío. Usa el rastreo público de Shalom; si envías instanceId y la guía aún no es pública, la busca también en los envíos pendientes de esa cuenta. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `ose_id` | Sí | ose_id de la orden. Solo números, hasta 15 dígitos. |
| `instanceId` | No | Opcional: instancia de tu cuenta para buscar también en los envíos pendientes. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/track/by-ose?ose_id=106620694" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/track/by-ose?ose_id=106620694", {
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
$ch = curl_init("https://api.shalom-api.lat/track/by-ose?ose_id=106620694");
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
    "https://api.shalom-api.lat/track/by-ose?ose_id=106620694",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Guia encontrada.",
  "source": "tracking",
  "data": {
    "ose_id": 106620694,
    "orderNumber": "66479331",
    "orderCode": "3KTH",
    "status": "En destino",
    "delivered": false,
    "monto": "12.00",
    "montoAdicional": 0,
    "contenido": "1 PAQUETE XS",
    "fecha_emision": "2026-11-10 09:12:00",
    "fecha_traslado": "2026-11-10 20:05:00",
    "tiempo_llegada": "48 horas",
    "tipo_pago": "Contra entrega",
    "estado_pago": "CA",
    "direccion_entrega": "ENTREGAR EN AGENCIA",
    "aereo": false,
    "reparto": null,
    "remitente": { "nombre": "TIENDA EJEMPLO SAC", "documento": "20123456789" },
    "destinatario": { "nombre": "CLIENTE DE PRUEBA", "documento": "12345678" },
    "comprobante": { "cop_id": 72409411, "tipo": "BV", "serie": "B936", "numero": "0049643", "fecha": "2026-11-10", "hora": "12:46:00", "estado_pago": "CA" },
    "origen": { "id": 7, "nombre": "LIMA" },
    "destino": { "id": 582, "nombre": "PIURA" }
  }
}
```

<Callout>
Si la encuentra en tus envíos pendientes, responde source "pending" y status "Pendiente", con menos campos: fecha, tipo_pago, recipient_id, remitente y destinatario (\{ nombre, documento, telefono \}, pueden venir en null), origen y destino.
</Callout>

## Descargar el ticket oficial (PDF o imagen) [#descargar-el-ticket-oficial-pdf-o-imagen]

```http
GET /track/voucher
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Descarga el ticket oficial del envío tal como lo emite Shalom Pro, en PDF o como imagen lista para WhatsApp. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro dueña del envío. |
| `ose_id` | No | ose_id del envío. Recomendado: funciona también con guías recién creadas. |
| `orderNumber` | No | Número de orden (8 a 12 dígitos). Alternativa a ose_id, junto con orderCode. |
| `orderCode` | No | Código de seguridad (4 caracteres). Va con orderNumber. |
| `format` | No | pdf (por defecto) devuelve el PDF oficial; image, jpg o jpeg devuelven JPEG y png devuelve PNG, rasterizados del mismo PDF. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694&format=pdf" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694&format=pdf", {
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
$ch = curl_init("https://api.shalom-api.lat/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694&format=pdf");
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
    "https://api.shalom-api.lat/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694&format=pdf",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
Content-Type: application/pdf
Content-Disposition: inline; filename="voucher-106620694.pdf"

<binario: PDF del ticket oficial de Shalom>
```

<Callout>
Envía ose_id o bien orderNumber + orderCode. Con orderNumber + orderCode el ose_id se resuelve por el rastreo público y, si la guía aún no aparece ahí, por tus envíos pendientes. La imagen sale del mismo PDF oficial: nunca se genera un ticket propio.
</Callout>

## Descargar el rótulo (etiqueta PDF) [#descargar-el-rótulo-etiqueta-pdf]

```http
GET /track/label
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Descarga el PDF del rótulo del envío, la etiqueta que se pega en el paquete, generado por Shalom Pro. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro dueña del envío. |
| `ose_id` | No | ose_id del envío. Recomendado: es la única forma de pedir el rótulo de una guía que todavía no aparece en el rastreo público. |
| `orderNumber` | No | Número de orden (8 a 12 dígitos). Alternativa a ose_id, junto con orderCode. |
| `orderCode` | No | Código de seguridad (4 caracteres). Va con orderNumber. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/track/label?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/track/label?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694", {
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
$ch = curl_init("https://api.shalom-api.lat/track/label?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694");
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
    "https://api.shalom-api.lat/track/label?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=106620694",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
Content-Type: application/pdf

<binario: PDF del rótulo>
```

<Callout>
Con orderNumber + orderCode el ose_id se resuelve solo por el rastreo público (sin respaldo en pendientes). El PDF lo genera Shalom en formato horizontal, pensado para imprimir en A4, no en etiqueta térmica. Shalom también emite el rótulo de guías anuladas: verifica el estado antes de despachar.
</Callout>

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | orderNumber no tiene de 8 a 12 dígitos, orderCode no tiene 4 caracteres, ose_id no es numérico, format no es válido, o falta ose_id y también orderNumber + orderCode. |
| `403` | El instanceId no existe o no pertenece a tu API key. |
| `404` | Shalom no tiene esa orden o no se pudo resolver su ose_id (by-ose, voucher y label). POST /track no responde 404: devuelve search.success en false. |
| `409` | La instancia perdió la sesión de Shalom Pro y no pudo reconectarse. Vuelve a iniciar sesión (ver Instancias). |
| `502` | Shalom no devolvió el documento o la consulta. Reintenta en unos minutos; el borde puede mostrarlo como "error code: 502". |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Qué necesito para rastrear un envío de Shalom por API?">
El número de orden (8 a 12 dígitos; las guías nuevas tienen 9) y el código de seguridad de 4 caracteres que se entregan al registrar la guía. Los dos son obligatorios.
</Accordion>

<Accordion title="¿Cuántas guías puedo consultar en una sola llamada?">
Una con POST /track y hasta 50 con POST /track/batch. El lote consume 1 operación por llamada.
</Accordion>

<Accordion title="¿El estado del envío es en tiempo real?">
Es un espejo del rastreo de Shalom. Si el dato guardado tiene más de 10 minutos, se pide el estado en vivo antes de responderte. Las reglas completas están en Cómo se actualiza el tracking.
</Accordion>

<Accordion title="¿Cómo sé si la guía no existe?">
POST /track responde 200 con search.success en false y statuses en null. Revisa que el número y el código sean los del ticket.
</Accordion>

<Accordion title="¿Cómo obtengo el ticket o el rótulo del envío?">
Con GET /track/voucher (ticket oficial en PDF, o imagen con format=image o png) y GET /track/label (rótulo en PDF). Los dos requieren una instancia de Shalom Pro.
</Accordion>

<Accordion title="¿Cómo evito estar consultando el estado todo el tiempo?">
Con webhooks: registras tu URL una vez, suscribes las guías y recibes un POST firmado cada vez que una cambia de estado.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Recibir cambios de estado por webhook" href="/docs/webhooks" />

<Card title="Cómo se actualiza el tracking" href="/docs/optimizations" />

<Card title="Crear envíos en Shalom Pro" href="/docs/crear-envio" />
</Cards>