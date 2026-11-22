# Conectar Claude o ChatGPT (https://docs.shalom-api.lat/docs/conectar-ia)

Shalom API tiene un conector MCP remoto. Al conectarlo, Claude o ChatGPT pueden rastrear tus guías, buscar agencias, cotizar, crear guías en tu cuenta de Shalom Pro y más, siempre con el permiso que elijas. Usa los mismos datos y la misma cuota que la API.

- **URL del conector:** `https://api.shalom-api.lat/mcp`
- **Autenticación:** OAuth. Al conectar se abre Shalom API para que inicies sesión y autorices el acceso. No necesitas copiar ninguna API key.

## Conectar en un minuto [#conectar-en-un-minuto]

<Steps>
<Step>
### Abre el Conector en el panel [#abre-el-conector-en-el-panel]

Entra a [shalom-api.lat/dashboard/conector](https://shalom-api.lat/dashboard/conector) y pulsa **Conectar** en Claude o en ChatGPT.
</Step>

<Step>
### Pega el mensaje en tu asistente [#pega-el-mensaje-en-tu-asistente]

Copia el mensaje que te muestra el panel y pégalo en un chat nuevo. El asistente te guía para agregar el conector personalizado.
</Step>

<Step>
### Autoriza el acceso [#autoriza-el-acceso]

Se abre la pantalla de permiso de Shalom API. Elige el nivel de acceso y pulsa **Autorizar**.
</Step>
</Steps>

## Hacerlo a mano [#hacerlo-a-mano]

<Tabs items={["Claude","ChatGPT","Claude Code"]}>
<Tab value="Claude">
En Claude, ve a **Configuración → Conectores → Agregar conector personalizado**, ponle de nombre `Shalom` y usa la URL `https://api.shalom-api.lat/mcp`. Después pulsa **Conectar** y autoriza.
</Tab>

<Tab value="ChatGPT">
En ChatGPT, activa el modo desarrollador en **Configuración → Aplicaciones y conectores → Configuración avanzada**. Luego crea un conector con la URL `https://api.shalom-api.lat/mcp` y autenticación OAuth.
</Tab>

<Tab value="Claude Code">
```bash
claude mcp add --transport http shalom https://api.shalom-api.lat/mcp
```

Luego ejecuta `/mcp` dentro de Claude Code para iniciar sesión.
</Tab>
</Tabs>

## Permisos [#permisos]

Al autorizar eliges uno de dos niveles. Puedes cambiarlo desconectando y volviendo a conectar.

| Nivel | Qué permite |
| --- | --- |
| **Lectura y gestión** | Consultar todo y además crear, anular y cambiar cosas (guías, autogestiones, instancias, API keys, webhooks). |
| **Solo lectura** | Consultar todo, sin cambiar nada. |

<Callout type="warn" title="Acciones que cuestan o no se pueden deshacer">
Crear guías (una o en lote), anular un envío pendiente, confirmar una autogestión y eliminar el webhook piden tu confirmación antes de ejecutarse: el asistente te muestra un resumen y espera tu respuesta.
</Callout>

- Cada herramienta consume cuota igual que su endpoint de la API (ver la [Referencia de la API](/docs/referencia)). Las que no consumen cuota lo dicen en su descripción.
- Si tu plan venció, no puedes hacer cambios y solo funcionan las consultas que no consumen cuota (tus instancias y su estado, API keys, webhook y guías vigiladas, tipos de contenido, historial de autogestiones, ubigeo de reparto, plan y consumo).
- Si no indicas la instancia, se usa la primera con la sesión iniciada; para iniciar sesión, la primera que no la tiene.
- Puedes ver y desconectar tus asistentes en el panel, en **Conector**.

## Qué puede hacer [#qué-puede-hacer]

| Área | Herramientas |
| --- | --- |
| Seguimiento | Rastrear una guía o hasta 50, buscar una orden por `ose_id`, ticket oficial (PDF o imagen) y rótulo en PDF |
| Agencias | Listar, buscar, las más cercanas a un punto, departamentos, provincias y distritos |
| Antes de enviar | Cotizar, tipos de contenido, consultar un DNI, buscar un destinatario guardado en tu cuenta |
| Instancias | Ver tus instancias y su estado, **crear una instancia** e **iniciar sesión en Shalom Pro** |
| Tu cuenta Shalom Pro | Envíos pendientes, en tránsito e historial, comprobantes, perfil del remitente, historial de autogestiones y ubigeo de reparto |
| Gestión de envíos | Crear guías (una o hasta 100 en lote), anular un envío pendiente, cambiar la clave de recojo, iniciar y confirmar autogestiones |
| API keys | Ver tus API keys (sin la key completa) y **crear una nueva** |
| Webhooks | Ver, configurar o eliminar el webhook, ver sus entregas, vigilar una guía y dejar de vigilarla |
| Cuenta | Plan, vencimiento y consumo del mes |

<Callout title="Tu contraseña de Shalom Pro">
Para iniciar sesión en una instancia desde el chat le das tu usuario y contraseña de Shalom Pro al asistente, así que quedan en esa conversación. Shalom API no la devuelve ni la guarda en sus registros. Si lo prefieres, inicia sesión desde el panel en **Instancias**.
</Callout>

**No puede:** borrar instancias, cerrar la sesión de Shalom Pro, borrar API keys, cambiar contraseñas, pagar o cambiar tu plan, ni nada de administración.