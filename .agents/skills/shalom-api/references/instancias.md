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