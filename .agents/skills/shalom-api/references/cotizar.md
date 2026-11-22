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