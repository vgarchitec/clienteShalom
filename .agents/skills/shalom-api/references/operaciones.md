# Operaciones de envíos (https://docs.shalom-api.lat/docs/operaciones)

Estos endpoints consultan y gestionan las guías que ya existen en tu cuenta de Shalom Pro, igual que las vistas del panel. Todos requieren una instancia con la sesión iniciada (instanceId).

## Las tres etapas de una guía [#las-tres-etapas-de-una-guía]

Pendiente: registrada y todavía no despachada. Solo en esta etapa se puede anular, y puede que todavía no aparezca en el rastreo público. En tránsito: ya salió y está en camino. Entregada: aparece en el historial y en los comprobantes.

## Anular un pendiente [#anular-un-pendiente]

DELETE /account/pending-shipments busca la guía en tus pendientes y la anula con el mismo mecanismo que el panel. No se puede deshacer: confirma la guía antes de llamarlo.

## Listados paginados [#listados-paginados]

GET /account/tracking-list, GET /account/history y GET /account/vouchers devuelven data.items y la paginación del panel en data.meta. El historial y los comprobantes aceptan los mismos filtros. Los comprobantes traen ticket_url para descargar el ticket oficial (ver Rastrear envíos).

## Lista de envíos pendientes [#lista-de-envíos-pendientes]

```http
POST /account/pending-shipments
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Devuelve, tal como los entrega Shalom Pro, los envíos de la cuenta que siguen pendientes (registrados y aún no despachados). Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (en el body). |
| `desde` | No | Opcional: solo pendientes registrados desde esta fecha (YYYY-MM-DD). Recomendado: la respuesta y el consumo bajan mucho. |
| `hasta` | No | Opcional: solo pendientes registrados hasta esta fecha (YYYY-MM-DD). |
| `solo_guias` | No | Opcional (true): devuelve \{ total, truncado, guias: [\{ guia, ose_id, codigo, fecha \}] \} en vez del detalle completo. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/pending-shipments" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "desde": "2026-11-17"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/pending-shipments", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "desde": "2026-11-17"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/pending-shipments");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "desde" => "2026-11-17"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/pending-shipments",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "desde": "2026-11-17"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "0": {
    "id": 19763252,
    "shipping_date": "2026-11-09",
    "service_order_id_empresarial": 106620694,
    "service_order_guia_empresarial": "103149077",
    "code_service_order_empresarial": "HC7N",
    "service_order_serie_empresarial": "V207",
    "origin_station": { "ter_id": 7, "name": "AGENCIA ORIGEN", "abbreviation": "ORI" },
    "destination_station": { "ter_id": 582, "name": "AGENCIA DESTINO", "abbreviation": "DES" },
    "recipient_id": 23958
  }
}
```

<Callout>
Espejo de la vista de envíos pendientes de Shalom Pro. Ojo: es un objeto con claves "0", "1", …, no un array; recórrelo con Object.values() (JavaScript) o .values() (Python). service_order_id_empresarial es el ose_id y service_order_guia_empresarial el número de guía que acepta DELETE /account/pending-shipments. Se devuelven hasta 5000 pendientes; las cabeceras x-pending-total (total real en Shalom), x-pending-returned y x-pending-truncated (true si hay más de los devueltos) permiten comprobarlo. Si usas la lista para saber qué guías ya entregaste en agencia, filtra con desde y anula las guías que reemplazaste: así la lista se mantiene corta. Cualquier fallo, incluida una sesión que no se pudo reconectar, responde 500 con "Ocurrió un error al listar los envíos pendientes (Mirror)."
</Callout>

## Anular un envío pendiente [#anular-un-envío-pendiente]

```http
DELETE /account/pending-shipments
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Anula un envío que todavía está pendiente en la cuenta Shalom Pro, con el mismo mecanismo que el panel. No se puede deshacer. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (en el body). |
| `guia` | No | Número de guía (string de 8 a 12 dígitos). Envía guia u ose_id. |
| `ose_id` | No | ose_id del envío pendiente (string o número). Envía guia u ose_id. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X DELETE "https://api.shalom-api.lat/account/pending-shipments" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "103149077"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/pending-shipments", {
  method: "DELETE",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "103149077"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/pending-shipments");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "guia" => "103149077"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.delete(
    "https://api.shalom-api.lat/account/pending-shipments",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "guia": "103149077"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Envío pendiente eliminado",
  "guia": "103149077",
  "ose_id": 106620694,
  "resultado": { "...": "respuesta de Shalom Pro tal cual" }
}
```

<Callout>
Primero se busca el envío en tus pendientes. Si no está ahí responde 404; si Shalom no confirma la anulación, 409 con su mensaje; si Shalom no devuelve la lista de pendientes, 502.
</Callout>

## Seguimiento de envíos (en tránsito) [#seguimiento-de-envíos-en-tránsito]

