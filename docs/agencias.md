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