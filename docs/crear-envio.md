# Crear envíos en Shalom Pro (https://docs.shalom-api.lat/docs/crear-envio)

Estos endpoints crean guías reales en tu cuenta de Shalom Pro, así que requieren una instancia con la sesión iniciada y su instanceId en el body. El registro individual resuelve el destinatario, el producto y el costo; el registro en lote hace lo mismo para una lista de envíos.

## Antes de crear una guía [#antes-de-crear-una-guía]

Necesitas una instancia con la sesión de Shalom Pro iniciada (ver Instancias), el ter_id de las agencias de origen y destino (GET /agencies/search) y los datos del destinatario. Para saber el costo antes de registrar, cotiza con POST /account/quote y el mismo instanceId: usa la misma tarifa.

## Remitente y destinatario [#remitente-y-destinatario]

El remitente siempre es el titular de la cuenta Shalom Pro: la API lo toma del perfil (POST /account/get-user) y no se puede cambiar por envío.

El destinatario se identifica por su documento. Si ya está registrado en tu cuenta se usa tal cual; si no, se registra en el momento. En el registro individual se registra con name, firstname, lastname y phone. En el lote, con recipientPhone y el nombre que devuelve la consulta de DNI.

## Producto, costo y coberturas [#producto-costo-y-coberturas]

Con content, la API resuelve el producto y calcula el costo con la tarifa de tu cuenta. Los productos fijos (SOBRE y PAQUETE XXS a L) tienen tarifa por unidad. OTRA MEDIDA se cotiza por volumen y exige ancho, alto y largo (máx. 1.5 m por lado) y peso (máx. 500 kg).

La garantía asegura el valor declarado (máx. 40,000) y la API calcula su costo con las reglas del panel de Shalom: S/ 5 fijos para SOBRE y PAQUETE XXS a M con valor declarado de hasta 1,000, y un porcentaje del valor (por lo general 0.5%) en el resto de casos. El cobro seguro cobra al destinatario el monto que indiques.

## Una guía o un lote [#una-guía-o-un-lote]

Para pedidos sueltos usa POST /account/register. Para el cierre del día, POST /account/register-bulk registra la lista completa en una llamada y te dice cuáles se crearon y cuáles no.

Guarda siempre tu propio identificador de pedido junto al guia y al ose_id que devuelve Shalom. Si una respuesta se pierde por un timeout, revisa tus envíos pendientes (POST /account/pending-shipments) antes de reintentar, para no duplicar la guía.

## Registrar un envío individual [#registrar-un-envío-individual]

