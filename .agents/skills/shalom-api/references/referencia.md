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