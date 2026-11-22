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