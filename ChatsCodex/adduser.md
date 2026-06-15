# Añadir avatar de usuario

> Implementar la funcionalidad de subir una imagen para poder ser utilizada como avatar de cada usuario en el formulario de edición del usuario. Controlar que solo se puedan subir archivos de imágenes, con dimensiones máximas de 680px x 680px y 1mb maximo de peso.
> Hay que añadir el campo pertinente en la tabla user. Las imágenes se guardarán en local storage/app/public/avatars.

<details><summary>22 previous messages</summary>

> Voy a revisar la estructura del proyecto y activar las guías de Laravel, Livewire, Flux, Tailwind y Pest. Después consultaré la documentación versionada con Boost antes de tocar código.
>
> <details><summary>Ran a command</summary>
>
> <details><summary>Explored 4 files</summary>
>
> - Read `./.agents/skills/laravel-best-practices/SKILL.md`
> - Read `./.agents/skills/pest-testing/SKILL.md`
> - Read `./.agents/skills/livewire-development/SKILL.md`
> - Read `./.agents/skills/fluxui-development/SKILL.md`
>
> </details>
>
> - Ran `printf '%s\n' '--- files ---'; rg --files -g '!vendor' -g '!node_modules' | head -200; printf '%s\n' '--- status ---'; git status --short`
>
> <details><summary>Explored a file</summary>
>
> - Read `./.agents/skills/tailwindcss-development/SKILL.md`
>
> </details>
>
> </details>
>
> El proyecto ya usa componentes Livewire de página y hay cambios locales ajenos en pedidos y traducciones; los dejaré intactos. Ahora localizaré las herramientas de Boost y revisaré el formulario, modelo, política y pruebas existentes para integrar el avatar siguiendo el patrón actual.
>
> <details><summary>Explored 10 files, ran a command</summary>
>
> <details><summary>Explored 4 files</summary>
>
> - Read `./resources/views/pages/users/⚡edit.blade.php`
> - Read `./tests/Feature/UserProfilePageTest.php`
> - Read `./tests/Feature/ProductImageTest.php`
> - Read `./database/migrations/0001_01_01_000000_create_users_table.php`
>
> </details>
>
> - Ran `php artisan list --raw | rg 'boost|livewire|make:migration|storage:link'`
>
> <details><summary>Explored 6 files</summary>
>
> - Read `./boost.json`
> - Read `./config/filesystems.php`
> - Read `./tests/Pest.php`
> - Read `./app/Models/User.php`
> - Read `./app/Policies/UserPolicy.php`
> - Read `./routes/web.php`
>
> </details>
>
> </details>
>
> La edición es un componente Livewire 4 de archivo único. Implementaré una subida opcional con `WithFileUploads`, validación de tipo/tamaño/dimensiones, reemplazo seguro del avatar anterior en el disco `public`, una columna nullable en `users` y pruebas de almacenamiento y rechazo de archivos inválidos.
>
> <details><summary>Explored 9 files, 4 searches, a list, ran 2 commands</summary>
>
> <details><summary>Explored 2 files, a search</summary>
>
> - Read `./resources/views/pages/users/⚡edit.blade.php`
> - Read `./database/factories/UserFactory.php`
> - Searched for `flux:avatar|avatar|temporaryUrl|WithFileUploads|dimensions:` in `resources`
>
> </details>
>
> - Ran `php artisan help boost:mcp; php artisan make:migration --help | sed -n '1,160p'; php artisan make:test --help | sed -n '1,180p'`
>
> <details><summary>Explored a search</summary>
>
> - Searched for `File::image|dimensions|maxWidth|maxHeight|TemporaryUploadedFile|store\(` in `livewire`
>
> </details>
>
> - Ran `test -L public/storage && ls -ld public/storage || true; rg -n '"Avatar"|"Choose|image|MB|dimensions|Upload' lang/en.json lang/es.json`
>
> <details><summary>Explored 8 files, 2 searches, a list</summary>
>
> - Read `./vendor/laravel/framework/src/Illuminate/Validation/Rule.php`
> - Read `./vendor/laravel/framework/src/Illuminate/Validation/Rules/ImageFile.php`
> - Read `./vendor/livewire/livewire/src/Features/SupportFileUploads/TemporaryUploadedFile.php`
> - Listed files
> - Searched for `search-docs|database-schema` in `boost`
> - Read `./lang/en.json`
> - Read `./lang/es.json`
> - Searched for `avatar` in `components`
> - Read `./vendor/laravel/framework/src/Illuminate/Validation/Rules/File.php`
> - Read `./vendor/laravel/framework/src/Illuminate/Validation/Rule.php`
> - Read `./vendor/laravel/framework/src/Illuminate/Validation/Rules/Dimensions.php`
>
> </details>
>
> </details>
>
> Voy a crear la migración con Artisan y modificar el componente de perfil, el modelo y sus pruebas. La interfaz mostrará una previsualización inmediata y conservará el avatar actual hasta que el formulario se guarde correctamente.
>
> <details><summary>Edited 4 files, ran a command</summary>
>
> - Ran `php artisan make:migration add_avatar_to_users_table --table=users --no-interaction`
> - Wrote `./app/Models/User.php` (+9 -1)
> - Wrote `./database/migrations/2026_06_15_073335_add_avatar_to_users_table.php` (+2 -2)
> - Wrote `./resources/views/pages/users/⚡edit.blade.php` (+58 -0)
> - Wrote `./tests/Feature/UserProfilePageTest.php` (+58 -0)
>
> </details>
>
> La primera implementación ya está aplicada. Ahora ejecutaré Pint y las pruebas específicas; si aparece alguna diferencia propia de Livewire 4 o de las reglas de archivo de Laravel 13, la ajustaré antes de cerrar.
>
> <details><summary>Ran 2 commands</summary>
>
> - Ran `vendor/bin/pint --dirty --format agent`
> - Ran `php artisan test --compact tests/Feature/UserProfilePageTest.php`
>
> </details>
>
> Las pruebas han detectado dos detalles del entorno existente: la fábrica genera teléfonos con formato libre y contamina estas pruebas, y la aserción de Livewire no expone las subreglas internas de `File::image()` por nombre. Ajustaré los datos de prueba para aislar exclusivamente la validación del avatar.
>
> <details><summary>Edited a file, ran 2 commands</summary>
>
> - Wrote `./tests/Feature/UserProfilePageTest.php` (+9 -6)
> - Ran `php artisan test --compact tests/Feature/UserProfilePageTest.php`
> - Ran `vendor/bin/pint --dirty --format agent`
>
> </details>
>
> Queda un caso real importante: si alguien fuerza un PDF pese al `accept="image/*"`, intentar previsualizarlo provoca una excepción antes de mostrar el error de validación. Voy a proteger la previsualización para que los archivos no imagen se rechacen limpiamente en el formulario.
>
> <details><summary>Edited 2 files, ran 3 commands</summary>
>
> - Wrote `./resources/views/pages/users/⚡edit.blade.php` (+1 -1)
> - Wrote `./tests/Feature/UserProfilePageTest.php` (+1 -1)
> - Ran `php artisan test --compact tests/Feature/UserProfilePageTest.php`
> - Ran `vendor/bin/pint --dirty --format agent`
> - Ran `git diff -- app/Models/User.php database/migrations/2026_06_15_073335_add_avatar_to_users_table.php 'resources/views/pages/users/⚡edit.blade.php' tests/Feature/UserProfilePageTest.php`
> - Wrote `./tests/Feature/UserProfilePageTest.php` (+1 -0)
>
> </details>
>
> Además he estabilizado una prueba preexistente que dependía de un teléfono aleatorio de la fábrica. Ejecutaré ahora la suite específica de forma aislada y después toda la suite para comprobar migraciones y regresiones.
>
> <details><summary>Ran a command</summary>
>
> - Ran `vendor/bin/pint --dirty --format agent && php artisan test --compact tests/Feature/UserProfilePageTest.php && php artisan test --compact`
>
> </details>
>
> La suite completa pasa: 67 pruebas y 1.613 aserciones. Falta comprobar el acceso público a los archivos; crearé el enlace estándar `public/storage` y revisaré visualmente la página con Laravel Herd.
>
> <details><summary>Ran 2 commands, called 2 tools</summary>
>
> - Ran `php artisan storage:link --no-interaction`
>
> <details><summary>Explored a file</summary>
>
> - Read `~/.codex/plugins/cache/openai-bundled/browser/26.609.41114/skills/control-in-app-browser/SKILL.md`
>
> </details>
>
> - Ran `git status --short; git diff --check; ls -ld public/storage 2>/dev/null || true`
>
> MCP tool call
>
> node_repl.js
>
> ```json
> {
>   "code": "var { setupBrowserRuntime } = await import('/Users/juanjosegonzalez/.codex/plugins/cache/openai-bundled/browser/26.609.41114/scripts/browser-client.mjs'); await setupBrowserRuntime({ globals: globalThis }); globalThis.browser = await agent.browsers.get('iab'); nodeRepl.write(await browser.documentation());",
>   "title": "Conectar con el navegador local",
>   "timeout_ms": 30000
> }
> ```
>
> ````text
> ## Additional Capabilities
> ### Browser Capabilities
> - `visibility`: Use to show or hide the browser to the user, and to determine the browser's current visibility. Keep browser work in the background unless the user asks to see it or live viewing is useful. When the browser should be visible, call set(true).
>   Read with `await (await browser.capabilities.get("visibility")).documentation()`.
> - `viewport`: Controls an explicit browser viewport override for responsive or device-size testing. Use it when a task calls for specific dimensions or breakpoint validation; otherwise leave it unset so the browser uses its normal 1280x720 viewport. Reset temporary overrides before finishing unless the user asked to keep them.
>   Read with `await (await browser.capabilities.get("viewport")).documentation()`.
> ### Tab Capabilities
> - `pageAssets`: List assets already observed in the current page state and bundle selected assets into a temporary local artifact.
>   Read with `await (await tab.capabilities.get("pageAssets")).documentation()`.
>
> ## Playwright
> Playwright is a critical part of the JavaScript API available to you.
>
> You only have access to a limited subset of the Playwright API, so only call functions that are explicitly defined.
> You do have access to `tab.playwright.evaluate(...)`, but only in a read-only page scope.
> Use locators for scoped interactions and targeted checks. For bulk DOM inspection, prefer one bounded read-only `evaluate(...)` that queries and projects the needed data. Avoid loops of locator property calls. In `evaluate(...)`, use basic DOM reads, limit returned elements, and do not assume globals or helpers such as `performance`, `NodeFilter`, `document.createTreeWalker`, or `FormData` exist.
>
> When using Playwright, keep and reuse a recent `tab.playwright.domSnapshot()` when it is available and you need it for locator construction or retry decisions. Treat the latest relevant snapshot as the source of truth for locator construction and retry decisions.
>
> ### Snapshot Discipline
> - Keep and reuse the latest relevant `domSnapshot()` until it proves stale or you need locator ground truth for UI that was not present in it.
> - Take a fresh `domSnapshot()` after navigation when you need to orient yourself or construct locators on the new page.
> - If a click times out, strict mode fails, or a selector parse error occurs, take a fresh `domSnapshot()` before forming the next locator.
> - Construct locators only from what appears in the latest snapshot. Do not guess labels, accessible names, or selectors.
> - Do not print full snapshot text repeatedly when a smaller excerpt, a `count()`, a specific attribute, or a direct locator check would answer the question with fewer tokens.
> - Do not discover page content by iterating through many results, cards, links, or rows and reading their text or attributes one by one.
> - Do not loop over a broad locator with `all()` and call `getAttribute(...)`, `textContent()`, or `innerText()` on each match. Each read crosses the browser boundary and becomes extremely expensive on large pages.
> - `locator.getAttribute(...)` is a single-element read, not a batch read. If the locator matches multiple elements, expect a strict-mode error rather than an array of attributes.
> - Use one broad observation to orient yourself: usually one fresh snapshot, or one screenshot if the visual structure is clearer than the DOM.
> - After that orientation step, narrow to the relevant section or a small number of strong candidates.
> - If the page is not getting narrower, do not scale up extraction across more elements. Change strategy instead.
> - Do not use `locator(...).allTextContents()`, `locator("body").textContent()`, or `locator("body").innerText()` as exploratory search tools across a page or large container.
> - Use broad text or attribute extraction only after you have already identified the exact container or element you need, and only when a smaller scoped check would not answer the question.
> - When you need many links, media URLs, or result titles, prefer a single `domSnapshot()` and parse the relevant lines, use the site's own search/filter UI, or navigate directly to a focused results page. Only fall back to per-element reads for a small, already-scoped set of candidates.
> - Do not use large body-text dumps, embedded app-state JSON such as `__NEXT_DATA__`, or repeated full-page extraction across multiple candidate pages as an exploratory search strategy.
> - Use large text or embedded JSON extraction only after you have already identified the relevant page, or when a site-specific skill explicitly depends on it.
>
> ### Hard Constraints For Playwright In This Runtime
> - Do not pass a regex as `name` to `getByRole(...)` in this environment. Use a plain string `name` only.
> - Do not use `.first()`, `.last()`, or `.nth()` unless you have just called `count()` on the same locator and explicitly confirmed why that position is correct.
> - Do not click, fill, or press on a locator until you have verified it resolves to exactly one element when uniqueness is not obvious.
> - Do not retry the same failing locator without a fresh `domSnapshot()`.
> - Do not use a guessed locator as an exploratory probe. If the latest snapshot does not clearly support the locator, do not spend timeout budget testing it.
> - Do not assume browser-side Playwright supports the full upstream API surface. If a method is not explicitly known to exist, do not call it.
> - Do not assume `locator(...).selectOption(...)` exists in this environment.
>
> ### Required Interaction Recipe
> Before every click, fill, select-like action, or press:
>
> 1. Reuse the latest relevant `domSnapshot()` when it still contains the locator ground truth you need. Take a fresh one only when it does not.
> 2. Build the most stable locator from the latest snapshot.
> 3. If uniqueness is not obvious from the selector itself, call `count()` on that locator.
> 4. Proceed only if the locator resolves to exactly one element.
> 5. Perform the action.
> 6. After the action, collect another observation only when the next decision requires it. Prefer a targeted state check when it answers the question; take a fresh snapshot when you need new locator ground truth.
>
> If `count()` is `0`:
>
> - The selector is wrong, stale, hidden, or the UI state is not ready.
> - Do not click anyway.
> - Do not wait on that locator to see if it eventually works.
> - Re-snapshot and rebuild the locator.
>
> If `count()` is greater than `1`:
>
> - The selector is ambiguous.
> - Scope to the correct container or switch to a stronger attribute.
> - Do not use `.first()` as a shortcut.
>
> ### Locator Strategy
> Build locators from what the snapshot actually shows, not what looks visually obvious.
>
> Prefer the most stable contract, in this order:
>
> 1. `data-testid`
> 2. Stable `data-*` attributes
> 3. Stable `href` (prefer exact or strong matches over broad substrings)
> 4. Scoped semantic role + accessible name using a string `name`
> 5. Scoped `getByText(...)`
> 6. Scoped CSS selectors via `locator(...)`
> 7. A scoped DOM-based click path or node-ID-based click when Playwright cannot produce a unique stable locator
>
> Use the most specific locator that is still durable.
>
> Treat a stable `href` as a strong hint, not proof of uniqueness. If multiple elements share the same `href`, scope to the correct card or container and confirm `count()` before clicking.
>
> Treat generic labels like `Menu`, `Main Menu`, `Help`, `Close`, `Default`, `Color`, `Size`, single-letter size labels such as `S`, `M`, `L`, `XL`, `Sort by`, `Search`, and `Add to cart` as ambiguous by default. Scope them to the correct container before acting.
>
> On search results, product grids, carousels, and modal-heavy pages, repeated `href`s and repeated generic labels are ambiguous by default. First identify the stable card or container, then scope the locator inside that container before clicking.
>
> ### Using `getByRole(..., { name })`
> - `name` is the accessible name, which may differ from visible text.
> - In the snapshot:
>   - `link "X"` usually reflects the accessible name.
>   - Nested text may be visible text only.
> - Use `getByRole` only when the accessible name is clearly present and likely unique in the latest snapshot.
>
> ### Interaction Best Practices
> - Scope before acting: find the right container or section first, then target the child element.
> - If you call `count()` on a locator, store the result in a local variable and reuse it unless the DOM changes.
> - Match the locator to the actual element type shown in the snapshot (link vs button vs menuitem vs generic text).
> - Do not assume every click navigates. If opening a menu or filter, wait for the expected UI state, not page load.
> - Prefer structured local signals such as selected control state, visible confirmation text, modal contents, a specific line item, or URL parameters over scraping broad result sections or dumping large parts of the page.
> - Do not add explicit `timeoutMs` to routine `click`, `fill`, `check`, or `setChecked` calls unless you have a concrete reason the target is slow to become actionable.
> - Reserve explicit timeout values for navigation, state transitions, or other known slow operations.
> - If you already know the exact destination URL and no click-side effect matters, prefer `tab.goto(url)` over a brittle locator click.
> - Do not reacquire `tab` inside each `node_repl` call. Reuse the existing `tab` binding to save tokens and preserve state. Only reacquire or reassign it when you intentionally switch tabs, after a kernel reset, or after a failed call that did not create the binding.
> - Do not use fixed sleeps as a default waiting strategy. After an action, prefer a concrete state check or targeted wait. Take a fresh snapshot when you need new locator ground truth.
> - If a fixed delay is truly unavoidable for a known transition, keep it short and follow it immediately with a specific verification step.
>
> ### Error Recovery
> - A strict mode violation means your locator is ambiguous.
> - Do not retry the same locator after a strict mode violation.
> - After strict mode fails, immediately inspect a fresh snapshot and rebuild the locator using tighter scope, a disambiguating container, or a stable attribute.
> - If a checkbox or radio exists but `check()` or `setChecked()` reports that it is hidden or did not change state, stop retrying the underlying input. Click its scoped visible associated `label[for]` or enclosing visible control once, then verify checked state.
> - A selector parse error means the locator syntax is invalid in this runtime.
> - Do not reuse the same locator form after a selector parse error.
> - A timeout usually means the target is missing, hidden, stale, offscreen, not yet rendered, or the selector is too broad.
> - Do not retry the same locator immediately after a timeout.
> - After a timeout, take a fresh snapshot, confirm the target still exists, and then either refine the locator or fall back to a more stable attribute.
> - If role or accessible-name targeting is unstable, fall back deliberately to a stable attribute (`data-*`, `href`, etc.), not brittle CSS structure.
> - If two locator attempts fail on the same target, stop escalating complexity on role or text locators. Switch to the most stable visible attribute from the snapshot or use a scoped DOM-based click path.
>
> ### Fallback Guidance
> - Prefer stable `href` values copied from the snapshot over guessed URL patterns.
> - Prefer scoped attribute selectors over global text selectors.
> - Use `getByText(...)` only when role-based or attribute-based locators are not reliable, and scope it to a container whenever possible.
> - Prefer attributes copied directly from the latest snapshot over inferred semantics, fragile CSS chains, or positional selectors.
> - Do not invent likely selectors. If the snapshot does not clearly expose a unique target, fetch a fresh snapshot and reassess before acting.
>
>
> ## API Reference
> Use this as the supported `agent.browsers.*` surface.
>
> ```ts
> // Installed by setupBrowserRuntime({ globals: globalThis }).
> const browser = await agent.browsers.get("iab");
> interface Agent {
>   browsers: Browsers; // API for finding and selecting browsers.
>   documentation: Documentation; // API for reading packaged browser-use documentation by name.
> }
>
> interface Browsers {
>   get(id: string): Promise<Browser>; // Get a browser by id or client type.
>   list(): Promise<Array<BrowserInfo>>; // List available browsers.
> }
>
> interface Browser {
>   browserId: string; // Browser id selected by `agent.browsers.get()`.
>   capabilities: BrowserCapabilityCollection; // Browser-scoped optional capabilities advertised by the connected backend; discover IDs with `await browser.capabilities.list()`, then call `await (await browser.capabilities.get(id)).documentation()` for method details.
>   tabs: Tabs; // API for interacting with browser tabs.
>   user: BrowserUser; // Readonly context about tabs in the user's browser windows.
>   documentation(): Promise<string>; // Read browser guidance and the core API reference.
>   nameSession(name: string): Promise<void>; // Name the current browser automation session.
> }
>
> interface BrowserUser {
>
>   openTabs(): Promise<Array<BrowserUserTabInfo>>; // List open top-level tabs across the user's browser windows ordered by `lastOpened` descending.
> }
>
> interface Tabs {
>
>   get(id: string): Promise<Tab>; // Get a tab by id.
>   list(): Promise<Array<TabInfo>>; // List open tabs in the browser.
>   new(): Promise<Tab>; // Create and return a new tab in the browser.
>   selected(): Promise<undefined | Tab>; // Return the currently selected tab, if any.
> }
>
> interface Tab {
>   capabilities: TabCapabilityCollection; // Tab-scoped optional capabilities advertised by the connected backend; discover IDs with `await tab.capabilities.list()`, then call `await (await tab.capabilities.get(id)).documentation()` for method details.
>   clipboard: TabClipboardAPI; // API for interacting with clipboard content in this tab.
>
>   cua: CUAAPI; // API for interacting with the tab via the cua api
>   dev: TabDevAPI; // API for developer-oriented tab inspection.
>   dom_cua: DomCUAAPI; // API for interacting with the tab via the dom based cua api
>   id: string; // A tab's unique identifier
>   playwright: PlaywrightAPI; // API for interacting with the tab via the playwright api
>   back(): Promise<void>; // Navigate this tab back in history.
>   close(): Promise<void>; // Close this tab.
>   forward(): Promise<void>; // Navigate this tab forward in history.
>   goto(url: string): Promise<void>; // Open a URL in this tab.
>   reload(): Promise<void>; // Reload this tab.
>   screenshot(options: ScreenshotOptions): Promise<Uint8Array>; // Capture a screenshot of this tab.
>   title(): Promise<undefined | string>; // Get the current title for this tab.
>   url(): Promise<undefined | string>; // Get the current URL for this tab.
> }
>
> interface CUAAPI {
>   click(options: ClickOptions): Promise<void>; // Click at a coordinate in the current viewport.
>   double_click(options: DoubleClickOptions): Promise<void>; // Double click at a coordinate in the current viewport.
>   
>   drag(options: DragOptions): Promise<void>; // Drag from a point to a point by the provided path.
>   keypress(options: KeypressOptions): Promise<void>; // Press control characters at the current focused element (focus it first via click/dblclick).
>   move(options: MoveOptions): Promise<void>; // Move the mouse to a point by the provided x and y coordinates.
>   scroll(options: ScrollOptions): Promise<void>; // Scroll by a delta from a specific viewport coordinate.
>   type(options: TypeOptions): Promise<void>; // Type text at the current focus.
> }
>
> interface DomCUAAPI {
>   click(options: DomClickOptions): Promise<void>; // Click a DOM node by its id from the visible DOM snapshot.
>   double_click(options: DomClickOptions): Promise<void>; // Double-click a DOM node by its id.
>   
>   get_visible_dom(): Promise<unknown>; // Return a filtered DOM with node ids for interactable elements.
>   keypress(options: DomKeypressOptions): Promise<void>; // Press control characters at the currently focused element (focus it first via click/dblclick).
>   scroll(options: DomScrollOptions): Promise<void>; // Scroll either the page or a specific node (if node_id provided) by deltas.
>   type(options: DomTypeOptions): Promise<void>; // Type text into the currently focused element (focus via click first).
> }
>
> interface PlaywrightAPI {
>   domSnapshot(): Promise<string>; // Return a snapshot of the current DOM as a string, including expanded iframe body content when available.
>
>   evaluate<TResult, TArg>(pageFunction: PlaywrightEvaluateFunction<TArg, TResult>, arg?: TArg, options?: PlaywrightEvaluateOptions): Promise<TResult>; // Evaluate JavaScript in a read-only page scope.
>   expectNavigation<T>(action: () => Promise<T>, options: { timeoutMs?: number; url?: string; waitUntil?: LoadState }): Promise<T>; // Expect a navigation triggered by an action.
>   frameLocator(frameSelector: string): PlaywrightFrameLocator; // Create a frame-scoped locator builder.
>   getByLabel(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by label text within the page.
>   getByPlaceholder(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by placeholder text within the page.
>   getByRole(role: string, options: { exact?: boolean; name?: TextMatcher }): PlaywrightLocator; // Find elements by ARIA role within the page.
>   getByTestId(testId: string): PlaywrightLocator; // Find elements by test id within the page.
>   getByText(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by text within the page.
>   locator(selector: string): PlaywrightLocator; // Create a locator scoped to this tab.
>   waitForEvent(event: "download", options?: WaitForEventOptions): Promise<PlaywrightDownload>; // Wait for the next event on the page.
>
>   waitForLoadState(options: PageWaitForLoadStateOptions): Promise<void>; // Wait for the page to reach a specific load state.
>   waitForTimeout(timeoutMs: number): Promise<void>; // Wait for a fixed duration.
>   waitForURL(url: string, options: PageWaitForURLOptions): Promise<void>; // Wait for the page URL to match the provided value.
> }
>
> interface PlaywrightFrameLocator {
>   frameLocator(frameSelector: string): PlaywrightFrameLocator; // Create a locator scoped to a nested frame.
>   getByLabel(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by label within this frame.
>   getByPlaceholder(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by placeholder within this frame.
>   getByRole(role: string, options: { exact?: boolean; name?: TextMatcher }): PlaywrightLocator; // Find elements by ARIA role within this frame.
>   getByTestId(testId: string): PlaywrightLocator; // Find elements by test id within this frame.
>   getByText(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by text within this frame.
>   locator(selector: string): PlaywrightLocator; // Create a locator scoped to this frame.
> }
>
> interface PlaywrightLocator {
>   all(): Promise<Array<PlaywrightLocator>>; // Resolve to a list of locators for each matched element.
>   allTextContents(options: { timeoutMs?: number }): Promise<Array<string>>; // Return `textContent` for *all* elements matched by this locator.
>   and(locator: PlaywrightLocator): PlaywrightLocator; // Return a locator matching elements that satisfy both this locator and `locator`.
>   check(options: LocatorCheckOptions): Promise<void>; // Check a checkbox or switch-like control.
>   click(options: LocatorClickOptions): Promise<void>; // Click the element matched by this locator.
>   count(): Promise<number>; // Number of elements matching this locator.
>   dblclick(options: LocatorClickOptions): Promise<void>; // Double-click the element matched by this locator.
>
>   fill(value: string, options: { timeoutMs?: number }): Promise<void>; // Replace the element's value with the provided text.
>   filter(options: LocatorFilterOptions): PlaywrightLocator; // Narrow this locator by additional constraints.
>   first(): PlaywrightLocator; // Return a locator pointing at the first matched element.
>   getAttribute(name: string, options: { timeoutMs?: number }): Promise<null | string>; // Return an attribute value from the first matched element.
>   getByLabel(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by label text, scoped to this locator.
>   getByPlaceholder(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by placeholder text, scoped to this locator.
>   getByRole(role: string, options: { exact?: boolean; name?: TextMatcher }): PlaywrightLocator; // Find elements by ARIA role, scoped to this locator.
>   getByTestId(testId: string): PlaywrightLocator; // Find elements by test id, scoped to this locator.
>   getByText(text: TextMatcher, options: { exact?: boolean }): PlaywrightLocator; // Find elements by text content, scoped to this locator.
>   innerText(options: { timeoutMs?: number }): Promise<string>; // Return the rendered (visible) text of the first matched element.
>   isEnabled(): Promise<boolean>; // Whether the first matched element is currently enabled.
>   isVisible(): Promise<boolean>; // Whether the first matched element is currently visible.
>   last(): PlaywrightLocator; // Return a locator pointing at the last matched element.
>   locator(selector: string, options: LocatorLocatorOptions): PlaywrightLocator; // Create a descendant locator scoped to this locator.
>   nth(index: number): PlaywrightLocator; // Return a locator pointing at the Nth matched element.
>   or(locator: PlaywrightLocator): PlaywrightLocator; // Return a locator matching elements that satisfy either this locator or `locator`.
>   press(value: string, options: { timeoutMs?: number }): Promise<void>; // Press a keyboard key while this locator is focused.
>   selectOption(value: SelectOptionInput | Array<SelectOptionInput>, options: { timeoutMs?: number }): Promise<void>; // Select one or more options on a native `<select>` element.
>   setChecked(checked: boolean, options: LocatorCheckOptions): Promise<void>; // Set a checkbox or switch-like control to a checked/unchecked state.
>   textContent(options: { timeoutMs?: number }): Promise<null | string>; // Return the raw textContent of the first matched element (or null if missing).
>   type(value: string, options: { timeoutMs?: number }): Promise<void>; // Type text into the element without clearing existing content.
>   uncheck(options: LocatorCheckOptions): Promise<void>; // Uncheck a checkbox or switch-like control.
>   waitFor(options: LocatorWaitForOptions): Promise<void>; // Wait for the element to reach a specific state.
> }
>
> interface PlaywrightDownload {
>
> }
>
> interface TabClipboardAPI {
>   read(): Promise<Array<TabClipboardItem>>; // Read clipboard items, including text and binary payloads.
>   readText(): Promise<string>; // Read plain text from the browser clipboard.
>   write(items: Array<TabClipboardItem>): Promise<void>; // Write clipboard items.
>   writeText(text: string): Promise<void>; // Write plain text to the browser clipboard.
> }
>
> interface TabDevAPI {
>   logs(options: TabDevLogsOptions): Promise<Array<TabDevLogEntry>>; // Read console log messages captured for this tab.
> }
>
> interface Documentation {
>   get(name: string): Promise<string>; // Read packaged documentation by its extensionless relative path.
> }
>
> interface BrowserInfo {
>   capabilities: ClientCapabilities;
>   id: string;
>   metadata?: Record<string, string>;
>   name: string;
>   type: ClientType;
> }
>
> type BrowserCapabilityCollection = {
>   get(id: string): Promise<unknown>;
>   list(): Promise<Array<{ id: string; description: string }>>;
> };
>
> interface BrowserUserTabInfo {
>   id: string; // Opaque identifier for this browser tab.
>   lastOpened?: string; // ISO 8601 timestamp for the last time the tab was opened or focused.
>   tabGroup?: string; // User-visible tab group name when the tab belongs to one.
>   title?: string; // User-visible tab title.
>   url?: string; // Current tab URL.
> }
>
> interface TabsContentOptions {
>
>   timeoutMs?: number; // Maximum time to wait for each page load, in milliseconds.
>   urls: Array<string>; // URLs to load in temporary background tabs.
> }
>
> interface TabsContentResult {
>
>   title: null | string; // The resolved page title when available.
>   url: string; // The resolved page URL when available, otherwise the requested URL.
> }
>
> interface FinalizeTabsOptions {
>   keep?: Array<FinalizeTabsKeep>; // Explicit tab dispositions to preserve after cleanup.
> }
>
> interface TabInfo {
>   id: string; // Metadata describing an open tab.
>   title?: string;
>   url?: string;
> }
>
> type TabCapabilityCollection = {
>   get(id: string): Promise<unknown>;
>   list(): Promise<Array<{ id: string; description: string }>>;
> };
>
> type ScreenshotOptions = {
>   clip?: ClipRect; // Crop to a specific rectangle instead of the full viewport.
>   fullPage?: boolean; // Capture the full page instead of the viewport.
> };
>
> type ClickOptions = {
>   button?: number; // Mouse button (1-left, 2-middle/wheel, 3-right, 4-back, 5-forward).
>   keypress?: Array<string>; // Modifier keys held during the click.
>   x: number;
>   y: number;
> };
>
> type DoubleClickOptions = {
>   keypress?: Array<string>; // Modifier keys held during the double click.
>   x: number;
>   y: number;
> };
>
> type DragOptions = {
>   keys?: Array<string>; // Optional modifier keys held during the drag.
>   path: Array<{ x: number; y: number }>; // Drag path as a list of points.
> };
>
> type KeypressOptions = {
>   keys: Array<string>; // Key combination to press.
> };
>
> type MoveOptions = {
>   keys?: Array<string>; // Optional modifier keys held while moving.
>   x: number;
>   y: number;
> };
>
> type ScrollOptions = {
>   keypress?: Array<string>; // Modifier keys held during scroll.
>   scrollX: number;
>   scrollY: number;
>   x: number;
>   y: number;
> };
>
> type TypeOptions = {
>   text: string;
> };
>
> type DomClickOptions = {
>   node_id: string; // Node id from `get_visible_dom()`.
> };
>
> type DomKeypressOptions = {
>   keys: Array<string>; // Key combination to press.
> };
>
> type DomScrollOptions = {
>   node_id?: string; // Optional node id to scroll within.
>   x: number; // Horizontal scroll delta.
>   y: number; // Vertical scroll delta.
> };
>
> type DomTypeOptions = {
>   text: string; // Text to type into the currently focused element.
> };
>
> type ElementInfoOptions = {
>   includeNonInteractable?: boolean; // When true, include non-interactable elements in addition to interactable targets.
>   x: number;
>   y: number;
> };
>
> type ElementInfo = {
>   ariaName?: string | null; // Accessible name if available.
>   boundingBox?: ElementInfoRect | null; // Element bounds in screenshot coordinates.
>   nodeId?: number | null; // Backend node id that can be passed to DOM-inspection APIs when available.
>   preview: string; // Compact human-readable node preview.
>   role?: string | null; // Computed ARIA role if available.
>   selector: ElementInfoSelector; // Suggested selector data for this element.
>   tagName: string; // Lowercased HTML tag name.
>   testId?: string | null; // Configured test id attribute if present.
>   visibleText?: string | null; // Rendered visible text, selected option text, or visible form value when available.
> };
>
> type ElementScreenshotOptions = {
>   includeNonInteractable?: boolean; // When true, highlight non-interactable elements in addition to interactable targets.
>   x: number;
>   y: number;
> };
>
> type PlaywrightEvaluateFunction<TArg, TResult> = string | (arg: TArg) => TResult | Promise<TResult>;
>
> type PlaywrightEvaluateOptions = {
>   timeoutMs?: number; // Maximum time to spend setting up the read-only DOM scope and running the script.
> };
>
> type LoadState = "load" | "domcontentloaded" | "networkidle";
>
> type TextMatcher = string | RegExp;
>
> type WaitForEventOptions = {
>   timeoutMs?: number;
> };
>
> type PageWaitForLoadStateOptions = {
>   state?: LoadState;
>   timeoutMs?: number;
> };
>
> type PageWaitForURLOptions = {
>   timeoutMs?: number;
>   waitUntil?: WaitUntil;
> };
>
> type LocatorCheckOptions = {
>   force?: boolean;
>   timeoutMs?: number;
> };
>
> type LocatorClickOptions = {
>   button?: MouseButton;
>   force?: boolean;
>   modifiers?: Array<KeyboardModifier>;
>   timeoutMs?: number;
> };
>
> type LocatorFilterOptions = {
>   has?: PlaywrightLocator;
>   hasNot?: PlaywrightLocator;
>   hasNotText?: TextMatcher;
>   hasText?: TextMatcher;
>   visible?: boolean;
> };
>
> type LocatorLocatorOptions = {
>   has?: PlaywrightLocator;
>   hasNot?: PlaywrightLocator;
>   hasNotText?: TextMatcher;
>   hasText?: TextMatcher;
> };
>
> type SelectOptionInput = string | SelectOptionDescriptor;
>
> type LocatorWaitForOptions = {
>   state: WaitForState;
>   timeoutMs?: number;
> };
>
> type TabClipboardItem = {
>   entries: Array<TabClipboardEntry>;
>   presentationStyle?: "unspecified" | "inline" | "attachment";
> };
>
> interface TabDevLogsOptions {
>   filter?: string; // Optional substring filter applied to the rendered log message.
>   levels?: Array<"debug" | "info" | "log" | "warn" | "error" | "warning">; // Optional levels to include.
>   limit?: number; // Maximum number of logs to return.
> }
>
> interface TabDevLogEntry {
>   level: "debug" | "info" | "log" | "warn" | "error"; // Console log level.
>   message: string; // Rendered log message text.
>   timestamp: string; // ISO 8601 timestamp for when the runtime captured the log.
>   url?: string; // Source URL reported by the browser runtime, when available.
> }
>
> interface ClientCapabilities {
>   browser?: Array<CapabilityInfo>;
>   tab?: Array<CapabilityInfo>;
> }
>
> type ClientType = "iab" | "extension" | "cdp";
>
> type TabsContentType = "html" | "text" | "domSnapshot";
>
> interface FinalizeTabsKeep {
>   status: FinalizeTabStatus; // Where the kept tab belongs after cleanup.
>   tab: string | Tab | TabInfo; // Tab object to keep open after browser cleanup.
> }
>
> type ClipRect = {
>   height: number;
>   width: number;
>   x: number;
>   y: number;
> };
>
> type ElementInfoRect = {
>   height: number;
>   width: number;
>   x: number;
>   y: number;
> };
>
> type ElementInfoSelector = {
>   candidates: Array<string>; // Ranked selector candidates for the element.
>   frameSelectors?: Array<string>; // Frame selectors to enter before using the element selector.
>   primary?: string | null; // The preferred selector for the element when available.
> };
>
> type WaitUntil = LoadState | "commit";
>
> type MouseButton = "left" | "right" | "middle";
>
> type KeyboardModifier = "Alt" | "Control" | "ControlOrMeta" | "Meta" | "Shift";
>
> type SelectOptionDescriptor = {
>   index?: number;
>   label?: string;
>   value?: string;
> };
>
> type WaitForState = "attached" | "detached" | "visible" | "hidden";
>
> type TabClipboardEntry = {
>   base64?: string;
>   mimeType: string;
>   text?: string;
> };
>
> interface CapabilityInfo {
>   description: string;
>   id: string;
> }
>
> type FinalizeTabStatus = "handoff" | "deliverable";
> ```
> ````
>
> MCP tool call
>
> node_repl.js
>
> ```json
> {
>   "code": "var tabsNow = await browser.tabs.list(); var tab = tabsNow.length ? await browser.tabs.get(tabsNow[0].id) : await browser.tabs.new(); await tab.goto('https://vuecommerce.test/login'); await tab.playwright.waitForLoadState({state:'domcontentloaded', timeoutMs:15000}); nodeRepl.write((await tab.url())+'\\n'+(await tab.title())+'\\n'+(await tab.playwright.domSnapshot()));",
>   "title": "Revisar la aplicación en Laravel Herd",
>   "timeout_ms": 30000
> }
> ```
>
> ```text
> https://vuecommerce.test/
> Vuecommerce
> - navigation:
>   - img "Your Company"
>   - link "Home":
>     - /url: https://vuecommerce.test
>   - link "Products":
>     - /url: https://vuecommerce.test/products
>   - link "Features":
>     - /url: "#"
>   - link "Marketplace":
>     - /url: "#"
>   - link "Company":
>     - /url: "#"
>   - link "Orders":
>     - /url: https://vuecommerce.test/orders
>   - button "Open options":
>     - generic: Open options
>   - radiogroup:
>     - radio:
>     - radio:
>     - radio [checked]:
>   - button "Open user menu":
>     - generic: Open user menu
> - heading "From the blog" [level=2]
> - paragraph: Learn how to grow your business with our expert advice.
> - article:
>   - time: Mar 16, 2020
>   - link "Marketing":
>     - /url: "#"
>   - heading "Boost your conversion rate" [level=3]:
>     - link "Boost your conversion rate":
>       - /url: "#"
>       - text: Boost your conversion rate
>   - paragraph: Illo sint voluptas. Error voluptates culpa eligendi. Hic vel totam vitae illo. Non aliquid explicabo necessitatibus unde. Sed exercitationem placeat consectetur nulla deserunt vel. Iusto corrupti dicta.
>   - paragraph:
>     - link "Michael Foster":
>       - /url: "#"
>       - text: Michael Foster
>   - paragraph: Co-Founder / CTO
> - article:
>   - time: Mar 10, 2020
>   - link "Sales":
>     - /url: "#"
>   - heading "How to use search engine optimization to drive sales" [level=3]:
>     - link "How to use search engine optimization to drive sales":
>       - /url: "#"
>       - text: How to use search engine optimization to drive sales
>   - paragraph: Optio cum necessitatibus dolor voluptatum provident commodi et. Qui aperiam fugiat nemo cumque.
>   - paragraph:
>     - link "Lindsay Walton":
>       - /url: "#"
>       - text: Lindsay Walton
>   - paragraph: Front-end Developer
> - article:
>   - time: Feb 12, 2020
>   - link "Business":
>     - /url: "#"
>   - heading "Improve your customer experience" [level=3]:
>     - link "Improve your customer experience":
>       - /url: "#"
>       - text: Improve your customer experience
>   - paragraph: Cupiditate maiores ullam eveniet adipisci in doloribus nulla minus. Voluptas iusto libero adipisci rem et corporis. Nostrud sint anim sunt aliqua. Nulla eu labore irure incididunt velit cillum quis magna dolore.
>   - paragraph:
>     - link "Tom Cook":
>       - /url: "#"
>       - text: Tom Cook
>   - paragraph: Director of Product
> - heading "Our team" [level=2]
> - paragraph: We’re a dynamic group of individuals who are passionate about what we do and dedicated to delivering the best results for our clients.
> - list:
>   - heading "Lindsay Walton" [level=3]
>   - paragraph: Front-end Developer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Courtney Henry" [level=3]
>   - paragraph: Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Tom Cook" [level=3]
>   - paragraph: Director of Product
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Whitney Francis" [level=3]
>   - paragraph: Copywriter
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Leonard Krasner" [level=3]
>   - paragraph: Senior Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Floyd Miles" [level=3]
>   - paragraph: Principal Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
> - heading "Free Shipping" [level=3]
> - paragraph: It's not actually free we just price it into the products. Someone's paying for it, and it's not us.
> - heading "24/7 Customer Support" [level=3]
> - paragraph: Our AI chat widget is powered by a naive series of if/else statements. Guaranteed to irritate.
> - heading "Fast Shopping Cart" [level=3]
> - paragraph: Look how fast that cart is going. What does this mean for the actual experience? I don't know.
> - heading "Gift Cards" [level=3]
> - paragraph: Buy them for your friends, especially if they don't like our store. Free money for us, it's great.
> - contentinfo:
>   - contentinfo:
>     - navigation "Footer":
>       - link "Home":
>         - /url: https://vuecommerce.test
>       - link "Products":
>         - /url: https://vuecommerce.test/products
>       - link "Features":
>         - /url: "#"
>       - link "Marketplace":
>         - /url: "#"
>       - link "Company":
>         - /url: "#"
>       - link "Orders":
>         - /url: https://vuecommerce.test/orders
>       - link "About":
>         - /url: "#"
>       - link "Blog":
>         - /url: "#"
>       - link "Jobs":
>         - /url: "#"
>       - link "Press":
>         - /url: "#"
>       - link "Accessibility":
>         - /url: "#"
>       - link "Partners":
>         - /url: "#"
>     - link "Facebook":
>       - /url: "#"
>       - generic: Facebook
>     - link "Instagram":
>       - /url: "#"
>       - generic: Instagram
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "GitHub":
>       - /url: "#"
>       - generic: GitHub
>     - link "YouTube":
>       - /url: "#"
>       - generic: YouTube
>     - paragraph: © 2024 Your Company, Inc. All rights reserved.
> - generic: Request
> - generic: Timeline
> - generic: Views
> - generic: "28"
> - generic: Queries
> - generic: "3"
> - generic: Models
> - generic: "1"
> - generic: Livewire
> - generic: "2"
> - generic: "2"
> - generic: GET /
> - generic: 37.69ms
> - generic: 2MB
> - generic: 13.x
> ```
>
> </details>
>
> Auto-review
>
> Status: approved
> Risk: low
> Rationale: Auto-review returned a low-risk allow decision.
>
> <details><summary>Called 3 tools</summary>
>
> MCP tool call
>
> node_repl.js
>
> ```json
> {
>   "code": "var userMenuButton = tab.playwright.getByRole('button',{name:'Open user menu', exact:true}); var userMenuCount = await userMenuButton.count(); if (userMenuCount !== 1) throw new Error('Expected one user menu button, found '+userMenuCount); await userMenuButton.click(); nodeRepl.write(await tab.playwright.domSnapshot());",
>   "title": "Abrir el perfil de usuario",
>   "timeout_ms": 30000
> }
> ```
>
> ```text
> - navigation:
>   - img "Your Company"
>   - link "Home":
>     - /url: https://vuecommerce.test
>   - link "Products":
>     - /url: https://vuecommerce.test/products
>   - link "Features":
>     - /url: "#"
>   - link "Marketplace":
>     - /url: "#"
>   - link "Company":
>     - /url: "#"
>   - link "Orders":
>     - /url: https://vuecommerce.test/orders
>   - button "Open options":
>     - generic: Open options
>   - radiogroup:
>     - radio:
>     - radio:
>     - radio [checked]:
>   - button "Open user menu" [expanded]:
>     - generic: Open user menu
>   - menu "Open user menu" [active]:
>     - menuitem "Your profile"
>     - menuitem "My purchases"
>     - paragraph: JuanJota JotaJuan
>     - paragraph: test@example.com
>     - menuitem "Sign out"
> - heading "From the blog" [level=2]
> - paragraph: Learn how to grow your business with our expert advice.
> - article:
>   - time: Mar 16, 2020
>   - link "Marketing":
>     - /url: "#"
>   - heading "Boost your conversion rate" [level=3]:
>     - link "Boost your conversion rate":
>       - /url: "#"
>       - text: Boost your conversion rate
>   - paragraph: Illo sint voluptas. Error voluptates culpa eligendi. Hic vel totam vitae illo. Non aliquid explicabo necessitatibus unde. Sed exercitationem placeat consectetur nulla deserunt vel. Iusto corrupti dicta.
>   - paragraph:
>     - link "Michael Foster":
>       - /url: "#"
>       - text: Michael Foster
>   - paragraph: Co-Founder / CTO
> - article:
>   - time: Mar 10, 2020
>   - link "Sales":
>     - /url: "#"
>   - heading "How to use search engine optimization to drive sales" [level=3]:
>     - link "How to use search engine optimization to drive sales":
>       - /url: "#"
>       - text: How to use search engine optimization to drive sales
>   - paragraph: Optio cum necessitatibus dolor voluptatum provident commodi et. Qui aperiam fugiat nemo cumque.
>   - paragraph:
>     - link "Lindsay Walton":
>       - /url: "#"
>       - text: Lindsay Walton
>   - paragraph: Front-end Developer
> - article:
>   - time: Feb 12, 2020
>   - link "Business":
>     - /url: "#"
>   - heading "Improve your customer experience" [level=3]:
>     - link "Improve your customer experience":
>       - /url: "#"
>       - text: Improve your customer experience
>   - paragraph: Cupiditate maiores ullam eveniet adipisci in doloribus nulla minus. Voluptas iusto libero adipisci rem et corporis. Nostrud sint anim sunt aliqua. Nulla eu labore irure incididunt velit cillum quis magna dolore.
>   - paragraph:
>     - link "Tom Cook":
>       - /url: "#"
>       - text: Tom Cook
>   - paragraph: Director of Product
> - heading "Our team" [level=2]
> - paragraph: We’re a dynamic group of individuals who are passionate about what we do and dedicated to delivering the best results for our clients.
> - list:
>   - heading "Lindsay Walton" [level=3]
>   - paragraph: Front-end Developer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Courtney Henry" [level=3]
>   - paragraph: Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Tom Cook" [level=3]
>   - paragraph: Director of Product
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Whitney Francis" [level=3]
>   - paragraph: Copywriter
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Leonard Krasner" [level=3]
>   - paragraph: Senior Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
>   - heading "Floyd Miles" [level=3]
>   - paragraph: Principal Designer
>   - list:
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "LinkedIn":
>       - /url: "#"
>       - generic: LinkedIn
> - heading "Free Shipping" [level=3]
> - paragraph: It's not actually free we just price it into the products. Someone's paying for it, and it's not us.
> - heading "24/7 Customer Support" [level=3]
> - paragraph: Our AI chat widget is powered by a naive series of if/else statements. Guaranteed to irritate.
> - heading "Fast Shopping Cart" [level=3]
> - paragraph: Look how fast that cart is going. What does this mean for the actual experience? I don't know.
> - heading "Gift Cards" [level=3]
> - paragraph: Buy them for your friends, especially if they don't like our store. Free money for us, it's great.
> - contentinfo:
>   - contentinfo:
>     - navigation "Footer":
>       - link "Home":
>         - /url: https://vuecommerce.test
>       - link "Products":
>         - /url: https://vuecommerce.test/products
>       - link "Features":
>         - /url: "#"
>       - link "Marketplace":
>         - /url: "#"
>       - link "Company":
>         - /url: "#"
>       - link "Orders":
>         - /url: https://vuecommerce.test/orders
>       - link "About":
>         - /url: "#"
>       - link "Blog":
>         - /url: "#"
>       - link "Jobs":
>         - /url: "#"
>       - link "Press":
>         - /url: "#"
>       - link "Accessibility":
>         - /url: "#"
>       - link "Partners":
>         - /url: "#"
>     - link "Facebook":
>       - /url: "#"
>       - generic: Facebook
>     - link "Instagram":
>       - /url: "#"
>       - generic: Instagram
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "GitHub":
>       - /url: "#"
>       - generic: GitHub
>     - link "YouTube":
>       - /url: "#"
>       - generic: YouTube
>     - paragraph: © 2024 Your Company, Inc. All rights reserved.
> - generic: Request
> - generic: Timeline
> - generic: Views
> - generic: "28"
> - generic: Queries
> - generic: "3"
> - generic: Models
> - generic: "1"
> - generic: Livewire
> - generic: "2"
> - generic: "2"
> - generic: GET /
> - generic: 37.69ms
> - generic: 2MB
> - generic: 13.x
> ```
>
> MCP tool call
>
> node_repl.js
>
> ```json
> {
>   "code": "var profileItem = tab.playwright.getByRole('menuitem',{name:'Your profile', exact:true}); var profileCount = await profileItem.count(); if (profileCount !== 1) throw new Error('Expected one profile item, found '+profileCount); await tab.playwright.expectNavigation(()=>profileItem.click(),{waitUntil:'domcontentloaded', timeoutMs:15000}); nodeRepl.write((await tab.url())+'\\n'+(await tab.playwright.domSnapshot()));",
>   "title": "Verificar el formulario de avatar",
>   "timeout_ms": 30000
> }
> ```
>
> ```text
> https://vuecommerce.test/users/1/edit
> - navigation:
>   - img "Your Company"
>   - link "Home":
>     - /url: https://vuecommerce.test
>   - link "Products":
>     - /url: https://vuecommerce.test/products
>   - link "Features":
>     - /url: "#"
>   - link "Marketplace":
>     - /url: "#"
>   - link "Company":
>     - /url: "#"
>   - link "Orders":
>     - /url: https://vuecommerce.test/orders
>   - button "Open options":
>     - generic: Open options
>   - radiogroup:
>     - radio:
>     - radio:
>     - radio [checked]:
>   - button "Open user menu":
>     - generic: Open user menu
> - main:
>   - generic: Your profile
>   - paragraph: Update the account information and security settings.
>   - generic: Personal information
>   - paragraph: Information used to identify and contact this user.
>   - generic: JJ
>   - generic: Avatar
>   - button "Avatar"
>   - generic: Choose file
>   - generic: No file chosen
>   - generic: Image up to 680 x 680 pixels and 1 MB.
>   - generic: First name
>   - textbox "First name":
>     - /placeholder: Enter the first name
>     - text: JuanJota
>   - generic: Last name
>   - textbox "Last name":
>     - /placeholder: Enter the last name
>     - text: JotaJuan
>   - generic: Email
>   - textbox "Email":
>     - /placeholder: Enter the email address
>     - text: test@example.com
>   - generic: Phone
>   - textbox "Phone":
>     - /placeholder: Enter the phone number
>   - generic: Administration
>   - paragraph: Manage account status, verification, and permissions.
>   - generic: Status
>   - combobox "Status":
>     - option "Select a status" [disabled]
>     - option "Active" [selected]
>     - option "Pending"
>     - option "Suspend"
>   - generic: Email verified
>   - switch "Email verified" [checked]
>   - generic: Administrator
>   - switch "Administrator" [checked]
>   - generic: Password
>   - paragraph: Leave these fields empty to keep the current password.
>   - generic: New password
>   - textbox "New password":
>     - /placeholder: Enter a new password
>   - button "Toggle password visibility":
>   - generic: Confirm password
>   - textbox "Confirm password":
>     - /placeholder: Repeat the new password
>   - button "Toggle password visibility":
>   - button "Save changes":
>     - generic: Save changes
> - contentinfo:
>   - contentinfo:
>     - navigation "Footer":
>       - link "Home":
>         - /url: https://vuecommerce.test
>       - link "Products":
>         - /url: https://vuecommerce.test/products
>       - link "Features":
>         - /url: "#"
>       - link "Marketplace":
>         - /url: "#"
>       - link "Company":
>         - /url: "#"
>       - link "Orders":
>         - /url: https://vuecommerce.test/orders
>       - link "About":
>         - /url: "#"
>       - link "Blog":
>         - /url: "#"
>       - link "Jobs":
>         - /url: "#"
>       - link "Press":
>         - /url: "#"
>       - link "Accessibility":
>         - /url: "#"
>       - link "Partners":
>         - /url: "#"
>     - link "Facebook":
>       - /url: "#"
>       - generic: Facebook
>     - link "Instagram":
>       - /url: "#"
>       - generic: Instagram
>     - link "X":
>       - /url: "#"
>       - generic: X
>     - link "GitHub":
>       - /url: "#"
>       - generic: GitHub
>     - link "YouTube":
>       - /url: "#"
>       - generic: YouTube
>     - paragraph: © 2024 Your Company, Inc. All rights reserved.
> - generic: Request
> - generic: Timeline
> - generic: Views
> - generic: "131"
> - generic: Queries
> - generic: "4"
> - generic: Models
> - generic: "2"
> - generic: Livewire
> - generic: "2"
> - generic: Gate
> - generic: "1"
> - generic: GET /users/1/edit
> - generic: 72.17ms
> - generic: 3MB
> - generic: 13.x
> ```
>
> MCP tool call
>
> node_repl.js
>
> ```json
> {
>   "code": "var browserErrors = await tab.dev.logs({levels:['error'], limit:20}); nodeRepl.write(JSON.stringify(browserErrors));",
>   "title": "Comprobar errores del navegador",
>   "timeout_ms": 30000
> }
> ```
>
> ```text
> []
> ```
>
> </details>
>
> La revisión visual confirma que el selector, las restricciones y el avatar de iniciales se renderizan correctamente, sin errores de consola. Aplicaré la migración pendiente en el entorno local para que la subida quede operativa también fuera de las pruebas.
>
> <details><summary>Ran 3 commands</summary>
>
> - Ran `php artisan migrate --no-interaction && git diff --check && git status --short`
> - Ran `php artisan migrate --no-interaction`
> - Ran `git diff --check && git status --short && ls -ld public/storage`
>
> </details>
</details>

Implementada la subida de avatares:

- Columna nullable `users.avatar`.
- Almacenamiento en `storage/app/public/avatars`.
- Validación: imágenes, máximo `680x680 px` y `1 MB`.
- Previsualización y reemplazo del avatar anterior.
- Enlace `public/storage` creado.
- Migración aplicada.

Verificado con Pint, navegador y suite completa: **67 pruebas, 1.613 aserciones**.
