# Agencias y cobertura (https://docs.shalom-api.lat/docs/agencias)

El catálogo de agencias se refresca desde Shalom cada 3 horas e incluye dirección, teléfono, horarios, coordenadas y si cada agencia atiende envíos aéreos. Su ter_id es el que se usa como origen y destino al cotizar y al crear guías.

## Listado o búsqueda [#listado-o-búsqueda]

GET /agencies devuelve el catálogo completo, con un filtro opcional por texto. GET /agencies/search suma filtros por departamento, provincia y servicio aéreo, y el orden por cercanía.

## Ordenar por cercanía [#ordenar-por-cercanía]

Con near=lat,lng el resultado viene ordenado por distancia a ese punto y cada agencia trae distancia_km. Con radius_km recortas el radio. Es lo que necesitas para mostrar la agencia más cercana en tu checkout.

## La versión pública no pide key [#la-versión-pública-no-pide-key]

GET /public/agencies y GET /public/agencies/search responden sin API key. Sirven para ver el formato real antes de integrar y alimentan la demo y el mapa del sitio.

## Guarda el ter_id [#guarda-el-ter_id]

Para crear un envío necesitas el ter_id de las agencias de origen y destino. Guárdalo junto al pedido al cotizar: así no tienes que volver a buscarlo al registrar la guía.

## Listar agencias [#listar-agencias]

```http
GET /agencies
```

Devuelve el catálogo completo de agencias, sin las listas de rutas aéreas de origen y destino. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `q` | No | Texto para filtrar por departamento, provincia, zona o ter_zona (contiene, sin distinguir mayúsculas). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/agencies?q=lima" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/agencies?q=lima", {
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
$ch = curl_init("https://api.shalom-api.lat/agencies?q=lima");
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
    "https://api.shalom-api.lat/agencies?q=lima",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Lista de agencias filtrada.",
  "total": 26,
  "query": "lima",
  "data": [
    {
      "ter_id": 392,
      "ter_abrebiatura": "MLGVLL",
      "zona": "CERCADO LIMA",
      "ter_zona": "LIMA OESTE 2",
      "provincia": "LIMA",
      "departamento": "LIMA",
      "latitud": "-12.04638719585",
      "longitud": "-77.049473000003",
      "direccion": "JR. PRESBÍTERO GARCÍA VILLÓN NRO. 560 CERCADO LIMA - LIMA",
      "telefono": "(01) 500 7878",
      "hora_atencion": "LUNES A VIERNES - 8:00 AM A 8:00 PM",
      "hora_domingo": "",
      "estadoAgencia": "ATENDIENDO EN ESTE MOMENTO",
      "nombre": "LIMA / LIMA / CERCADO LIMA / MALVINAS - JR. GARCIA VILLÓN",
      "lugar_over": "MALVINAS - JR. GARCIA VILLÓN",
      "ter_aereo": 1,
      "dep_id": 15,
      "prov_id": 1,
      "dist_id": 1,
      "ubi_id": 150101,
      "...": "más campos del catálogo de Shalom"
    }
  ]
}
```

<Callout>
Sin q, message es "Lista de agencias minimal." y query llega en null.
</Callout>

## Búsqueda avanzada [#búsqueda-avanzada]

```http
GET /agencies/search
```

Filtra por texto libre, departamento, provincia y servicio aéreo, y ordena por cercanía a unas coordenadas. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `q` | No | Texto libre. Busca en departamento, provincia, zona, ter_zona, nombre y dirección. |
| `departamento` | No | Filtra por departamento (contiene el texto). |
| `provincia` | No | Filtra por provincia (contiene el texto). |
| `aereo` | No | true para solo agencias con servicio aéreo (ter_aereo = 1); false para solo terrestres. |
| `near` | No | Coordenadas "lat,lng" (ej. -12.046,-77.043). Ordena del más cercano al más lejano y agrega distancia_km. |
| `radius_km` | No | Radio máximo en kilómetros. Solo se aplica junto con near. |
| `per_page` | No | Máximo de resultados (1 a 500, por defecto 100). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/agencies/search?near=-12.046,-77.043&radius_km=5&per_page=5" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/agencies/search?near=-12.046,-77.043&radius_km=5&per_page=5", {
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
$ch = curl_init("https://api.shalom-api.lat/agencies/search?near=-12.046,-77.043&radius_km=5&per_page=5");
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
    "https://api.shalom-api.lat/agencies/search?near=-12.046,-77.043&radius_km=5&per_page=5",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "total": 26,
  "returned": 5,
  "data": [
    {
      "ter_id": 392,
      "lugar_over": "MALVINAS - JR. GARCIA VILLÓN",
      "departamento": "LIMA",
      "provincia": "LIMA",
      "direccion": "JR. PRESBÍTERO GARCÍA VILLÓN NRO. 560 CERCADO LIMA - LIMA",
      "latitud": "-12.04638719585",
      "longitud": "-77.049473000003",
      "ter_aereo": 1,
      "distancia_km": 0.71,
      "...": "los mismos campos que GET /agencies"
    }
  ]
}
```

<Callout>
total es la cantidad de resultados antes del límite y returned la que se devolvió. distancia_km solo aparece con near.
</Callout>

## Listado público (sin API key) [#listado-público-sin-api-key]

```http
GET /public/agencies
```

