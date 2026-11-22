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