```http
POST /account/register
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Crea una guía real en tu cuenta de Shalom Pro. El remitente sale del perfil de la cuenta; el destinatario se busca por documento y, si no existe, se registra con sus datos. Con content, la API resuelve el producto y calcula el costo con la tarifa de tu cuenta. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `origen` | Sí | ter_id de la agencia de origen (entero). Obtenlo con GET /agencies/search. |
| `destino` | Sí | ter_id de la agencia de destino (entero), su nombre, o un string que empieza con "0" para envío aéreo (ej. "052"). |
| `documento` | Sí | DNI o carnet de extranjería del destinatario. Si no está registrado en tu cuenta, se registra con name, firstname, lastname y phone. |
| `name` | Sí | Nombres del destinatario. |
| `firstname` | Sí | Apellido paterno del destinatario. |
| `lastname` | Sí | Apellido materno del destinatario. |
| `phone` | Sí | Celular del destinatario (número entero). |
| `content` | No | Producto, tal como lo lista GET /account/products (SOBRE, PAQUETE XXS, XS, S, M, L u OTRA MEDIDA). Con él la API calcula el costo; con OTRA MEDIDA envía además ancho, alto, largo y peso. |
| `cantidad` | No | Cantidad de bultos (entero, por defecto 1). El costo de los productos fijos se multiplica por la cantidad; el de OTRA MEDIDA no. |
| `ancho` | No | Ancho en metros (máx. 1.5). Obligatorio con OTRA MEDIDA. Por defecto 0.10. |
| `alto` | No | Alto en metros (máx. 1.5). Obligatorio con OTRA MEDIDA. Por defecto 0.10. |
| `largo` | No | Largo en metros (máx. 1.5). Obligatorio con OTRA MEDIDA. Por defecto 0.15. |
| `peso` | No | Peso en kg (máx. 500). Obligatorio con OTRA MEDIDA. Por defecto 0.25. |
| `clave` | No | Clave de recojo de 4 dígitos, no consecutiva (1234 o 4321 se rechazan). Si no la envías, se usa 8002. |
| `declaracion_jurada` | No | Para envíos aéreos: '' (por defecto), 'Artículos de uso personal', 'Documentos', 'Ropa' o 'Electrodomésticos'. |
| `aereo` | No | 0 (terrestre, por defecto) o 1 (aéreo). Si destino es un string que empieza con "0", se pone en 1. |
| `costo` | No | Costo manual. Se ignora si envías content, porque la API lo calcula. |
| `deseo_garantia` | No | Boolean. Activa la garantía (seguro de la mercancía). Alias garantia. |
| `garantia_monto` | No | Valor declarado a asegurar (mayor a 0, máx. 40,000). Obligatorio con deseo_garantia. Alias garantia_costo. |
| `cobro_seguro` | No | Boolean. Activa el cobro seguro (cobro contra entrega). Alias servicio_cobranza. |
| `monto_cobro_seguro` | No | Monto a cobrar al destinatario (mayor a 0). Obligatorio con cobro_seguro. Alias servicio_cobranza_costo. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/register" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "origen": 7,
  "destino": 582,
  "content": "PAQUETE XS",
  "cantidad": 1,
  "documento": "12345678",
  "name": "CLIENTE",
  "firstname": "DE",
  "lastname": "PRUEBA",
  "phone": 987654321,
  "clave": "2580",
  "deseo_garantia": true,
  "garantia_monto": 800,
  "cobro_seguro": true,
  "monto_cobro_seguro": 150
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/register", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "origen": 7,
  "destino": 582,
  "content": "PAQUETE XS",
  "cantidad": 1,
  "documento": "12345678",
  "name": "CLIENTE",
  "firstname": "DE",
  "lastname": "PRUEBA",
  "phone": 987654321,
  "clave": "2580",
  "deseo_garantia": true,
  "garantia_monto": 800,
  "cobro_seguro": true,
  "monto_cobro_seguro": 150
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/register");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "origen" => 7, "destino" => 582, "content" => "PAQUETE XS", "cantidad" => 1, "documento" => "12345678", "name" => "CLIENTE", "firstname" => "DE", "lastname" => "PRUEBA", "phone" => 987654321, "clave" => "2580", "deseo_garantia" => true, "garantia_monto" => 800, "cobro_seguro" => true, "monto_cobro_seguro" => 150]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/register",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "origen": 7, "destino": 582, "content": "PAQUETE XS", "cantidad": 1, "documento": "12345678", "name": "CLIENTE", "firstname": "DE", "lastname": "PRUEBA", "phone": 987654321, "clave": "2580", "deseo_garantia": True, "garantia_monto": 800, "cobro_seguro": True, "monto_cobro_seguro": 150},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Orden de envío creada con exito!",
  "data": {
    "guia": 103149077,
    "codigo": "HC7N",
    "serie": "V207",
    "ose_id": 106620694,
    "precio": 8
  }
}
```

<Callout>
La respuesta es la de Shalom Pro tal cual. guia y codigo son el orderNumber y el orderCode de POST /track; ose_id sirve para el ticket, el rótulo y para anular. Si Shalom rechaza el registro (por ejemplo, por una clave consecutiva), la API responde 400 con su mismo cuerpo \{ success: false, message \}. tipo_pago siempre es REMITENTE.
</Callout>

## Registrar envíos en lote [#registrar-envíos-en-lote]