El mismo listado que GET /agencies, sin API key ni consumo de cuota. Es el que usa la demo del sitio.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `q` | No | Texto para filtrar por departamento, provincia, zona o ter_zona. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/public/agencies?q=lima" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/public/agencies?q=lima", {
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
$ch = curl_init("https://api.shalom-api.lat/public/agencies?q=lima");
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
    "https://api.shalom-api.lat/public/agencies?q=lima",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Lista de agencias filtrada.",
  "total": 26,
  "query": "lima",
  "data": [ { "ter_id": 392, "...": "misma estructura que GET /agencies" } ]
}
```

## Búsqueda pública (sin API key) [#búsqueda-pública-sin-api-key]

```http
GET /public/agencies/search
```

La misma búsqueda que GET /agencies/search, con los mismos parámetros, sin API key ni consumo de cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `q` | No | Texto libre. |
| `departamento` | No | Filtra por departamento. |
| `provincia` | No | Filtra por provincia. |
| `aereo` | No | true o false. |
| `near` | No | Coordenadas "lat,lng" para ordenar por cercanía. |
| `radius_km` | No | Radio máximo en kilómetros (con near). |
| `per_page` | No | Máximo de resultados (1 a 500, por defecto 100). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/public/agencies/search?near=-12.046,-77.043&per_page=5" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/public/agencies/search?near=-12.046,-77.043&per_page=5", {
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
$ch = curl_init("https://api.shalom-api.lat/public/agencies/search?near=-12.046,-77.043&per_page=5");
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
    "https://api.shalom-api.lat/public/agencies/search?near=-12.046,-77.043&per_page=5",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "total": 26,
  "returned": 5,
  "data": [ { "ter_id": 392, "distancia_km": 0.71, "...": "misma estructura que GET /agencies/search" } ]
}
```

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | near no tiene el formato lat,lng, aereo no es true ni false, o per_page está fuera del rango 1 a 500. |
| `429` | Superaste el rate limit. En las rutas /public/* el límite se cuenta por IP. |
| `500` | El catálogo de agencias no está disponible en este momento. Reintenta. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Necesito API key para consultar agencias?">
Para GET /agencies y GET /agencies/search sí, y consumen una operación. GET /public/agencies y GET /public/agencies/search responden sin API key y sin consumir cuota.
</Accordion>

<Accordion title="¿Cómo obtengo las agencias más cercanas a un punto?">
Con GET /agencies/search?near=lat,lng, y opcionalmente radius_km para limitar el radio y per_page para cuántas devolver.
</Accordion>

<Accordion title="¿El catálogo incluye coordenadas?">
Sí, cada agencia trae latitud y longitud.
</Accordion>

<Accordion title="¿Cómo sé si una agencia atiende envíos aéreos?">
Con el campo ter_aereo (1 = aéreo) o filtrando con aereo=true en la búsqueda avanzada.
</Accordion>

<Accordion title="¿Cada cuánto se actualiza el catálogo?">
Se refresca desde Shalom cada 3 horas.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Departamentos, provincias y distritos" href="/docs/ubicaciones" />

<Card title="Cotizar un envío entre agencias" href="/docs/cotizar" />
</Cards>

# Ubicaciones (ubigeos) (https://docs.shalom-api.lat/docs/ubicaciones)

Las ubicaciones se arman a partir del catálogo de agencias: departamento, provincia y distrito, cada uno con su ID. Sirven para llenar los selectores de dirección de tu checkout solo con las zonas donde Shalom tiene agencias.

## Se consultan en cascada [#se-consultan-en-cascada]

Primero pides los departamentos, después las provincias de un departamento y después los distritos de una provincia, con los id que devuelve cada nivel. Como salen del catálogo de agencias, que se refresca cada 3 horas, cambian poco y puedes guardarlas de tu lado.

## No confundas ubicación con agencia [#no-confundas-ubicación-con-agencia]

La ubicación es la división territorial; la agencia es el punto físico donde se despacha o se recoge. Para armar la dirección del cliente usas la ubicación; para crear la guía necesitas el ter_id de la agencia (ver Agencias y cobertura).

## Listar departamentos [#listar-departamentos]

```http
GET /locations/departments
```

Departamentos del Perú con al menos una agencia de Shalom, ordenados por nombre. Consume 1 operación.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/locations/departments" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/locations/departments", {
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
$ch = curl_init("https://api.shalom-api.lat/locations/departments");
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
    "https://api.shalom-api.lat/locations/departments",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "items": [
    { "id": 1, "name": "AMAZONAS", "ubi_id": 101 },
    { "id": 15, "name": "LIMA", "ubi_id": 1501 }
  ]
}
```

<Callout>
En los departamentos, ubi_id es id × 100 más el ID de su provincia más baja con agencia; no es el código INEI de 2 dígitos. Usa id para pedir sus provincias.
</Callout>

## Listar provincias de un departamento [#listar-provincias-de-un-departamento]

```http
GET /locations/departments/{depId}/provinces
```

Provincias del departamento con al menos una agencia de Shalom, ordenadas por nombre. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `{depId}` | Sí | ID del departamento (entero), ej. 15. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/locations/departments/15/provinces" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/locations/departments/15/provinces", {
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
$ch = curl_init("https://api.shalom-api.lat/locations/departments/15/provinces");
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
    "https://api.shalom-api.lat/locations/departments/15/provinces",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "items": [
    { "id": 2, "name": "BARRANCA", "ubi_id": 1502 },
    { "id": 5, "name": "CAÑETE", "ubi_id": 1505 }
  ]
}
```

## Listar distritos de una provincia [#listar-distritos-de-una-provincia]

```http
GET /locations/departments/{depId}/provinces/{provId}/districts
```

Distritos (zonas) de la provincia con al menos una agencia de Shalom, ordenados por nombre. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `{depId}` | Sí | ID del departamento (entero). |
| `{provId}` | Sí | ID de la provincia (entero), ej. 1. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/locations/departments/15/provinces/1/districts" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/locations/departments/15/provinces/1/districts", {
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
$ch = curl_init("https://api.shalom-api.lat/locations/departments/15/provinces/1/districts");
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
    "https://api.shalom-api.lat/locations/departments/15/provinces/1/districts",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "items": [
    { "id": 2, "name": "ANCON", "ubi_id": 150102 },
    { "id": 3, "name": "ATE-VITARTE", "ubi_id": 150103 }
  ]
}
```

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | depId o provId no es un número entero. |
| `404` | No hay agencias en ese departamento o en esa provincia (o el ID no existe). |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Por qué no aparecen todos los distritos del Perú?">
Porque la lista sale del catálogo de agencias de Shalom. Solo aparecen los departamentos, provincias y distritos donde hay al menos una agencia.
</Accordion>

<Accordion title="¿Cómo obtengo los distritos de una provincia?">
Con GET /locations/departments/\{depId\}/provinces/\{provId\}/districts, usando los id que devuelven los niveles anteriores.
</Accordion>

<Accordion title="¿Los ubigeos son obligatorios para crear un envío?">
No. La guía se crea con el ter_id de la agencia de destino. Los ubigeos sirven para armar y validar la dirección del cliente.
</Accordion>

<Accordion title="¿Sirven para el reparto a domicilio?">
Para la autogestión reparto_domicilio usa GET /shipments/home-delivery/ubigeo, que devuelve los IDs tal como los pide Shalom Pro.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Catálogo completo de agencias" href="/docs/agencias" />

<Card title="Crear envíos" href="/docs/crear-envio" />
</Cards>

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

# Cotizar tarifas y consultar DNI (https://docs.shalom-api.lat/docs/cotizar)

Antes de crear una guía puedes cotizar la tarifa entre dos agencias y, si envías las medidas del paquete, el precio de OTRA MEDIDA por volumen. En la misma página está la consulta de DNI, útil para completar y validar los datos del destinatario.

## Qué necesitas para cotizar [#qué-necesitas-para-cotizar]

Origen y destino, cada uno por su ter_id (GET /agencies/search) o por el nombre de la agencia. Envía también el instanceId de tu cuenta Shalom Pro: la cotización usa la tarifa de esa cuenta, la misma que se cobra al registrar, así que puedes mostrar el precio exacto en tu tienda.

## Productos fijos y otra medida [#productos-fijos-y-otra-medida]

Sin medidas, data.tariff trae el precio por unidad de cada producto fijo (sobre y paquetes XXS a L). Con ancho, alto, largo y peso, data.price trae además el precio de OTRA MEDIDA, calculado por volumen. Las cuatro medidas van juntas: si falta una, responde 400.

## Cotizar no reserva nada [#cotizar-no-reserva-nada]

La cotización es una consulta de precio: no crea la guía ni bloquea la tarifa. Si tu flujo es cotizar y luego registrar, hazlo seguido para que el precio que cobraste coincida con el que vas a pagar.

## Cotizar envío [#cotizar-envío]

```http
POST /account/quote
```

Calcula el costo de un envío entre dos agencias. Sin medidas devuelve la tarifa de los productos fijos; con width, height, length y weight devuelve además el precio de OTRA MEDIDA por volumen en data.price. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | No | Recomendado. Instancia de tu cuenta Shalom Pro: cotiza con la tarifa de esa cuenta, la misma que se cobra al registrar. Sin él se usa el cotizador público de Shalom, que puede responder 503. |
| `origin` | Sí | Agencia de origen, por ter_id (número) o por nombre (ej. 7). |
| `destination` | Sí | Agencia de destino, por ter_id (número) o por nombre (ej. 582). |
| `width` | No | Ancho en metros (mayor a 0, máx. 1.5). Para cotizar OTRA MEDIDA envía las cuatro medidas juntas. |
| `height` | No | Alto en metros (mayor a 0, máx. 1.5). |
| `length` | No | Largo en metros (mayor a 0, máx. 1.5). |
| `weight` | No | Peso en kg (mayor a 0, máx. 500). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/account/quote" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "origin": 7,
  "destination": 582,
  "width": 1.4,
  "height": 1.4,
  "length": 1.4,
  "weight": 9
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/quote", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc",
  "origin": 7,
  "destination": 582,
  "width": 1.4,
  "height": 1.4,
  "length": 1.4,
  "weight": 9
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/account/quote");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "2e656a02-7e37-4573-9d68-e76740d337dc", "origin" => 7, "destination" => 582, "width" => 1.4, "height" => 1.4, "length" => 1.4, "weight" => 9]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/account/quote",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "2e656a02-7e37-4573-9d68-e76740d337dc", "origin": 7, "destination": 582, "width": 1.4, "height": 1.4, "length": 1.4, "weight": 9},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Calculo realizado",
  "modo": "personalizado",
  "fuente": "pro",
  "data": {
    "price": 543,
    "type": "volumen",
    "message": "Cotización basada en el volumen",
    "tariff": { "sobre": 8, "cajapaquetexxs": 8, "cajapaquetexs": 10, "cajapaquetes": 12, "cajapaquetem": 16, "cajapaquetel": 20 }
  }
}
```

<Callout>
modo es "estandar" sin medidas y "personalizado" con las cuatro medidas; fuente es "pro" con instanceId y "publica" sin él. data.tariff trae el precio por unidad de cada producto fijo. La respuesta se guarda 5 minutos por ruta y medidas (y por instancia, con instanceId).
</Callout>

## Consultar DNI [#consultar-dni]

```http
GET /account/dni/{dni}
```

Devuelve el nombre de una persona por su número de DNI, desde un servicio externo de consulta. No requiere instancia. Consume 1 operación.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `{dni}` | Sí | Número de DNI (8 caracteres), ej. 12345678. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/account/dni/12345678" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/account/dni/12345678", {
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
$ch = curl_init("https://api.shalom-api.lat/account/dni/12345678");
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
    "https://api.shalom-api.lat/account/dni/12345678",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Información de DNI obtenida de la base de datos.",
  "data": {
    "dni": "12345678",
    "nombres": "CLIENTE",
    "apellidoPaterno": "DE",
    "apellidoMaterno": "PRUEBA",
    "digitoVerificador": 4
  }
}
```

<Callout>
La respuesta llega tal cual la entrega el servicio de consulta. Si ese servicio responde con error, la API devuelve 500 con el mensaje DNI_API_ERROR y el código que recibió.
</Callout>

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | El DNI no tiene 8 caracteres, falta origin o destination, no se encontró una agencia por nombre, o las medidas están incompletas o fuera de rango. |
| `403` | El instanceId no existe o no pertenece a tu API key. |
| `409` | La instancia perdió la sesión de Shalom Pro y no pudo reconectarse. Vuelve a iniciar sesión. |
| `422` | Shalom Pro no devolvió una tarifa para esa ruta. |
| `500` | El servicio de consulta de DNI falló (DNI_API_ERROR) o hubo un error no previsto. |
| `502` | Shalom Pro no pudo calcular la tarifa. Reintenta en unos minutos. |
| `503` | Cotizaste sin instanceId y el cotizador público de Shalom no está disponible. Reintenta con instanceId. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Necesito una instancia de Shalom Pro para cotizar?">
Es lo recomendado. Con instanceId la cotización usa la tarifa de tu cuenta, la misma que se cobra al registrar. Sin instanceId se intenta el cotizador público de Shalom, que puede no estar disponible (503).
</Accordion>

<Accordion title="¿Qué identificadores uso como origen y destino?">
El ter_id de la agencia, que obtienes con GET /agencies/search. También se acepta el nombre de la agencia.
</Accordion>

<Accordion title="¿La cotización reserva la tarifa?">
No. Es una consulta de precio: no crea la guía ni bloquea el costo. Cotiza y registra seguido para que ambos coincidan.
</Accordion>

<Accordion title="¿Cómo sé si una agencia tiene servicio aéreo?">
Con el campo ter_aereo del catálogo de agencias, o filtrando con aereo=true en GET /agencies/search.
</Accordion>

<Accordion title="¿Para qué sirve consultar el DNI?">
Para confirmar que el documento del destinatario existe y que el nombre coincide con el del pedido antes de crear la guía.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Obtener los IDs de agencia" href="/docs/agencias" />

<Card title="Crear la guía ya cotizada" href="/docs/crear-envio" />
</Cards>

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

# Instancias (sesión Shalom Pro) (https://docs.shalom-api.lat/docs/instancias)

Una instancia es una cuenta de Shalom Pro conectada a tu cuenta de Shalom API. Todas las operaciones marcadas como Requiere Shalom Pro (crear guías, listar pendientes, ticket, rótulo, autogestiones) usan tu API key más el instanceId de la instancia.

## Crear y conectar [#crear-y-conectar]

POST /instances crea la instancia con un nombre para identificarla y POST /instances/login abre la sesión con el usuario y la contraseña de Shalom Pro. La cantidad de instancias depende de tu plan; el plan gratuito no incluye instancias.

## La sesión se reconecta sola [#la-sesión-se-reconecta-sola]

La sesión de Shalom Pro se guarda en la instancia y sobrevive a reinicios del servidor. No se mantiene viva con consultas periódicas: se usa cuando una operación la necesita.

Si Shalom ya la venció, la API lo detecta en esa misma operación, vuelve a iniciar sesión con las credenciales guardadas y reintenta una vez. Para tu integración es transparente: la llamada solo tarda unos segundos más. Si llegan varias operaciones a la vez, comparten un solo login.

La reconexión necesita las credenciales guardadas. Se pierden con POST /instances/logout y dejan de servir si cambia la contraseña de Shalom Pro. En ese caso las operaciones Pro fallan (por lo general con 409, "La instancia perdió la sesión de Shalom Pro") y hay que volver a llamar a POST /instances/login.

## Comprobar la sesión [#comprobar-la-sesión]

POST /instances/status dice si la sesión está abierta en este momento, pero no reconecta. Úsalo para mostrar el estado en tu panel, no como requisito antes de cada operación.

## Crear una instancia [#crear-una-instancia]

```http
POST /instances
```

Crea una instancia vacía en tu cuenta. Después inicia sesión en ella con POST /instances/login. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `name` | No | Nombre para identificarla (ej. Tienda Lima). Por defecto "Mi Instancia". |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/instances" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "name": "Tienda Lima"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "name": "Tienda Lima"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/instances");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["name" => "Tienda Lima"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/instances",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"name": "Tienda Lima"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "status": "created",
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
  "name": "Tienda Lima",
  "message": "Instance created successfully"
}
```

<Callout>
Responde 403 si tu plan no incluye instancias o si ya alcanzaste su máximo. El plan gratuito no incluye instancias.
</Callout>

## Listar instancias [#listar-instancias]

```http
GET /instances
```

Devuelve las instancias de tu cuenta. No consume cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/instances" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances", {
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
$ch = curl_init("https://api.shalom-api.lat/instances");
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
    "https://api.shalom-api.lat/instances",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "instances": [
    {
      "id": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
      "name": "Tienda Lima",
      "username": "usuario@ejemplo.com",
      "createdAt": "2026-11-01T10:30:00.000Z",
      "isLoggedIn": true
    }
  ]
}
```

<Callout>
Aquí isLoggedIn indica que la instancia tiene credenciales y una sesión guardada, sin consultar a Shalom. Para comprobarla en vivo usa POST /instances/status.
</Callout>

## Eliminar una instancia [#eliminar-una-instancia]

```http
DELETE /instances
```

Elimina la instancia, su sesión y sus credenciales guardadas. No se puede deshacer. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia a eliminar (en el body). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X DELETE "https://api.shalom-api.lat/instances" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances", {
  method: "DELETE",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/instances");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "7c9e6679-7425-40de-944b-e07fc1f90ae7"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.delete(
    "https://api.shalom-api.lat/instances",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "status": "closed",
  "message": "Instance closed successfully"
}
```

## Estado de la sesión [#estado-de-la-sesión]

```http
POST /instances/status
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Comprueba con Shalom si la sesión de la instancia está abierta en este momento. Solo informa, no reconecta: un isLoggedIn en false con credenciales guardadas no es un error, porque la próxima operación Pro se reconecta sola. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia a revisar (en el body). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/instances/status" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances/status", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/instances/status");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "7c9e6679-7425-40de-944b-e07fc1f90ae7"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/instances/status",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "isLoggedIn": true,
  "username": "usuario@ejemplo.com",
  "url": "https://pro.shalom.pe/#/envios/list"
}
```

<Callout>
Sin sesión responde isLoggedIn en false, username en null y url "https://pro.shalom.pe/login". El resultado de la comprobación se reutiliza hasta un minuto.
</Callout>

## Iniciar sesión en Shalom Pro [#iniciar-sesión-en-shalom-pro]

```http
POST /instances/login
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Inicia sesión en pro.shalom.pe con el usuario y la contraseña de la cuenta. La sesión y las credenciales quedan guardadas en la instancia para reconectar sola cuando la sesión vence, así que basta con hacerlo una vez. Puede tardar varios segundos. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia donde iniciar sesión. |
| `username` | Sí | Usuario o correo de Shalom Pro. |
| `password` | Sí | Contraseña de Shalom Pro. |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/instances/login" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
  "username": "usuario@ejemplo.com",
  "password": "tu-contraseña"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances/login", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7",
  "username": "usuario@ejemplo.com",
  "password": "tu-contraseña"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/instances/login");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "7c9e6679-7425-40de-944b-e07fc1f90ae7", "username" => "usuario@ejemplo.com", "password" => "tu-contraseña"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/instances/login",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7", "username": "usuario@ejemplo.com", "password": "tu-contraseña"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Login successful",
  "url": "https://pro.shalom.pe/#/envios/list"
}
```

<Callout>
Si la instancia ya tenía la sesión abierta con esas mismas credenciales, responde message "Already logged in". Con credenciales incorrectas responde 401 con \{ success: false, message \}.
</Callout>

## Cerrar sesión en Shalom Pro [#cerrar-sesión-en-shalom-pro]

```http
POST /instances/logout
```

<Callout type="info" title="Requiere Shalom Pro">
Este endpoint usa una instancia con la sesión de Shalom Pro iniciada. Ver [Instancias](/docs/instancias).
</Callout>

Cierra la sesión y borra las credenciales guardadas. Después la instancia ya no se reconecta sola hasta un nuevo POST /instances/login. No consume cuota.

| Parámetro | Requerido | Descripción |
| --- | --- | --- |
| `instanceId` | Sí | Instancia donde cerrar sesión (en el body). |

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X POST "https://api.shalom-api.lat/instances/logout" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}'
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/instances/logout", {
  method: "POST",
  headers: {
    "x-api-key": process.env.SHALOM_API_KEY,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
  "instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"
}),
});
const data = await res.json();
console.log(data);
```
</Tab>

<Tab value="PHP">
```php
<?php
$ch = curl_init("https://api.shalom-api.lat/instances/logout");
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "x-api-key: TU_API_KEY",
    "Content-Type: application/json",
  ],
  CURLOPT_POSTFIELDS => json_encode(["instanceId" => "7c9e6679-7425-40de-944b-e07fc1f90ae7"]),
]);
$data = json_decode(curl_exec($ch), true);
print_r($data);
```
</Tab>

<Tab value="Python">
```python
import os, requests

res = requests.post(
    "https://api.shalom-api.lat/instances/logout",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
    json={"instanceId": "7c9e6679-7425-40de-944b-e07fc1f90ae7"},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "success": true,
  "message": "Logged out and session cleared"
}
```

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | Falta instanceId, username o password. |
| `401` | Falta la API key, la key es inválida (POST y GET /instances), o el usuario o la contraseña de Shalom Pro son incorrectos (login). |
| `403` | En las rutas con instanceId, la key es inválida o la instancia no es de tu cuenta. En POST /instances, tu plan no permite crear más instancias. |
| `404` | La instancia no existe. |
| `500` | No se pudo completar el login, el logout o la creación. El campo details trae el motivo cuando está disponible. |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Qué credenciales usa una instancia de Shalom Pro?">
Las de la cuenta de Shalom Pro que despacha los envíos. Se guardan en la instancia para operar en su nombre y reconectar la sesión cuando vence.
</Accordion>

<Accordion title="¿Tengo que iniciar sesión cada cierto tiempo?">
No. Haces POST /instances/login una vez y la instancia se reconecta sola cuando hace falta, aunque la sesión haya vencido o el servidor se haya reiniciado.
</Accordion>

<Accordion title="¿Cuándo tengo que volver a iniciar sesión?">
Si cambió la contraseña de Shalom Pro, si hiciste logout o si una operación responde que la instancia perdió la sesión (409) o que el auto-login falló.
</Accordion>

<Accordion title="¿Puedo tener varias instancias?">
Sí, según el máximo de tu plan. Lo recomendable es una por cuenta de Shalom Pro.
</Accordion>

<Accordion title="¿La API key de mi cuenta sirve para las operaciones Pro?">
Sí. Usas tu misma API key y agregas el instanceId de la instancia en cada operación Pro.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Crear envíos con tu instancia" href="/docs/crear-envio" />

<Card title="Pendientes, historial y comprobantes" href="/docs/operaciones" />

<Card title="API keys y planes" href="/docs/autenticacion" />
</Cards>

# Referencia de la API (https://docs.shalom-api.lat/docs/referencia)

<AgentInstructions />

Shalom API es una API REST con respuestas JSON. Esta página reúne lo común a todos los endpoints y la lista completa; el detalle de cada uno está en su tema.

## URL base [#url-base]

```text
https://api.shalom-api.lat
```

Todas las peticiones usan HTTPS. Cuando hay cuerpo, envíalo en JSON con `Content-Type: application/json`.

## Autenticación [#autenticación]

Envía tu API key en el header `x-api-key` de cada petición, salvo en las rutas `/public/*`. La copias en el panel, en **API Key**. Detalles en [Autenticación y API keys](/docs/autenticacion).

```bash title="cURL"
curl "https://api.shalom-api.lat/validate" \
  -H "x-api-key: TU_API_KEY"
```

## Límites y errores [#límites-y-errores]

- 60 peticiones por minuto por API key. Al superarlo, la API responde `429` con el header `retry-after`.
- Cada plan tiene una cuota mensual de operaciones. Solo consumen cuota las llamadas exitosas de los endpoints marcados con **Sí** en la tabla de abajo.
- Los errores responden con un código HTTP y un cuerpo JSON con el campo `error`. Lista completa en [Errores y límites](/docs/errores).

## Shalom Pro [#shalom-pro]

Los endpoints marcados con **Sí** en la columna Shalom Pro operan sobre tu cuenta de Shalom Pro: necesitan una [instancia](/docs/instancias) con la sesión iniciada y su `instanceId`. Se llaman con tu misma API key.

## Todos los endpoints [#todos-los-endpoints]

| Método | Ruta | Qué hace | Cuota | Shalom Pro |
| --- | --- | --- | --- | --- |
| POST | `/track` | [Rastrear una guía](/docs/tracking) | Sí | No |
| POST | `/track/batch` | Rastrear hasta 50 guías | Sí, 1 por llamada | No |
| GET | `/track/by-ose` | Resolver un ose_id a guía y código | Sí | Opcional |
| GET | `/track/voucher` | Ticket oficial en PDF o imagen | Sí | Sí |
| GET | `/track/label` | Rótulo en PDF | Sí | Sí |
| GET | `/agencies` | [Listar agencias](/docs/agencias) | Sí | No |
| GET | `/agencies/search` | Buscar agencias y ordenar por cercanía | Sí | No |
| GET | `/public/agencies` | Listado público, sin API key | No | No |
| GET | `/public/agencies/search` | Búsqueda pública, sin API key | No | No |
| GET | `/locations/departments` | [Departamentos con agencias](/docs/ubicaciones) | Sí | No |
| GET | `/locations/departments/{depId}/provinces` | Provincias de un departamento | Sí | No |
| GET | `/locations/departments/{depId}/provinces/{provId}/districts` | Distritos de una provincia | Sí | No |
| POST | `/account/quote` | [Cotizar un envío](/docs/cotizar) | Sí | Opcional (recomendado) |
| GET | `/account/dni/{dni}` | Consultar un DNI | Sí | No |
| GET | `/account/products` | [Catálogo de contenidos](/docs/crear-envio) | No | No |
| POST | `/account/register` | Crear una guía | Sí | Sí |
| POST | `/account/register-bulk` | Crear guías en lote | Sí, 1 por llamada | Sí |
| POST | `/account/get-user` | Perfil del remitente | Sí | Sí |
| POST | `/account/person/search` | Buscar una persona registrada | Sí | Sí |
| POST | `/account/pending-shipments` | [Envíos pendientes](/docs/operaciones) | Sí | Sí |
| DELETE | `/account/pending-shipments` | Anular un envío pendiente | Sí | Sí |
| GET | `/account/tracking-list` | Envíos en tránsito | Sí | Sí |
| GET | `/account/history` | Historial de entregados | Sí | Sí |
| GET | `/account/vouchers` | Comprobantes | Sí | Sí |
| POST | `/shipments/pickup-code` | [Cambiar la clave de recojo](/docs/autogestiones) | Sí | Sí |
| POST | `/shipments/self-management` | Iniciar una autogestión (envía el código) | No | Sí |
| POST | `/shipments/self-management/confirm` | Confirmar la autogestión | Sí | Sí |
| GET | `/shipments/self-management` | Historial de autogestiones | No | Sí |
| GET | `/shipments/home-delivery/ubigeo` | Ubigeo para reparto a domicilio | No | Sí |
| POST | `/instances` | [Crear una instancia](/docs/instancias) | No | No |
| GET | `/instances` | Listar instancias | No | No |
| DELETE | `/instances` | Eliminar una instancia | No | Sí (instanceId) |
| POST | `/instances/status` | Estado de la sesión | No | Sí |
| POST | `/instances/login` | Iniciar sesión en Shalom Pro | No | Sí |
| POST | `/instances/logout` | Cerrar sesión en Shalom Pro | No | Sí |
| PUT | `/webhooks` | [Configurar el webhook](/docs/webhooks) | No | No |
| GET | `/webhooks` | Consultar el webhook | No | No |
| DELETE | `/webhooks` | Eliminar el webhook | No | No |
| GET | `/webhooks/deliveries` | Historial de entregas | No | No |
| POST | `/tracking/subscriptions` | Suscribir una guía | No | No |
| GET | `/tracking/subscriptions` | Listar suscripciones | No | No |
| DELETE | `/tracking/subscriptions` | Cancelar una suscripción | No | No |
| GET | `/validate` | [Validar la API key y ver el consumo](/docs/autenticacion) | No | No |

## Por tema [#por-tema]

<Cards>
<Card title="Rastrear envíos" href="/docs/tracking" description="Una guía o hasta 50, ticket oficial y rótulo." />

<Card title="Cotizar y consultar DNI" href="/docs/cotizar" description="Tarifas entre agencias y datos de una persona." />

<Card title="Crear envíos" href="/docs/crear-envio" description="Guías reales en Shalom Pro, una o en lote." />

<Card title="Operaciones de envíos" href="/docs/operaciones" description="Pendientes, en tránsito, historial y comprobantes." />

<Card title="Autogestiones" href="/docs/autogestiones" description="Cambios sobre una guía ya creada." />

<Card title="Agencias y cobertura" href="/docs/agencias" description="Catálogo, búsqueda y agencias cercanas." />

<Card title="Ubicaciones" href="/docs/ubicaciones" description="Departamentos, provincias y distritos." />

<Card title="Instancias" href="/docs/instancias" description="Conecta y gestiona tu sesión de Shalom Pro." />

<Card title="Webhooks" href="/docs/webhooks" description="Avisos firmados cuando cambia una guía." />
</Cards>

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

# Autenticación y API keys (https://docs.shalom-api.lat/docs/autenticacion)

Toda petición, salvo las rutas /public/*, se autentica con una API key personal que empieza con sk_. Con ella la plataforma te identifica y mide tu consumo mensual según tu plan.

## Cómo obtener tu key [#cómo-obtener-tu-key]

Crea tu cuenta en shalom-api.lat: al registrarte se genera tu primera key. En el panel, en API Key, puedes copiarla, crear otras y eliminarlas; tu plan puede limitar cuántas tienes.

Una cuenta sin plan usa el plan gratuito: 5 operaciones al mes y sin instancias de Shalom Pro, así que no puede crear guías (sí puede rastrear y consultar agencias).

## Dónde va la API key [#dónde-va-la-api-key]

Envíala en el header x-api-key de cada petición. También se acepta Authorization: Bearer, pero en ese caso el rate limit se cuenta por IP y no por key, así que x-api-key es la forma recomendada. No la pongas en la URL ni en el cuerpo: las URLs quedan en logs, proxies y analítica.

Si tu cliente es una app web, la key nunca debe llegar al navegador. Las llamadas salen desde tu backend.

## Qué consume cuota [#qué-consume-cuota]

Cada llamada exitosa a un endpoint que consume cuota suma una operación al mes de tu cuenta, sin importar qué key uses. Las respuestas con error no suman. Cada endpoint de esta documentación dice si consume cuota o no; la lista completa está en la Referencia de la API.

Si tu plan vence, los endpoints que consumen cuota responden 403, y si agotas la cuota, 429. Los que no consumen cuota siguen funcionando en los dos casos.

## Buenas prácticas para producción [#buenas-prácticas-para-producción]

Guarda la key en variables de entorno, nunca en el repositorio. Usa una key distinta para pruebas y para producción, así puedes eliminar una sin afectar a la otra. Si sospechas una filtración, crea una nueva y elimina la anterior desde el panel.

## Validar tu API key [#validar-tu-api-key]

```http
GET /validate
```

Confirma que la key es válida y devuelve el límite mensual de tu plan y el consumo del mes. Úsala como health check de tu integración. No consume cuota y responde aunque tu plan haya vencido o hayas agotado la cuota.

<Tabs items={["cURL","JavaScript","PHP","Python"]}>
<Tab value="cURL">
```bash
curl -X GET "https://api.shalom-api.lat/validate" \
  -H "x-api-key: TU_API_KEY"
```
</Tab>

<Tab value="JavaScript">
```js
const res = await fetch("https://api.shalom-api.lat/validate", {
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
$ch = curl_init("https://api.shalom-api.lat/validate");
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
    "https://api.shalom-api.lat/validate",
    headers={"x-api-key": os.environ["SHALOM_API_KEY"]},
)
print(res.json())
```
</Tab>
</Tabs>

```json title="Respuesta"
{
  "valid": true,
  "userId": "b7c9d1e2-4f6a-4c3b-9d2e-1a2b3c4d5e6f",
  "message": "API key válida",
  "limit": 1000,
  "currentUsage": 137,
  "remaining": 863
}
```

<Callout>
limit y remaining llegan en null cuando el plan no tiene límite mensual.
</Callout>

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `401` | Falta la API key o es inválida. |
| `403` | El plan asociado a la key venció (solo en las rutas que consumen cuota). |
| `429` | Superaste el rate limit o agotaste la cuota mensual (ver Errores y límites). |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Cómo obtengo una API key de Shalom API?">
Creando una cuenta en shalom-api.lat. Al registrarte se genera tu primera key, que ves y copias en el panel, en API Key.
</Accordion>

<Accordion title="¿Dónde se envía la API key?">
En el header x-api-key de cada petición (también se acepta Authorization con Bearer). No la pongas en la URL ni en el cuerpo.
</Accordion>

<Accordion title="¿Qué consume cuota?">
Cada llamada exitosa a un endpoint que no diga "No consume cuota". Las respuestas con error (4xx o 5xx) no consumen. Las rutas de gestión (instancias, webhooks, suscripciones, catálogo de contenidos, /validate) y el primer paso de las autogestiones son gratis.
</Accordion>

<Accordion title="¿Cuál es el límite de peticiones de Shalom API?">
60 peticiones por minuto por API key, además de la cuota mensual de tu plan.
</Accordion>

<Accordion title="¿Cómo compruebo si mi API key sigue activa?">
Con GET /validate. Es el primer descarte cuando una integración falla.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Códigos de error y reintentos" href="/docs/errores" />

<Card title="Conectar tu cuenta Shalom Pro" href="/docs/instancias" />
</Cards>

# Buenas prácticas y uso justo (https://docs.shalom-api.lat/docs/buenas-practicas)

Cada consulta a Shalom API que necesita datos en vivo pasa por la infraestructura de conexión con Shalom, que es compartida entre todos los clientes. Una integración que consulta en bucle no solo gasta su cuota: puede degradar el servicio de los demás. Esta página resume cómo integrar sin bucles y qué pasa si una integración genera un consumo excesivo.

## Regla de oro: avisos, no preguntas [#regla-de-oro-avisos-no-preguntas]

Para saber cuándo cambia una guía, no la preguntes una y otra vez: suscríbela con POST /tracking/subscriptions y configura tu webhook con PUT /webhooks. La plataforma vigila la guía por ti y te envía un POST firmado cada vez que cambia de estado.

Suscribe cada guía justo después de crearla o registrarla. Cuando llegue el webhook, guarda el estado en tu base de datos y responde a tus usuarios desde ahí, no desde la API.

## Usa POST /track solo cuando alguien lo necesita [#usa-post-track-solo-cuando-alguien-lo-necesita]

POST /track sirve para una consulta puntual, por ejemplo cuando un cliente abre la pantalla de seguimiento. Si el dato guardado tiene menos de 10 minutos se responde al instante; si no, se consulta a Shalom en vivo.

No programes un cron que recorra todas tus guías con POST /track. Si necesitas revisar varias a la vez (por ejemplo, una conciliación diaria), usa POST /track/batch con hasta 50 guías por llamada, una vez al día o cada pocas horas, y solo con las guías que no están entregadas.

## Listados del panel: con caché y sin bucles [#listados-del-panel-con-caché-y-sin-bucles]

GET /account/tracking-list, GET /account/history, GET /account/vouchers y POST /account/pending-shipments consultan el panel de Shalom Pro en vivo en cada llamada y devuelven páginas completas. Son caros: no los uses para vigilar estados.

Pídelos como máximo una vez cada 15 minutos por instancia, guarda la respuesta en tu sistema y recorre las páginas una sola vez. Si lo que quieres es saber cuándo cambia una guía, usa webhooks.

## Guarda lo que no cambia [#guarda-lo-que-no-cambia]

Los datos de un DNI (GET /account/dni/\{dni\}), las agencias y las ubicaciones casi no cambian. Guárdalos en tu base o en caché por al menos un día y no los vuelvas a pedir en cada pedido del mismo cliente.

El ticket y el rótulo de una guía (GET /track/voucher, GET /track/label) no cambian una vez generados: descárgalos una vez y guarda el archivo.

## No reintentes errores 4xx [#no-reintentes-errores-4xx]

Un 400, 401, 403 o 404 significan que la petición o el dato están mal: reintentar no lo arregla y solo genera tráfico. Marca la guía con error en tu sistema y no la vuelvas a pedir hasta que algo cambie.

Un 429, 500, 502 o 503 sí se pueden reintentar, con espera creciente (por ejemplo 1, 5, 15 y 60 minutos) y un máximo de intentos. Nunca reintentes de inmediato en un bucle sin límite.

## Lista de revisión de tu integración [#lista-de-revisión-de-tu-integración]

Antes de salir a producción, comprueba lo siguiente:

1. Las guías nuevas se suscriben con POST /tracking/subscriptions y tu URL recibe los webhooks.
2. Ningún cron llama a POST /track ni a los listados del panel en bucle.
3. Los listados del panel se piden como máximo cada 15 minutos y se guardan.
4. DNI, agencias, tickets y rótulos se guardan y no se vuelven a pedir.
5. Los errores 4xx no se reintentan, y los 5xx o 429 tienen espera creciente y un tope de intentos.
6. Si ya recibes webhooks, no consultas además las mismas guías con POST /track/batch.

## Política de uso justo [#política-de-uso-justo]

Si una integración genera un consumo excesivo (consultas en bucle, reintentos sin fin o listados pedidos sin pausa), se suspende el acceso de su API key para proteger el servicio del resto de clientes. El panel sigue disponible para revisar la cuenta.

Para reactivar el acceso hay que hacer un pago único de S/30 y corregir la integración siguiendo esta página, para que no vuelva a pasar. Si necesitas ayuda para pasar a webhooks, escríbenos por WhatsApp.

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Puedo consultar POST /track cada pocos minutos para saber si cambió una guía?">
No. Para enterarte de los cambios suscribe la guía (POST /tracking/subscriptions) y recibe el webhook tracking.status_changed. Consulta POST /track solo cuando un usuario abre la pantalla de seguimiento.
</Accordion>

<Accordion title="¿Cada cuánto puedo pedir GET /account/tracking-list o GET /account/history?">
Como máximo una vez cada 15 minutos por instancia, y guarda la respuesta en tu sistema. Cada llamada consulta el panel de Shalom en vivo; no sirven para vigilar estados en bucle.
</Accordion>

<Accordion title="¿Qué pasa si mi integración genera un consumo excesivo?">
Se suspende el acceso de tu API key para proteger el servicio del resto de clientes. Para reactivarla hay que hacer un pago único de S/30 y corregir la integración para que no vuelva a pasar.
</Accordion>

<Accordion title="¿Debo reintentar cuando una guía o una etiqueta responde 404?">
No. Un 4xx significa que el dato está mal o todavía no existe; reintentar no lo arregla. Marca la guía en tu sistema y no la vuelvas a pedir hasta que cambie algo (por ejemplo, un webhook).
</Accordion>

<Accordion title="Ya uso webhooks, ¿necesito además consultar POST /track/batch?">
No. Si la guía está suscrita, el webhook te avisa de cada cambio. Consultar además en lote solo duplica el trabajo y el consumo.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Recibir cambios de estado por webhook" href="/docs/webhooks" />

<Card title="Cómo se actualiza el tracking" href="/docs/optimizations" />

<Card title="Errores, límites y reintentos" href="/docs/errores" />

<Card title="Rastrear envíos" href="/docs/tracking" />
</Cards>

# Conectar Claude o ChatGPT (https://docs.shalom-api.lat/docs/conectar-ia)

Shalom API tiene un conector MCP remoto. Al conectarlo, Claude o ChatGPT pueden rastrear tus guías, buscar agencias, cotizar, crear guías en tu cuenta de Shalom Pro y más, siempre con el permiso que elijas. Usa los mismos datos y la misma cuota que la API.

- **URL del conector:** `https://api.shalom-api.lat/mcp`
- **Autenticación:** OAuth. Al conectar se abre Shalom API para que inicies sesión y autorices el acceso. No necesitas copiar ninguna API key.

## Conectar en un minuto [#conectar-en-un-minuto]

<Steps>
<Step>
### Abre el Conector en el panel [#abre-el-conector-en-el-panel]

Entra a [shalom-api.lat/dashboard/conector](https://shalom-api.lat/dashboard/conector) y pulsa **Conectar** en Claude o en ChatGPT.
</Step>

<Step>
### Pega el mensaje en tu asistente [#pega-el-mensaje-en-tu-asistente]

Copia el mensaje que te muestra el panel y pégalo en un chat nuevo. El asistente te guía para agregar el conector personalizado.
</Step>

<Step>
### Autoriza el acceso [#autoriza-el-acceso]

Se abre la pantalla de permiso de Shalom API. Elige el nivel de acceso y pulsa **Autorizar**.
</Step>
</Steps>

## Hacerlo a mano [#hacerlo-a-mano]

<Tabs items={["Claude","ChatGPT","Claude Code"]}>
<Tab value="Claude">
En Claude, ve a **Configuración → Conectores → Agregar conector personalizado**, ponle de nombre `Shalom` y usa la URL `https://api.shalom-api.lat/mcp`. Después pulsa **Conectar** y autoriza.
</Tab>

<Tab value="ChatGPT">
En ChatGPT, activa el modo desarrollador en **Configuración → Aplicaciones y conectores → Configuración avanzada**. Luego crea un conector con la URL `https://api.shalom-api.lat/mcp` y autenticación OAuth.
</Tab>

<Tab value="Claude Code">
```bash
claude mcp add --transport http shalom https://api.shalom-api.lat/mcp
```

Luego ejecuta `/mcp` dentro de Claude Code para iniciar sesión.
</Tab>
</Tabs>

## Permisos [#permisos]

Al autorizar eliges uno de dos niveles. Puedes cambiarlo desconectando y volviendo a conectar.

| Nivel | Qué permite |
| --- | --- |
| **Lectura y gestión** | Consultar todo y además crear, anular y cambiar cosas (guías, autogestiones, instancias, API keys, webhooks). |
| **Solo lectura** | Consultar todo, sin cambiar nada. |

<Callout type="warn" title="Acciones que cuestan o no se pueden deshacer">
Crear guías (una o en lote), anular un envío pendiente, confirmar una autogestión y eliminar el webhook piden tu confirmación antes de ejecutarse: el asistente te muestra un resumen y espera tu respuesta.
</Callout>

- Cada herramienta consume cuota igual que su endpoint de la API (ver la [Referencia de la API](/docs/referencia)). Las que no consumen cuota lo dicen en su descripción.
- Si tu plan venció, no puedes hacer cambios y solo funcionan las consultas que no consumen cuota (tus instancias y su estado, API keys, webhook y guías vigiladas, tipos de contenido, historial de autogestiones, ubigeo de reparto, plan y consumo).
- Si no indicas la instancia, se usa la primera con la sesión iniciada; para iniciar sesión, la primera que no la tiene.
- Puedes ver y desconectar tus asistentes en el panel, en **Conector**.

## Qué puede hacer [#qué-puede-hacer]

| Área | Herramientas |
| --- | --- |
| Seguimiento | Rastrear una guía o hasta 50, buscar una orden por `ose_id`, ticket oficial (PDF o imagen) y rótulo en PDF |
| Agencias | Listar, buscar, las más cercanas a un punto, departamentos, provincias y distritos |
| Antes de enviar | Cotizar, tipos de contenido, consultar un DNI, buscar un destinatario guardado en tu cuenta |
| Instancias | Ver tus instancias y su estado, **crear una instancia** e **iniciar sesión en Shalom Pro** |
| Tu cuenta Shalom Pro | Envíos pendientes, en tránsito e historial, comprobantes, perfil del remitente, historial de autogestiones y ubigeo de reparto |
| Gestión de envíos | Crear guías (una o hasta 100 en lote), anular un envío pendiente, cambiar la clave de recojo, iniciar y confirmar autogestiones |
| API keys | Ver tus API keys (sin la key completa) y **crear una nueva** |
| Webhooks | Ver, configurar o eliminar el webhook, ver sus entregas, vigilar una guía y dejar de vigilarla |
| Cuenta | Plan, vencimiento y consumo del mes |

<Callout title="Tu contraseña de Shalom Pro">
Para iniciar sesión en una instancia desde el chat le das tu usuario y contraseña de Shalom Pro al asistente, así que quedan en esa conversación. Shalom API no la devuelve ni la guarda en sus registros. Si lo prefieres, inicia sesión desde el panel en **Instancias**.
</Callout>

**No puede:** borrar instancias, cerrar la sesión de Shalom Pro, borrar API keys, cambiar contraseñas, pagar o cambiar tu plan, ni nada de administración.

# Errores y límites (https://docs.shalom-api.lat/docs/errores)

Los errores responden con un código HTTP y un cuerpo JSON con el campo error y, a veces, details o message. Las validaciones de formato responden 400 con message, que indica el campo con problemas. Los rechazos de Shalom (registro de guías, login) pueden llegar como \{ success: false, message \}.

## Tu error y el de origen no se tratan igual [#tu-error-y-el-de-origen-no-se-tratan-igual]

Un 400, 401, 403 o 404 significan que la petición, la key o el dato están mal: reintentar no arregla nada. Un 429, 500, 502 o 503 sí se resuelven esperando y volviendo a intentar.

Un 409 en una operación Pro casi siempre es la sesión de la instancia: vuelve a iniciar sesión con POST /instances/login y reintenta.

Los errores 5xx pueden llegarte sin cuerpo JSON (por ejemplo, el texto "error code: 502"), porque el borde de la red los reemplaza. Trátalos por su código.

## Qué hacer con un 429 [#qué-hacer-con-un-429]

Hay dos 429 distintos y el cuerpo te dice cuál es. Si superaste el rate limit de 60 peticiones por minuto, el cuerpo trae "error": "Too Many Requests" y el header retry-after indica cuántos segundos esperar. Los headers x-ratelimit-limit y x-ratelimit-remaining te avisan antes de llegar.

Si agotaste la cuota mensual del plan, el cuerpo trae limit, currentUsage y remaining en 0. Reintentar no sirve hasta el mes siguiente o hasta que mejores el plan. Los endpoints que no consumen cuota siguen funcionando.

En los dos casos, no reintentes en bucle: espera un intervalo creciente entre intentos y reparte el trabajo. Para seguir cambios de estado usa webhooks en lugar de consultar cada guía.

## Vigila tu consumo antes de chocar [#vigila-tu-consumo-antes-de-chocar]

GET /validate devuelve el consumo del mes sin gastar cuota. Engánchalo a tu monitoreo y avisa a tu equipo cuando te acerques al límite, en lugar de descubrirlo con un 429 en producción.

## Errores [#errores]

| Código | Significado |
| --- | --- |
| `400` | Petición inválida: falta un campo, tiene otro tipo o está fuera de rango. También cuando Shalom rechaza un registro o una autogestión con su mensaje. |
| `401` | Falta la API key o es inválida. En POST /instances/login, usuario o contraseña de Shalom Pro incorrectos. |
| `403` | El plan venció, el plan no permite la acción (por ejemplo, crear más instancias), o el instanceId no pertenece a tu API key. |
| `404` | El recurso no existe, por ejemplo una orden sin ose_id, una guía que no está en tus pendientes o una instancia inexistente. |
| `409` | La instancia perdió la sesión de Shalom Pro y no pudo reconectarse, o Shalom no permite la operación en el estado actual de la guía. |
| `422` | Shalom Pro no devolvió una tarifa para esa ruta (POST /account/quote). |
| `429` | Rate limit superado (cuerpo \{ statusCode, error, message \} y header retry-after) o cuota mensual agotada (cuerpo \{ error, limit, currentUsage, remaining: 0 \}). |
| `500` | Error interno o del sistema de Shalom sin un código más preciso. Reintenta con espera creciente. |
| `502` | Shalom no respondió o devolvió un error. Reintenta en unos minutos. |
| `503` | Un servicio de origen no está disponible, por ejemplo el cotizador público de Shalom (cotiza con instanceId). |

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Cómo sé cuánta cuota me queda este mes?">
Con GET /validate, que devuelve limit, currentUsage y remaining. No consume cuota.
</Accordion>

<Accordion title="¿Un error consume cuota?">
No. Solo las respuestas exitosas de los endpoints que consumen cuota suman al consumo del mes.
</Accordion>

<Accordion title="¿Debo reintentar un 400 o un 404?">
No. La petición o el dato están mal; reintentar no lo arregla. Corrige lo que envías.
</Accordion>

<Accordion title="¿Cuál es el límite de peticiones?">
60 peticiones por minuto por API key. Al superarlo la API responde 429 con el header retry-after, que indica cuántos segundos esperar.
</Accordion>

<Accordion title="¿Qué hago si recibo un 500 o un 502?">
Reintentar con espera creciente. Suele ser un problema temporal del sistema de Shalom, no de tu integración.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Validar tu API key y qué consume cuota" href="/docs/autenticacion" />

<Card title="Evitar polling con webhooks" href="/docs/webhooks" />
</Cards>

# Inicio rápido (https://docs.shalom-api.lat/docs)

<AgentInstructions />

Shalom API es una API REST para integrar los envíos de **Shalom** (agencia a agencia) en tu sistema: rastrear guías, consultar agencias y cobertura, cotizar, crear guías en tu cuenta de Shalom Pro y recibir los cambios de estado por webhook.

## Empieza en tres pasos [#empieza-en-tres-pasos]

<Steps>
<Step>
### Crea tu cuenta y tu API key [#crea-tu-cuenta-y-tu-api-key]

Regístrate en [shalom-api.lat](https://shalom-api.lat/auth/register). En el panel, entra a **API Key** y copia tu key (empieza con `sk_`).
</Step>

<Step>
### Haz tu primera consulta [#haz-tu-primera-consulta]

Todas las peticiones van a `https://api.shalom-api.lat` con tu key en el header `x-api-key`.

```bash title="cURL"
curl -X POST "https://api.shalom-api.lat/track" \
  -H "x-api-key: TU_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{ "orderNumber": "66479331", "orderCode": "3KTH" }'
```
</Step>

<Step>
### Conecta tu cuenta de Shalom Pro (opcional) [#conecta-tu-cuenta-de-shalom-pro-opcional]

Para crear guías, ver tus envíos pendientes o hacer autogestiones, crea una [instancia](/docs/instancias) e inicia sesión con tu usuario de Shalom Pro. Necesitas un plan que incluya instancias: el plan gratuito no las incluye.
</Step>
</Steps>

## Qué puedes hacer [#qué-puedes-hacer]

<Cards>
<Card title="Rastrear envíos" href="/docs/tracking" description="Estado de una guía o de hasta 50 a la vez, ticket oficial y rótulo." />

<Card title="Agencias y cobertura" href="/docs/agencias" description="Busca agencias por texto, filtros o cercanía." />

<Card title="Cotizar" href="/docs/cotizar" description="Tarifas entre agencias y consulta de DNI." />

<Card title="Crear envíos" href="/docs/crear-envio" description="Registra guías reales en tu cuenta de Shalom Pro." />

<Card title="Webhooks" href="/docs/webhooks" description="Recibe los cambios de estado de tus guías." />

<Card title="Claude y ChatGPT" href="/docs/conectar-ia" description="Conecta tu cuenta a tu asistente de IA." />
</Cards>

<Callout title="Límites">
Cada API key admite 60 peticiones por minuto y cada plan tiene una cuota mensual de operaciones (el gratuito, 5 al mes). Qué consume cuota está en la [Referencia de la API](/docs/referencia); los límites, en [Errores y límites](/docs/errores).
</Callout>

# Instalar el nodo de n8n (https://docs.shalom-api.lat/docs/instalar-n8n)

El nodo comunitario n8n-nodes-shalom conecta tus flujos de n8n (self-hosted v1+) con Shalom API en minutos: rastrea guías, lista agencias, cotiza, valida DNI y crea envíos reales en Shalom Pro. Requiere una API key y, para las operaciones Pro, una instancia conectada.

## Qué necesitas antes de empezar [#qué-necesitas-antes-de-empezar]

Una instancia de n8n self-hosted, versión 1 o superior. Los nodos comunitarios no se pueden instalar en n8n Cloud, así que si usas el servicio hospedado este camino no aplica.

Y una API key: se genera al crear tu cuenta en shalom-api.lat y la copias en el panel, en API Key. Las operaciones de Shalom Pro necesitan además un plan que incluya instancias.

## Instalar el nodo [#instalar-el-nodo]

Desde n8n: Settings → Community nodes → instalar el paquete `n8n-nodes-shalom` y reiniciar la instancia. También puedes instalarlo con npm y copiarlo en la carpeta de nodos custom.

Al reiniciar, el nodo aparece en el panel como «Shalom API».

## Configurar la credencial [#configurar-la-credencial]

La credencial pide dos cosas: la base URL `https://api.shalom-api.lat` y tu API key, que viaja en el header `x-api-key`.

Usa el botón Test para validarla contra GET /validate antes de montar el flujo. Te dice si la key está activa y cuánto has consumido en el mes.

## Los cinco recursos del nodo [#los-cinco-recursos-del-nodo]

Agencias, Ubicaciones, Tracking, Cuentas e Instancias. Las operaciones de Shalom Pro piden el instanceId como parámetro de cada nodo, no a nivel de credencial, así que puedes tener varias instancias en el mismo flujo.

## Un flujo típico [#un-flujo-típico]

Iniciar sesión en la instancia, registrar el envío, rastrearlo y avisar. Y si quieres reaccionar a los cambios de estado sin consultar cada rato, suma un webhook en lugar de un bucle de polling.

<Steps>
<Step>
### Instala el nodo comunitario [#instala-el-nodo-comunitario]

En n8n, ve a Settings → Community nodes, busca e instala el paquete n8n-nodes-shalom (o cópialo en ~/.n8n/custom). Reinicia n8n: el nodo "Shalom API" aparece en el panel de nodos.

```bash
# Opción A: desde la UI de n8n (Settings → Community nodes) buscar "n8n-nodes-shalom"

# Opción B: instalación manual en el directorio custom de n8n
mkdir -p ~/.n8n/custom
npm install n8n-nodes-shalom --prefix ~/.n8n/custom/n8n-nodes-shalom
# Reinicia n8n
```
</Step>

<Step>
### Configura la credencial Shalom API [#configura-la-credencial-shalom-api]

Al arrastrar el nodo al flujo, crea la credencial Shalom API. El botón Test valida tu conexión contra GET /validate. Las operaciones Shalom Pro piden el instanceId como parámetro de cada nodo (p. ej. con la expresión \{\{ $json.instanceId \}\}).

```text title="credencial"
Base URL: https://api.shalom-api.lat
API Key:  sk_tu_api_key   (header x-api-key)

# Botón Test → valida contra GET /validate
```
</Step>

<Step>
### Conoce los 5 recursos del nodo [#conoce-los-5-recursos-del-nodo]

Agencias (listar y búsqueda avanzada) · Ubicaciones (departamentos, provincias, distritos) · Tracking (rastrear, lote, comprobante, etiqueta) · Cuentas (cotizar, DNI, registrar envío individual o masivo, pendientes, usuario) · Instancias (crear, listar, eliminar, login, estado, logout).

```text title="operaciones (endpoint mapeado)"
Agencias    → GET /agencies, GET /agencies/search
Ubicaciones → GET /locations/departments, .../provinces, .../districts
Tracking    → POST /track, POST /track/batch, GET /track/voucher, GET /track/label
Cuentas     → POST /account/quote, GET /account/dni/:dni, POST /account/register,
              POST /account/register-bulk, POST /account/pending-shipments, POST /account/get-user
Instancias  → POST /instances, GET /instances, DELETE /instances, POST /instances/login
```
</Step>

<Step>
### Flujo de ejemplo: registrar y rastrear [#flujo-de-ejemplo-registrar-y-rastrear]

Flujo típico de ecommerce: Instancias → Iniciar sesión (Shalom Pro) con instanceId + usuario + contraseña; Cuentas → Registrar envío individual (o masivo con shipments[] y securityCode); Tracking → Rastrear envío con orderNumber y orderCode; opcionalmente Tracking → Descargar comprobante para adjuntar el voucher.
</Step>

<Step>
### Sin polling: usa webhooks [#sin-polling-usa-webhooks]

Registra tu URL con PUT /webhooks y suscribe guías con POST /tracking/subscriptions (nodo Webhook de n8n apuntando a tu endpoint). Recibirás un POST firmado por cada cambio de estado sin consultar en bucle.
</Step>
</Steps>

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿El nodo funciona en n8n Cloud?">
No. Los nodos comunitarios requieren una instancia self-hosted de n8n.
</Accordion>

<Accordion title="¿Cómo valido la credencial del nodo?">
Con el botón Test de la credencial, que consulta GET /validate y confirma que la API key está activa.
</Accordion>

<Accordion title="¿Dónde va el instanceId?">
Como parámetro de cada operación de Shalom Pro, no en la credencial. Admite expresiones como =\{\{ $json.instanceId \}\}.
</Accordion>

<Accordion title="¿El nodo reemplaza a los webhooks?">
No. Para reaccionar a cambios de estado sin polling conviene usar webhooks; el nodo sirve para las llamadas puntuales del flujo.
</Accordion>

<Accordion title="¿Dónde está el código del nodo?">
En github.com/ronnaldrangel/n8n-nodes-shalom, y el paquete publicado se llama n8n-nodes-shalom en npm.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Webhooks de tracking" href="/docs/webhooks" />

<Card title="Configurar la credencial" href="/docs/autenticacion" />
</Cards>

# Instalar la skill de IA (https://docs.shalom-api.lat/docs/instalar-skill)

La skill de IA es un archivo SKILL.md (estándar de Agent Skills) que se publica en https://shalom-api.lat/skill/SKILL.md. Es corta a propósito: trae la autenticación, los límites y las reglas que rompen integraciones (instancias de Shalom Pro, códigos del catálogo, clave de recojo, formato de los pendientes, webhooks), y para el detalle de cada endpoint remite a esta documentación y a su llms.txt. Así no se desactualiza: los endpoints viven en un solo lugar. Compatible con Claude Code, Cursor, Codex, OpenCode, VS Code y cualquier agente que soporte el formato.

## Qué es una skill de IA [#qué-es-una-skill-de-ia]

Es un archivo de instrucciones que tu agente carga para saber cómo usar un servicio: qué endpoints existen, cómo autenticarse y qué formato tienen las respuestas. Sin ella, el agente improvisa y suele inventar parámetros.

La de Shalom API enseña al agente a rastrear envíos, buscar agencias, cotizar y crear guías en Shalom Pro.

## Dónde se instala [#dónde-se-instala]

Cada agente tiene su carpeta de skills. La guía cubre Claude Code, Cursor, Codex, OpenCode y VS Code. Si usas otro, el archivo SKILL.md sirve igual: es texto plano.

## La API key va en el entorno [#la-api-key-va-en-el-entorno]

No escribas la key dentro de la skill. El agente la lee de una variable de entorno, igual que haría tu código. Así puedes compartir la skill con tu equipo sin repartir credenciales.

## Comprobar que quedó lista [#comprobar-que-quedó-lista]

Pídele al agente que consulte las agencias de Cusco por API. Si responde con datos reales y no con un ejemplo inventado, la skill está activa y con la key bien configurada.

<Steps>
<Step>
### Descarga la skill desde shalom-api.lat [#descarga-la-skill-desde-shalom-apilat]

Crea la carpeta de skills de tu proyecto y descarga el SKILL.md servido en producción (se actualiza junto con la documentación). Codex y Antigravity leen .agents/skills/; en Claude Code usa .claude/skills/ en su lugar. Hay una guía por agente en shalom-api.lat/claude, /codex, /grokbot, /antigravity y /hermes. El agente detecta la skill al iniciar la siguiente sesión.

```bash
mkdir -p .agents/skills/shalom-api
curl -o .agents/skills/shalom-api/SKILL.md https://shalom-api.lat/skill/SKILL.md
```
</Step>

<Step>
### Configura tu API key [#configura-tu-api-key]

El agente necesita una API key para operar. Expórtala como variable de entorno en tu proyecto o pídesela al agente cuando la pida.

```bash
# .env de tu proyecto
SHALOM_API_KEY=sk_tu_api_key
```
</Step>

<Step>
### Prueba que la IA quedó lista [#prueba-que-la-ia-quedó-lista]

Con la skill instalada, pídele a tu agente algo concreto. Debería usar POST /track con tu API key y devolverte el estado de la guía.

```text title="prompt"
Usa la skill shalom-api: rastrea la guía 66479331 con código 3KTH y dime en qué estado está.
```
</Step>
</Steps>

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Qué es una skill de IA?">
Un archivo de instrucciones que tu agente carga para saber cómo usar un servicio: autenticación, reglas y dónde leer el detalle de cada endpoint.
</Accordion>

<Accordion title="¿Con qué agentes funciona la skill de Shalom API?">
Claude Code, Cursor, Codex, OpenCode y VS Code. En cualquier otro sirve el mismo archivo, porque es texto plano.
</Accordion>

<Accordion title="¿Dónde guardo la API key?">
En una variable de entorno, nunca dentro del archivo de la skill.
</Accordion>

<Accordion title="¿Cómo compruebo que la skill quedó bien instalada?">
Pidiéndole al agente que consulte agencias por API. Si devuelve datos reales, está funcionando.
</Accordion>

<Accordion title="¿El agente puede crear envíos reales con la skill?">
Puede, siempre que le des una instancia de Shalom Pro con sesión activa. Las operaciones Pro necesitan el instanceId.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Documentación de autenticación" href="/docs/autenticacion" />

<Card title="Rastrear envíos por API" href="/docs/tracking" />
</Cards>

# Cómo se actualiza el tracking (https://docs.shalom-api.lat/docs/optimizations)

Shalom API guarda una copia del estado de cada guía y la actualiza por su cuenta, para que POST /track responda rápido y los webhooks avisen sin que tengas que preguntar en bucle. Esta página explica qué guías se actualizan, cada cuánto y qué pasa al consultarlas. Nada de esto cambia los endpoints ni el formato de las respuestas.

## Solo las guías suscritas se actualizan en segundo plano [#solo-las-guías-suscritas-se-actualizan-en-segundo-plano]

La actualización automática es para las guías con una suscripción activa (POST /tracking/subscriptions): son las que necesitan un aviso por webhook en cuanto cambian. Al suscribir una guía nueva, su primera consulta en segundo plano ocurre dentro de los 30 minutos siguientes; el horario se reparte al azar para no consultar todas las guías a la vez.

Una guía que solo consultaste con POST /track, sin suscribirla, no se vigila en segundo plano. Para tu integración el resultado es el mismo, porque al consultarla se refresca en vivo (ver más abajo).

## Ritmo de actualización [#ritmo-de-actualización]

Las guías suscritas se consultan según su actividad. Una guía nueva se consulta cada 30 minutos en sus primeras 6 consultas y después cada 2 horas. Cuando cambia de estado, la siguiente consulta es a los 30 minutos. Al llegar a la agencia destino pasa a cada 2 horas y, cuando ya acumula 24 consultas, a cada 3 horas hasta que la entregan. Los horarios llevan una variación aleatoria pequeña.

Una vez entregada, la guía no se vuelve a consultar. Si una consulta falla, se reintenta antes: a los 5, 15, 30, 60 y 120 minutos.

## Refresco en vivo al consultar [#refresco-en-vivo-al-consultar]

Al llamar a POST /track, a cualquier hora y esté o no suscrita la guía, si el último dato guardado tiene más de 10 minutos se pide el estado en vivo y se espera hasta 8 segundos. Si llega a tiempo recibes el estado fresco; si no, recibes el último dato guardado y la consulta termina en segundo plano. Las guías ya entregadas responden siempre con el dato guardado.

Si esa consulta en vivo detecta un cambio, los suscriptores de la guía reciben su webhook en ese momento.

## Pausa nocturna [#pausa-nocturna]

Entre las 22:00 y las 06:00, hora de Lima, no se hacen consultas en segundo plano. Las consultas que tocaban de noche se reparten entre las 06:00 y las 07:30, así que un cambio ocurrido de noche se avisa desde las 06:00. POST /track no tiene pausa.

## Guías inactivas [#guías-inactivas]

Una guía deja de vigilarse en segundo plano cuando lleva 20 días sin movimiento o cuando Shalom la marca como devuelta o anulada. No se borra ni pierde su historial.

Vuelve a vigilarse si la consultas con POST /track y el estado en vivo muestra un cambio. También si la suscribes de nuevo con POST /tracking/subscriptions, aunque ya estuviera suscrita: eso la reactiva de inmediato y le da otros 20 días de seguimiento.

## Consultas en vivo más livianas [#consultas-en-vivo-más-livianas]

Cuando ya se conoce el ose_id de la guía, la actualización en segundo plano pide solo su línea de tiempo. Si cambió, descarga la guía completa para refrescar también el resto de datos (monto, estado de pago, agencias). Por eso la respuesta de POST /track y el webhook siempre traen los datos completos.

## Qué te conviene hacer [#qué-te-conviene-hacer]

Suscribe las guías de las que necesitas avisos automáticos justo después de crearlas. No consultes POST /track en bucle para enterarte de los cambios: usa webhooks. Si una guía estuvo detenida mucho tiempo y la vuelves a necesitar, suscríbela de nuevo.

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Por qué una guía no se actualiza sola si nadie la consulta?">
En segundo plano solo se vigilan las guías con una suscripción activa (POST /tracking/subscriptions). Una guía sin suscripción se actualiza cuando la consultas con POST /track: si el dato guardado tiene más de 10 minutos, se pide el estado en vivo antes de responder.
</Accordion>

<Accordion title="¿Recibo avisos de webhook de noche?">
Entre las 22:00 y las 06:00 (hora de Lima) las guías suscritas no se consultan en segundo plano, así que un cambio de la noche se avisa a partir de las 06:00, repartido hasta las 07:30. La excepción es que alguien consulte esa guía con POST /track de noche: si el estado en vivo cambió, el aviso sale en ese momento.
</Accordion>

<Accordion title="¿Qué pasa con una guía que lleva semanas sin moverse?">
Si pasa 20 días sin movimiento, o Shalom la marca como devuelta o anulada, deja de vigilarse en segundo plano. Vuelve a vigilarse si la consultas con POST /track y el estado cambió, o si la suscribes de nuevo.
</Accordion>

<Accordion title="¿Tengo que cambiar algo en mi integración?">
No. Los endpoints, las respuestas y el formato de los webhooks no cambian. Solo suscribe las guías de las que quieres recibir avisos automáticos.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Rastrear envíos" href="/docs/tracking" />

<Card title="Recibir cambios de estado por webhook" href="/docs/webhooks" />
</Cards>