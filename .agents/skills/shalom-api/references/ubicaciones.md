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