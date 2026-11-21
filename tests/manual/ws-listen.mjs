// Listener manual por WebSocket real (protocolo Pusher de Reverb).
// Uso: KEY=... node tests/manual/ws-listen.mjs
const key = process.env.KEY;
const url = `ws://127.0.0.1:8080/app/${key}?protocol=7&client=emplea-local-check&version=1.0&flash=false`;

console.log('CONECTANDO', url.replace(key, '***'));

const ws = new WebSocket(url);

ws.onopen = () => {
    console.log('ABIERTO: WebSocket conectado a Reverb');
    ws.send(JSON.stringify({
        event: 'pusher:subscribe',
        data: { channel: 'publicaciones' },
    }));
};

ws.onmessage = (evento) => {
    const mensaje = JSON.parse(evento.data);

    if (mensaje.event === 'pusher:connection_established') {
        console.log('ESTABLECIDO', mensaje.data);
        return;
    }

    if (mensaje.event === 'pusher_internal:subscription_succeeded') {
        console.log('SUSCRITO al canal publicaciones');
        return;
    }

    if (mensaje.event?.startsWith('publicacion.')) {
        console.log('RECIBIDO', mensaje.event, mensaje.data);
        ws.close();
        process.exit(0);
    }

    console.log('MSG', mensaje.event);
};

ws.onerror = (error) => {
    console.error('ERROR WebSocket', error.message ?? error);
    process.exit(1);
};

setTimeout(() => {
    console.log('TIMEOUT sin eventos');
    process.exit(1);
}, 25000);
