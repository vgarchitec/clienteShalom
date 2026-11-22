# Autogestiones: gestionar una guía ya creada (https://docs.shalom-api.lat/docs/autogestiones)

Una guía ya creada todavía se puede gestionar: cambiar la clave de recojo, cambiar el contacto, cambiar el destino, devolver la mercadería, retener o liberar la carga y pedir reparto a domicilio. Todo usa el módulo de autogestión de Shalom Pro y requiere una instancia con la sesión iniciada.

## Con código de seguridad, en cualquier estado [#con-código-de-seguridad-en-cualquier-estado]

Si envías el código de seguridad de la guía (campo codigo), la autogestión funciona con el envío en ruta, en destino o entregado: la API verifica la orden con el mismo módulo del panel y es Shalom quien decide qué se permite según el estado. Por ejemplo, cambiar el destino de una guía entregada se rechaza con el mensaje de Shalom (409).

Sin el código, la operación solo aplica a los envíos pendientes de la cuenta. Si la guía ya salió, responde 404 y te pide el código.

## Dos pasos, un solo cobro [#dos-pasos-un-solo-cobro]

Salvo el cambio de clave, toda autogestión se confirma con un código que envía Shalom. Primero llamas a POST /shipments/self-management con lo que quieres cambiar: la API valida los datos, guarda el cambio y pide el código. Te devuelve un challengeId que dura 10 minutos.

Después llamas a POST /shipments/self-management/confirm con ese challengeId y el código recibido. Solo ahí se aplica el cambio, y solo ese paso consume una operación.

## Qué cambia cada tipo [#qué-cambia-cada-tipo]

cambiar_contacto cambia el contacto autorizado (documento en destinatario). cambio_destino lleva la guía a otra agencia. devolucion_mercaderia la devuelve a la agencia que indiques. retencion_entrega retiene la entrega y liberacion_carga libera una carga retenida. reparto_domicilio pide la entrega en una dirección: primero resuelve el ubigeo con GET /shipments/home-delivery/ubigeo y envía las tarifas de reparto y de etiqueta que cobra Shalom.

## Cambiar la clave de recojo [#cambiar-la-clave-de-recojo]

```http
POST /shipments/pickup-code
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Cambia la clave de recojo (4 dígitos) de una guía en un solo paso, sin código de verificación. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `guia` | Sí | Número de guía (6 a 12 dígitos). |
| `codigo` | No | Código de seguridad de la guía (4 a 8 letras o números). Recomendado: con él funciona con la guía en ruta, en destino o entregada; sin él, solo con envíos pendientes. |
| `clave` | Sí | Nueva clave de 4 dígitos. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/shipments/pickup-code" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "77175223",
  "codigo": "9MPJ",
  "clave": "2008"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/shipments/pickup-code", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "77175223",
  "codigo": "9MPJ",
  "clave": "2008"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/shipments/pickup-code");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "guia" => "77175223", "codigo" => "9MPJ", "clave" => "2008"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/shipments/pickup-code",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "guia": "77175223", "codigo": "9MPJ", "clave": "2008"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "tipo": "cambio_clave",
  "guia": "77175223",
  "message": "Clave de recojo actualizada",
  "resultado": { "...": "respuesta de Shalom Pro tal cual" }
}
```

<Callout>
Shalom valida los datos del envío y, si rechaza el cambio por una regla propia (por ejemplo, si tu cuenta no es la remitente), la API responde 409 o 400 con su mensaje.
</Callout>

## Iniciar una autogestión (envía el código) [#iniciar-una-autogestión-envía-el-código]