```http
POST /account/register-bulk
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Registra una lista de envíos en tu cuenta Shalom Pro, uno tras otro. Un envío que falla no detiene los demás. Consume 1 operación por llamada.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `shipments` | Sí | Lista de envíos (mínimo 1). Cada uno requiere recipientDoc (DNI o CE), recipientPhone, origin y destination (ter_id o nombre de agencia, como string) y content (ver GET /account/products). |
| `securityCode` | No | Clave de recojo de 4 dígitos para todos los envíos del lote, no consecutiva. Si no la envías, se usa 8002. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/register-bulk" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "shipments": [
    {
      "recipientDoc": "12345678",
      "recipientPhone": "987654321",
      "contactDoc": "87654321",
      "origin": "7",
      "destination": "582",
      "content": "PAQUETE XS",
      "quantity": "1"
    },
    {
      "recipientDoc": "11223344",
      "recipientPhone": "912345678",
      "origin": "7",
      "destination": "582",
      "content": "OTRA MEDIDA",
      "width": "0.40",
      "height": "0.30",
      "length": "0.50",
      "weight": "3"
    }
  ],
  "securityCode": "5858"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/register-bulk", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "shipments": [
    {
      "recipientDoc": "12345678",
      "recipientPhone": "987654321",
      "contactDoc": "87654321",
      "origin": "7",
      "destination": "582",
      "content": "PAQUETE XS",
      "quantity": "1"
    },
    {
      "recipientDoc": "11223344",
      "recipientPhone": "912345678",
      "origin": "7",
      "destination": "582",
      "content": "OTRA MEDIDA",
      "width": "0.40",
      "height": "0.30",
      "length": "0.50",
      "weight": "3"
    }
  ],
  "securityCode": "5858"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/register-bulk");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "shipments" => [["recipientDoc" => "12345678", "recipientPhone" => "987654321", "contactDoc" => "87654321", "origin" => "7", "destination" => "582", "content" => "PAQUETE XS", "quantity" => "1"], ["recipientDoc" => "11223344", "recipientPhone" => "912345678", "origin" => "7", "destination" => "582", "content" => "OTRA MEDIDA", "width" => "0.40", "height" => "0.30", "length" => "0.50", "weight" => "3"]], "securityCode" => "5858"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/register-bulk",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "shipments": [{"recipientDoc": "12345678", "recipientPhone": "987654321", "contactDoc": "87654321", "origin": "7", "destination": "582", "content": "PAQUETE XS", "quantity": "1"}, {"recipientDoc": "11223344", "recipientPhone": "912345678", "origin": "7", "destination": "582", "content": "OTRA MEDIDA", "width": "0.40", "height": "0.30", "length": "0.50", "weight": "3"}], "securityCode": "5858"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Bulk registration completed: 2 exitosos, 0 fallidos.",
  "summary": { "total": 2, "success": 2, "failed": 0 },
  "results": [
    {
      "index": 0,
      "success": true,
      "recipientDoc": "12345678",
      "data": { "guia": 103149077, "codigo": "HC7N", "ose_id": 106620694, "...": "igual que data en POST /account/register" }
    },
    {
      "index": 1,
      "success": true,
      "recipientDoc": "11223344",
      "data": { "guia": 103149078, "codigo": "K2PD", "ose_id": 106620695, "...": "igual que data en POST /account/register" }
    }
  ]
}
```

<Callout>
Responde 200 aunque fallen envíos: success es true solo si todos se crearon, y cada envío fallido trae success en false y error con el motivo en lugar de data. Revisa results por index y reintenta solo los fallidos, porque reenviar el lote completo duplica las guías ya creadas. Campos opcionales por envío: contactDoc (contacto que también puede recoger), quantity, width, height, length y weight (en metros y kg; obligatorios con OTRA MEDIDA), deseo_garantia + garantia_monto y cobro_seguro + monto_cobro_seguro. Si el destinatario no está registrado, se registra con recipientPhone y el nombre que devuelve la consulta de DNI. Un destination que empieza con "0" se registra como aéreo.
</Callout>

## Catálogo de contenidos [#catálogo-de-contenidos]

```http
GET /account/products
```