```http
GET /account/tracking-list
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Espejo de la vista "Seguimiento de envíos" de Shalom Pro: envíos en tránsito, paginados, con guía, código, fecha, origen y destino. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (querystring). |
| `page` | No | Página (entero, por defecto 1). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/account/tracking-list?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/tracking-list?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1", {
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
$ch = curl_init("https://api.shalom-api.lat/account/tracking-list?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1");
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
    "https://api.shalom-api.lat/account/tracking-list?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Lista de envíos en tránsito.",
  "data": {
    "items": [
      {
        "id": 91230250,
        "guideNumber": "12345678",
        "code": "ABCD",
        "shippingDate": "2026-11-10 10:00:00",
        "origin": { "id": 159, "name": "Agencia Origen" },
        "destination": { "id": 497, "name": "Agencia Destino" }
      }
    ],
    "meta": { "current_page": 1, "last_page": 3, "per_page": 20, "total": 48 }
  }
}
```

## Historial de envíos entregados [#historial-de-envíos-entregados]

```http
GET /account/history
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Espejo de la vista "Historial de envíos" de Shalom Pro: envíos entregados, paginados, con filtros por fecha, documento, guía y destino. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (querystring). |
| `page` | No | Página (entero, por defecto 1). |
| `from_date` | No | Fecha inicial, YYYY-MM-DD. |
| `to_date` | No | Fecha final, YYYY-MM-DD. |
| `document_type` | No | DNI, RUC o CE. |
| `document_number` | No | Documento del remitente o del destinatario. |
| `order_number` | No | Número de guía. |
| `destination` | No | ID o nombre de la agencia destino. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/account/history?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1&from_date=2026-11-01&to_date=2026-11-15" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/history?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1&from_date=2026-11-01&to_date=2026-11-15", {
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
$ch = curl_init("https://api.shalom-api.lat/account/history?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1&from_date=2026-11-01&to_date=2026-11-15");
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
    "https://api.shalom-api.lat/account/history?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1&from_date=2026-11-01&to_date=2026-11-15",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Historial de envíos entregados.",
  "data": {
    "items": [
      {
        "id": 91234250,
        "serviceOrderIdEmpresarial": 91234250,
        "guideNumber": "12345678",
        "code": "ABCD",
        "series": "V336",
        "shippingDate": "2026-11-10 10:00:00",
        "origin": { "id": 159, "name": "Agencia Origen" },
        "destination": { "id": 497, "name": "Agencia Destino" },
        "receiver": { "name": "CLIENTE DE PRUEBA", "document": "12345678" },
        "packageDelivered": true,
        "price": "12.00",
        "product": { "id": 1096, "name": "PAQUETE XXS" }
      }
    ],
    "meta": { "current_page": 1, "last_page": 44, "per_page": 20, "total": 861 }
  }
}
```

<Callout>
Son los mismos filtros que el panel de Shalom Pro y se envían tal cual.
</Callout>

## Lista de comprobantes [#lista-de-comprobantes]

```http
GET /account/vouchers
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Lista los envíos entregados, ya normalizados, con su ose_id y el enlace para descargar el ticket oficial con GET /track/voucher. Acepta los mismos filtros que el historial. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (querystring). |
| `page` | No | Página (entero, por defecto 1). |
| `from_date` | No | Fecha inicial, YYYY-MM-DD. |
| `to_date` | No | Fecha final, YYYY-MM-DD. |
| `document_type` | No | DNI, RUC o CE. |
| `document_number` | No | Documento del remitente o del destinatario. |
| `order_number` | No | Número de guía. |
| `destination` | No | ID o nombre de la agencia destino. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/account/vouchers?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/vouchers?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1", {
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
$ch = curl_init("https://api.shalom-api.lat/account/vouchers?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1");
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
    "https://api.shalom-api.lat/account/vouchers?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&page=1",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Historial de envíos entregados.",
  "data": {
    "items": [
      {
        "ose_id": 91234250,
        "guia": "12345678",
        "codigo": "ABCD",
        "serie": "V336",
        "fecha": "2026-11-10 10:00:00",
        "producto": "PAQUETE XXS",
        "monto": "12.00",
        "entregado": true,
        "origen": "Agencia Origen",
        "destino": "Agencia Destino",
        "destinatario": { "nombre": "CLIENTE DE PRUEBA", "documento": "12345678" },
        "ticket_path": "/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=91234250",
        "ticket_url": "https://api.shalom-api.lat/track/voucher?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&ose_id=91234250"
      }
    ],
    "meta": { "current_page": 1, "last_page": 44, "per_page": 20, "total": 861 }
  }
}
```

<Callout>
ticket_url apunta al ticket oficial en PDF; agrega &format=image para JPEG. Descargarlo es otra llamada y consume su propia operación.
</Callout>

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | Falta instanceId, falta guia y también ose_id, o una fecha no tiene el formato YYYY-MM-DD. |
| `403` | El instanceId no existe o no pertenece a tu API key. |
| `404` | La guía o el ose_id no está en tus envíos pendientes (DELETE). |
| `409` | Shalom no confirmó la anulación (DELETE), o la instancia perdió la sesión de Shalom Pro (listados). |
| `500` | Falló el listado de pendientes o la anulación por un error no previsto. |
| `502` | Shalom no respondió el listado. Reintenta en unos minutos. |

## Relacionado [#relacionado]

<Cards>
<Card title="Crear envíos en Shalom Pro" href="/docs/crear-envio" />

<Card title="Gestionar una guía ya creada" href="/docs/autogestiones" />

<Card title="Ticket, rótulo y rastreo" href="/docs/tracking" />
</Cards>