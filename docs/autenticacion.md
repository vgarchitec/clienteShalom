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