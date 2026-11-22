const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

const ARTIFACT_DIR = 'C:\\Users\\ASUS\\.gemini\\antigravity\\brain\\3f47c025-b3a1-4d01-bea9-2270e97bf22f\\screenshots';
const LOCAL_DOCS_DIR = path.join(process.cwd(), 'docs', 'manual', 'screenshots');

[ARTIFACT_DIR, LOCAL_DOCS_DIR].forEach(dir => {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

async function main() {
    console.log('--- 1. Iniciando servidor PHP Laravel ---');
    const phpServer = spawn('php', ['-S', '127.0.0.1:8000', '-t', 'public']);
    phpServer.stderr.on('data', d => {
        // console.log('[PHP]', d.toString().trim());
    });

    console.log('--- 2. Iniciando Microsoft Edge Headless ---');
    const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
    const tempDir = path.join(process.cwd(), 'temp_edge_profile');
    if (!fs.existsSync(tempDir)) fs.mkdirSync(tempDir, { recursive: true });

    const edge = spawn(edgePath, [
        '--headless=new',
        '--remote-debugging-port=9222',
        `--user-data-dir=${tempDir}`,
        '--no-first-run',
        '--no-default-browser-check',
        '--window-size=1280,900',
        'http://127.0.0.1:8000'
    ]);

    let targets = null;
    for (let i = 0; i < 30; i++) {
        await new Promise(r => setTimeout(r, 600));
        try {
            const listRes = await fetch('http://127.0.0.1:9222/json/list');
            if (listRes.ok) {
                targets = await listRes.json();
                if (targets && targets.length > 0) break;
            }
        } catch (e) {}
    }

    if (!targets || targets.length === 0) {
        throw new Error('No se pudo conectar a Edge CDP');
    }

    const pageTarget = targets.find(t => t.type === 'page' && !t.url.startsWith('chrome-extension')) || targets[0];
    console.log('Conectando a WebSocket CDP:', pageTarget.url, pageTarget.webSocketDebuggerUrl);

    const ws = new WebSocket(pageTarget.webSocketDebuggerUrl);
    await new Promise((resolve, reject) => {
        ws.onopen = resolve;
        ws.onerror = reject;
    });

    let msgId = 1;
    function send(method, params = {}) {
        return new Promise((resolve) => {
            const id = msgId++;
            const timeout = setTimeout(() => {
                ws.removeEventListener('message', handler);
                resolve({});
            }, 10000);

            const handler = (evt) => {
                const msg = JSON.parse(evt.data);
                if (msg.id === id) {
                    clearTimeout(timeout);
                    ws.removeEventListener('message', handler);
                    resolve(msg.result || {});
                }
            };
            ws.addEventListener('message', handler);
            ws.send(JSON.stringify({ id, method, params }));
        });
    }

    await send('Page.enable');
    await send('Network.enable');
    await send('Emulation.setDeviceMetricsOverride', {
        width: 1280,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false
    });

    async function goto(url, delay = 1000) {
        await send('Page.navigate', { url });
        await new Promise(r => setTimeout(r, delay));
    }

    async function capture(filename, description) {
        console.log(`[Captura] ${filename} - ${description}`);
        await new Promise(r => setTimeout(r, 400));
        const result = await send('Page.captureScreenshot', { format: 'png' });
        if (result && result.data) {
            const buf = Buffer.from(result.data, 'base64');
            fs.writeFileSync(path.join(ARTIFACT_DIR, filename), buf);
            fs.writeFileSync(path.join(LOCAL_DOCS_DIR, filename), buf);
        } else {
            console.error(` -> Error capturando ${filename}`);
        }
    }

    async function clearSession() {
        await send('Network.clearBrowserCookies');
        await goto('http://127.0.0.1:8000/login', 600);
    }

    async function login(email, password) {
        console.log(`Iniciando sesión como ${email}...`);
        await goto('http://127.0.0.1:8000/login', 1000);
        await send('Runtime.evaluate', {
            expression: `
                (function() {
                    const loginForm = document.querySelector('form[action*="login"]');
                    if (!loginForm) return;
                    const emailInput = loginForm.querySelector('#email');
                    const passInput = loginForm.querySelector('#password');
                    if (emailInput) emailInput.value = '${email}';
                    if (passInput) passInput.value = '${password}';
                    const submitBtn = loginForm.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.click();
                })()
            `
        });
        await new Promise(r => setTimeout(r, 2000));
    }

    try {
        // ==========================================
        // 1. MÓDULO PÚBLICO / VISITANTE
        // ==========================================
        console.log('\n=== 1. Vistas Públicas ===');
        await clearSession();

        await goto('http://127.0.0.1:8000/', 1200);
        await capture('01_inicio_landing.png', 'Página de inicio y bienvenida');

        await goto('http://127.0.0.1:8000/ofertas', 1000);
        await capture('02_catalogo_ofertas.png', 'Buscador y catálogo de ofertas');

        await goto('http://127.0.0.1:8000/ofertas/1', 1000);
        await capture('03_detalle_oferta.png', 'Detalle público de una oferta laboral');

        await goto('http://127.0.0.1:8000/oportunidades', 1000);
        await capture('04_directorio_oportunidades.png', 'Directorio de becas, cursos y talleres');

        await goto('http://127.0.0.1:8000/oportunidades/1', 1000);
        await capture('05_detalle_oportunidad.png', 'Detalle de oportunidad de desarrollo local');

        await goto('http://127.0.0.1:8000/login', 1000);
        await capture('06_iniciar_sesion.png', 'Formulario de inicio de sesión');

        await goto('http://127.0.0.1:8000/register', 1000);
        await capture('07_registro_usuario.png', 'Formulario de registro de usuario');

        await goto('http://127.0.0.1:8000/forgot-password', 1000);
        await capture('08_recuperar_clave.png', 'Recuperación de contraseña');

        await goto('http://127.0.0.1:8000/reset-password/sample-token-1234?email=postulante@emplealocal.test', 1000);
        await capture('09_restablecer_clave.png', 'Restablecer contraseña con token');

        // ==========================================
        // 2. MÓDULO POSTULANTE
        // ==========================================
        console.log('\n=== 2. Vistas Rol Postulante ===');
        await clearSession();
        await login('postulante@emplealocal.test', 'password123');

        await goto('http://127.0.0.1:8000/dashboard', 1200);
        await capture('10_postulante_dashboard.png', 'Dashboard principal del postulante');

        await goto('http://127.0.0.1:8000/mis-postulaciones', 1000);
        await capture('11_postulante_mis_postulaciones.png', 'Historial y estado de postulaciones');

        await goto('http://127.0.0.1:8000/perfil', 1000);
        await capture('12_postulante_perfil.png', 'Vista de perfil profesional del postulante');

        await goto('http://127.0.0.1:8000/perfil/editar', 1000);
        await capture('13_postulante_perfil_editar.png', 'Edición de perfil profesional y CV');

        await goto('http://127.0.0.1:8000/perfil/password', 1000);
        await capture('14_postulante_cambiar_password.png', 'Actualización de contraseña');

        await goto('http://127.0.0.1:8000/ofertas/2', 1200);
        await capture('15_postulante_oferta_postulacion.png', 'Oferta con botón de postulación activa');

        // ==========================================
        // 3. MÓDULO EMPLEADOR
        // ==========================================
        console.log('\n=== 3. Vistas Rol Empleador ===');
        await clearSession();
        await login('empleador@emplealocal.test', 'password123');

        await goto('http://127.0.0.1:8000/dashboard', 1200);
        await capture('16_empleador_dashboard.png', 'Dashboard de control del empleador');

        await goto('http://127.0.0.1:8000/empleador/ofertas', 1000);
        await capture('17_empleador_mis_ofertas.png', 'Gestión de ofertas publicadas por la empresa');

        await goto('http://127.0.0.1:8000/empleador/ofertas/crear', 1000);
        await capture('18_empleador_crear_oferta.png', 'Formulario para publicar nueva oferta laboral');

        await goto('http://127.0.0.1:8000/empleador/ofertas/1/editar', 1000);
        await capture('19_empleador_editar_oferta.png', 'Formulario para editar oferta de trabajo');

        await goto('http://127.0.0.1:8000/empleador/postulantes', 1000);
        await capture('20_empleador_postulantes.png', 'Gestión y evaluación de candidatos postulados');

        await goto('http://127.0.0.1:8000/empleador/publicidades', 1000);
        await capture('21_empleador_publicidades.png', 'Gestión de anuncios comerciales de la empresa');

        await goto('http://127.0.0.1:8000/empleador/publicidades/crear', 1000);
        await capture('22_empleador_crear_publicidad.png', 'Creación y carga de banner publicitario');

        await goto('http://127.0.0.1:8000/perfil', 1000);
        await capture('23_empleador_perfil.png', 'Perfil corporativo del empleador');

        // ==========================================
        // 4. MÓDULO ADMINISTRADOR
        // ==========================================
        console.log('\n=== 4. Vistas Rol Administrador ===');
        await clearSession();
        await login('admin@emplealocal.test', 'password123');

        await goto('http://127.0.0.1:8000/dashboard', 1200);
        await capture('24_admin_dashboard.png', 'Dashboard administrativo con métricas y kpis');

        await goto('http://127.0.0.1:8000/admin/usuarios', 1000);
        await capture('25_admin_usuarios.png', 'Listado general de usuarios y roles');

        await goto('http://127.0.0.1:8000/admin/usuarios/2/editar', 1000);
        await capture('26_admin_editar_usuario.png', 'Edición administrativa de usuario y asignación de rol');

        await goto('http://127.0.0.1:8000/admin/ofertas', 1000);
        await capture('27_admin_moderacion_ofertas.png', 'Moderación y aprobación de ofertas de trabajo');

        await goto('http://127.0.0.1:8000/admin/ofertas/1/editar', 1000);
        await capture('28_admin_editar_oferta.png', 'Edición administrativa de una oferta');

        await goto('http://127.0.0.1:8000/admin/oportunidades', 1000);
        await capture('29_admin_oportunidades.png', 'Administración de cursos y oportunidades');

        await goto('http://127.0.0.1:8000/admin/oportunidades/create', 1000);
        await capture('30_admin_crear_oportunidad.png', 'Formulario para registrar nueva oportunidad');

        await goto('http://127.0.0.1:8000/admin/oportunidades/1/edit', 1000);
        await capture('31_admin_editar_oportunidad.png', 'Edición de oportunidad existente');

        await goto('http://127.0.0.1:8000/admin/publicidades', 1000);
        await capture('32_admin_publicidades.png', 'Moderación y aprobación de anuncios publicitarios');

        console.log('\n=== ¡Todas las 32 vistas capturadas exitosamente! ===');
    } finally {
        ws.close();
        edge.kill();
        phpServer.kill();
        try { fs.rmSync(tempDir, { recursive: true, force: true }); } catch {}
    }
}

main().catch(err => {
    console.error('Error durante la ejecución:', err);
    process.exit(1);
});