Productos que acepta content al registrar, con el productId interno de Shalom y las medidas de cada caja en centímetros. No requiere instancia ni consume cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/account/products" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/products", {
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
$ch = curl_init("https://api.shalom-api.lat/account/products");
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
    "https://api.shalom-api.lat/account/products",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "total": 7,
  "data": [
    { "content": "SOBRE", "productId": 3, "name": "Sobre", "detail": "Documentos simples en sobre manila / Tamaño A4" },
    { "content": "PAQUETE XXS", "productId": 1096, "name": "Caja Paquete XXS", "detail": "15 x 10 x 10" },
    { "content": "PAQUETE XS", "productId": 1090, "name": "Caja Paquete XS", "detail": "15 x 20 x 12" },
    { "content": "PAQUETE S", "productId": 5, "name": "Caja Paquete S", "detail": "20 x 30 x 12" },
    { "content": "PAQUETE M", "productId": 1093, "name": "Caja Paquete M", "detail": "24 x 30 x 20" },
    { "content": "PAQUETE L", "productId": 2, "name": "Caja Paquete L", "detail": "42 x 30 x 23" },
    { "content": "OTRA MEDIDA", "productId": 4, "name": "Otra Medida", "detail": "Medidas personalizadas (ancho x alto x largo)" }
  ],
  "allowedContents": ["SOBRE", "PAQUETE XXS", "PAQUETE XS", "PAQUETE S", "PAQUETE M", "PAQUETE L", "CAJA", "BULTO", "OTRA MEDIDA"]
}
```

<Callout>
Usa content tal cual en /account/register o /account/register-bulk. allowedContents incluye CAJA y BULTO, pero no tienen producto asociado en data: la API no les calcula el costo y en el lote fallan con "Producto no reconocido". Usa los siete de data.
</Callout>

## Perfil del remitente [#perfil-del-remitente]

```http
POST /account/get-user
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Devuelve los datos del titular de la cuenta Shalom Pro (el remitente de tus guías), tal como los entrega Shalom Pro. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (en el body). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/get-user" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/get-user", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/get-user");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/get-user",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "id": 1252462,
  "name": "Tienda Ejemplo",
  "email": "ventas@ejemplo.com",
  "status": 1,
  "has_selfmanagement_history": true,
  "sender": {
    "type": "person",
    "id": 2692645,
    "name": "TITULAR DE PRUEBA",
    "document": "12345678",
    "document_type": "dni",
    "email": null,
    "phone": 987654321
  }
}
```

<Callout>
El resultado se guarda 24 horas por instancia y se renueva al iniciar o cerrar sesión.
</Callout>

## Buscar una persona registrada (DNI, RUC o CE) [#buscar-una-persona-registrada-dni-ruc-o-ce]

```http
POST /account/person/search
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Busca entre las personas y empresas ya registradas en tu cuenta Shalom Pro y devuelve nombre, apellidos, teléfono, correo y dirección para autocompletar formularios. No consulta RENIEC. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro (en el body). |
| `document` | Sí | Solo dígitos, de 6 a 12. DNI de 8, RUC de 11 o CE. |
| `type_document` | No | DNI, RUC o CE. Si no lo envías se deduce por la longitud (8 = DNI, 11 = RUC, otro = CE). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/person/search" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "document": "12345678"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/person/search", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "document": "12345678"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/person/search");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "document" => "12345678"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/person/search",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "document": "12345678"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Entidad encontrada",
  "data": {
    "type_document": "DNI",
    "document": "12345678",
    "name": "CLIENTE",
    "lastname": "DE",
    "surname": "PRUEBA",
    "full_name": "CLIENTE DE PRUEBA",
    "phone": "987654321",
    "email": "cliente@ejemplo.com",
    "address": "",
    "id": 123456
  }
}
```

<Callout>
404 si el documento no está registrado en esa cuenta; 400 si el DNI no tiene 8 dígitos o el RUC no tiene 11.
</Callout>

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | Falta un campo obligatorio o tiene otro tipo (por ejemplo, origen no es un entero), una medida o la garantía están fuera de rango, no se encontró la agencia por nombre, o Shalom rechazó el registro (el cuerpo trae su \{ success: false, message \}). |
| `403` | El instanceId no existe o no pertenece a tu API key. |
| `404` | El documento no está registrado en la cuenta (person/search). |
| `409` | La instancia perdió la sesión de Shalom Pro (person/search). |
| `500` | En register, register-bulk y get-user: falló Shalom Pro, el cálculo de la tarifa, el registro del destinatario o la sesión de la instancia (por ejemplo, sin credenciales guardadas). El cuerpo no trae el detalle: revisa la sesión con POST /instances/status y reintenta. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Puedo crear guías solo con la API key?">
No. Crear envíos requiere una instancia con la sesión de Shalom Pro iniciada, con las credenciales de la cuenta que despacha.
</Accordion>

<Accordion title="¿Cuántos envíos puedo crear de una sola vez?">
POST /account/register-bulk no fija un máximo y consume una operación por llamada. Los envíos se registran uno tras otro, así que un lote grande tarda más.
</Accordion>

<Accordion title="¿Cómo calculo el costo antes de crear la guía?">
Con POST /account/quote y el mismo instanceId. Usa la misma tarifa que se cobra al registrar.
</Accordion>

<Accordion title="¿Qué hago si se cayó la sesión de Shalom Pro?">
Si la instancia tiene credenciales guardadas se reconecta sola en la siguiente operación. Si no, vuelve a iniciarla con POST /instances/login y reintenta.
</Accordion>

<Accordion title="¿Puedo validar el DNI del destinatario antes de registrar?">
Sí, con GET /account/dni/\{dni\} (ver Cotizar tarifas y consultar DNI) o, si ya le enviaste antes, con POST /account/person/search.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Conectar tu cuenta Shalom Pro" href="/docs/instancias" />

<Card title="Cotizar antes de crear" href="/docs/cotizar" />

<Card title="Pendientes, historial y comprobantes" href="/docs/operaciones" />

<Card title="Rastrear la guía creada" href="/docs/tracking" />
</Cards>