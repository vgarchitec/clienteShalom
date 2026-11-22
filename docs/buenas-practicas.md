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