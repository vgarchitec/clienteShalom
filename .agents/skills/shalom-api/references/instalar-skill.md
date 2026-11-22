# Instalar la skill de IA (https://docs.shalom-api.lat/docs/instalar-skill)

La skill de IA es un archivo SKILL.md (estándar de Agent Skills) que se publica en https://shalom-api.lat/skill/SKILL.md. Es corta a propósito: trae la autenticación, los límites y las reglas que rompen integraciones (instancias de Shalom Pro, códigos del catálogo, clave de recojo, formato de los pendientes, webhooks), y para el detalle de cada endpoint remite a esta documentación y a su llms.txt. Así no se desactualiza: los endpoints viven en un solo lugar. Compatible con Claude Code, Cursor, Codex, OpenCode, VS Code y cualquier agente que soporte el formato.

## Qué es una skill de IA [#qué-es-una-skill-de-ia]

Es un archivo de instrucciones que tu agente carga para saber cómo usar un servicio: qué endpoints existen, cómo autenticarse y qué formato tienen las respuestas. Sin ella, el agente improvisa y suele inventar parámetros.

La de Shalom API enseña al agente a rastrear envíos, buscar agencias, cotizar y crear guías en Shalom Pro.

## Dónde se instala [#dónde-se-instala]

Cada agente tiene su carpeta de skills. La guía cubre Claude Code, Cursor, Codex, OpenCode y VS Code. Si usas otro, el archivo SKILL.md sirve igual: es texto plano.

## La API key va en el entorno [#la-api-key-va-en-el-entorno]

No escribas la key dentro de la skill. El agente la lee de una variable de entorno, igual que haría tu código. Así puedes compartir la skill con tu equipo sin repartir credenciales.

## Comprobar que quedó lista [#comprobar-que-quedó-lista]

Pídele al agente que consulte las agencias de Cusco por API. Si responde con datos reales y no con un ejemplo inventado, la skill está activa y con la key bien configurada.

<Steps>
<Step>
### Descarga la skill desde shalom-api.lat [#descarga-la-skill-desde-shalom-apilat]

Crea la carpeta de skills de tu proyecto y descarga el SKILL.md servido en producción (se actualiza junto con la documentación). Codex y Antigravity leen .agents/skills/; en Claude Code usa .claude/skills/ en su lugar. Hay una guía por agente en shalom-api.lat/claude, /codex, /grokbot, /antigravity y /hermes. El agente detecta la skill al iniciar la siguiente sesión.

```bash
mkdir -p .agents/skills/shalom-api
curl -o .agents/skills/shalom-api/SKILL.md https://shalom-api.lat/skill/SKILL.md
```
</Step>

<Step>
### Configura tu API key [#configura-tu-api-key]

El agente necesita una API key para operar. Expórtala como variable de entorno en tu proyecto o pídesela al agente cuando la pida.

```bash
# .env de tu proyecto
SHALOM_API_KEY=sk_tu_api_key
```
</Step>

<Step>
### Prueba que la IA quedó lista [#prueba-que-la-ia-quedó-lista]

Con la skill instalada, pídele a tu agente algo concreto. Debería usar POST /track con tu API key y devolverte el estado de la guía.

```text title="prompt"
Usa la skill shalom-api: rastrea la guía 66479331 con código 3KTH y dime en qué estado está.
```
</Step>
</Steps>

## Preguntas frecuentes [#preguntas-frecuentes]

<Accordions type="single">
<Accordion title="¿Qué es una skill de IA?">
Un archivo de instrucciones que tu agente carga para saber cómo usar un servicio: autenticación, reglas y dónde leer el detalle de cada endpoint.
</Accordion>

<Accordion title="¿Con qué agentes funciona la skill de Shalom API?">
Claude Code, Cursor, Codex, OpenCode y VS Code. En cualquier otro sirve el mismo archivo, porque es texto plano.
</Accordion>

<Accordion title="¿Dónde guardo la API key?">
En una variable de entorno, nunca dentro del archivo de la skill.
</Accordion>

<Accordion title="¿Cómo compruebo que la skill quedó bien instalada?">
Pidiéndole al agente que consulte agencias por API. Si devuelve datos reales, está funcionando.
</Accordion>

<Accordion title="¿El agente puede crear envíos reales con la skill?">
Puede, siempre que le des una instancia de Shalom Pro con sesión activa. Las operaciones Pro necesitan el instanceId.
</Accordion>
</Accordions>

## Relacionado [#relacionado]

<Cards>
<Card title="Documentación de autenticación" href="/docs/autenticacion" />

<Card title="Rastrear envíos por API" href="/docs/tracking" />
</Cards>