```http
POST /shipments/self-management
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Paso 1: valida los datos, guarda el cambio que quieres hacer y pide a Shalom que envíe el código de verificación por SMS o email. Devuelve un challengeId de un solo uso que dura 10 minutos. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `guia` | Sí | Número de guía (6 a 12 dígitos). |
| `tipo` | Sí | cambiar_contacto, cambio_destino, devolucion_mercaderia, retencion_entrega, liberacion_carga o reparto_domicilio. |
| `telefono` | Sí | Celular de 9 dígitos que empieza con 9. Recibe el código por SMS y Shalom lo usa para validarlo. |
| `codigo` | No | Código de seguridad de la guía (4 a 8 letras o números). Recomendado: con él la guía se puede operar en ruta, en destino o entregada; sin él, solo si está en tus envíos pendientes. |
| `canal` | No | sms (por defecto) o email. |
| `email` | No | Correo que recibe el código. Obligatorio con canal = email. |
| `destinatario` | No | Documento del destinatario (7 a 12 dígitos). Obligatorio en cambiar_contacto. En cambio_destino y devolucion_mercaderia se toma del envío si no lo envías; si no se encuentra, responde 400. |
| `destino` | No | ter_id o nombre de la agencia destino. Obligatorio en cambio_destino y devolucion_mercaderia. |
| `direccion` | No | Dirección de entrega (mínimo 5 caracteres). Obligatoria en reparto_domicilio. |
| `dep_id` | No | Departamento de entrega (entero). Obligatorio en reparto_domicilio; sale de GET /shipments/home-delivery/ubigeo. |
| `prov_id` | No | Provincia de entrega (entero). Obligatoria en reparto_domicilio. |
| `dist_id` | No | Distrito de entrega (entero). Obligatorio en reparto_domicilio. |
| `rate` | No | Tarifa de reparto que cobra Shalom (número, 0 o más). Obligatoria en reparto_domicilio. |
| `label_rate` | No | Tarifa de etiqueta (número, 0 o más). Obligatoria en reparto_domicilio. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/shipments/self-management" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "77175223",
  "codigo": "9MPJ",
  "tipo": "cambio_destino",
  "telefono": "987654321",
  "destino": 582
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/shipments/self-management", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "guia": "77175223",
  "codigo": "9MPJ",
  "tipo": "cambio_destino",
  "telefono": "987654321",
  "destino": 582
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/shipments/self-management");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "guia" => "77175223", "codigo" => "9MPJ", "tipo" => "cambio_destino", "telefono" => "987654321", "destino" => 582]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/shipments/self-management",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "guia": "77175223", "codigo": "9MPJ", "tipo": "cambio_destino", "telefono": "987654321", "destino": 582},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "challengeId": "83e9cc9607eb9475e7a96f6be742e0a9",
  "expiresAt": "2026-11-11T09:02:46.299Z",
  "tipo": "cambio_destino",
  "guia": "77175223",
  "canal": "sms",
  "mensaje": "Te enviamos un código por SMS. Confírmalo en /shipments/self-management/confirm."
}
```

<Callout>
Máximo 5 solicitudes de código por instancia cada 10 minutos; la sexta responde 429.
</Callout>

## Confirmar la autogestión con el código [#confirmar-la-autogestión-con-el-código]

```http
POST /shipments/self-management/confirm
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Paso 2: valida el código contra Shalom y aplica el cambio guardado en el paso 1. Máximo 5 intentos por código. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | La misma instancia del paso 1. |
| `challengeId` | Sí | Identificador que devolvió el paso 1. |
| `clave` | Sí | Código de 4 a 8 letras o números recibido por SMS o email (ej. M9M7NT). Se ignoran espacios y guiones. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/shipments/self-management/confirm" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "challengeId": "83e9cc9607eb9475e7a96f6be742e0a9",
  "clave": "M9M7NT"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/shipments/self-management/confirm", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "challengeId": "83e9cc9607eb9475e7a96f6be742e0a9",
  "clave": "M9M7NT"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/shipments/self-management/confirm");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "challengeId" => "83e9cc9607eb9475e7a96f6be742e0a9", "clave" => "M9M7NT"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/shipments/self-management/confirm",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "challengeId": "83e9cc9607eb9475e7a96f6be742e0a9", "clave": "M9M7NT"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "tipo": "cambio_destino",
  "guia": "77175223",
  "message": "Destino actualizado en Shalom",
  "resultado": { "...": "respuesta de Shalom Pro tal cual" }
}
```

<Callout>
Un código incorrecto responde 400 y suma un intento; al sexto intento el challenge se borra (429) y hay que pedir otro código. Si Shalom rechaza el cambio, responde 400 con su mensaje.
</Callout>

## Historial de autogestiones [#historial-de-autogestiones]

