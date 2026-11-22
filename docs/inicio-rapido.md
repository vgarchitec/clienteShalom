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