```http
GET /shipments/self-management
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Lista las autogestiones hechas en la cuenta, con filtros por guía, tipo y fechas. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `guia` | No | Filtra por número de guía. |
| `tipo` | No | Filtra por uno de los tipos de autogestión. |
| `desde` | No | Fecha inicial (YYYY-MM-DD). |
| `hasta` | No | Fecha final (YYYY-MM-DD). |
| `page` | No | Página (entero, por defecto 1). |
| `per_page` | No | Resultados por página (1 a 50, por defecto 20). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/shipments/self-management?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&per_page=5" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/shipments/self-management?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&per_page=5", {
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
$ch = curl_init("https://api.shalom-api.lat/shipments/self-management?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&per_page=5");
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
    "https://api.shalom-api.lat/shipments/self-management?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc&per_page=5",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "data": [
    {
      "id": 47378,
      "fecha_autogestion": "2026-11-10T08:53:17.000000Z",
      "autogestion": "cambio_clave",
      "codigo_guia": "P9JP"
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 1 }
}
```

## Ubigeo para reparto a domicilio [#ubigeo-para-reparto-a-domicilio]

```http
GET /shipments/home-delivery/ubigeo
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Sin dep_id devuelve departamentos; con dep_id, sus provincias; con dep_id y prov_id, sus distritos. Son los IDs que pide reparto_domicilio. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia de la cuenta Shalom Pro. |
| `dep_id` | No | ID del departamento (entero). |
| `prov_id` | No | ID de la provincia (entero). Requiere dep_id. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/shipments/home-delivery/ubigeo?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/shipments/home-delivery/ubigeo?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc", {
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
$ch = curl_init("https://api.shalom-api.lat/shipments/home-delivery/ubigeo?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc");
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
    "https://api.shalom-api.lat/shipments/home-delivery/ubigeo?instanceId=2e656a02-7e37-4573-9d68-e76740d337dc",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "data": [ { "id": 15, "name": "LIMA" } ],
  "nivel": "departamentos"
}
```

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | Falta un campo del tipo elegido o tiene un formato inválido (guía, teléfono, email, documento, código), el código de verificación es incorrecto, o Shalom rechazó el cambio con su mensaje. |
| `403` | El instanceId no existe o no pertenece a tu API key. |
| `404` | No se pudo ubicar la guía (envía codigo si ya salió de pendientes), o el challenge no existe, expiró o es de otra instancia. |
| `409` | Shalom no permite esa autogestión para la guía en su estado actual, o la instancia perdió la sesión de Shalom Pro. |
| `429` | Más de 5 solicitudes de código en 10 minutos, o más de 5 intentos con el mismo código. |
| `502` | Shalom no respondió o rechazó el envío del código. Reintenta. |
| `503` | El servicio de autogestión no está disponible en este momento. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Puedo cambiar el destino de una guía ya creada?">
Sí, con el tipo cambio_destino: inicias la autogestión con la agencia destino y el teléfono, y confirmas con el código que llega por SMS o email.
</Accordion>

<Accordion title="¿Por qué me pide un código si yo ya estoy autenticado?">
Porque Shalom exige confirmar con el contacto del envío. El código lo genera Shalom y llega al teléfono o correo que indiques; sin él la operación no se aplica.
</Accordion>

<Accordion title="¿Puedo pedir el código por correo en vez de SMS?">
Sí, con canal "email" y el correo en el campo email. El teléfono de 9 dígitos sigue siendo obligatorio.
</Accordion>

<Accordion title="¿Qué pasa si el código se vence?">
El challenge dura 10 minutos y admite 5 intentos. Si se vence, vuelve a llamar a POST /shipments/self-management para pedir uno nuevo.
</Accordion>

<Accordion title="¿Me avisan cuando la guía llega a la agencia de destino?">
Eso es otro flujo. El estado de la guía te llega por webhook (statusCode AT_DESTINATION); las autogestiones sirven para cambiar algo de una guía ya creada.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Pendientes, historial y comprobantes" href="/docs/operaciones" />

<Card title="Crear envíos en Shalom Pro" href="/docs/crear-envio" />

<Card title="Recibir cambios de estado" href="/docs/webhooks" />
</Cards>