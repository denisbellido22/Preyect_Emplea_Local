import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Tiempo real de Emplea Local (Laravel Reverb + Laravel Echo).
 *
 * La portada escucha el canal público "publicaciones". Si el servidor Reverb
 * no está disponible, la página sigue funcionando: sólo se muestra un aviso y
 * Pusher reintenta la conexión automáticamente.
 */
window.Pusher = Pusher;

document.addEventListener('DOMContentLoaded', () => {
    const seccion = document.getElementById('ultimas-oportunidades');

    if (!seccion) {
        return;
    }

    iniciarTiempoReal(seccion);
});

function iniciarTiempoReal(seccion) {
    const grid = seccion.querySelector('#oportunidades-grid');
    const plantilla = seccion.querySelector('#plantilla-oportunidad');
    const botonVerTodas = seccion.querySelector('#ver-todas-oportunidades');
    const mensajeVacio = seccion.querySelector('#oportunidades-vacio');
    const aviso = seccion.querySelector('#realtime-aviso');
    const indicador = seccion.querySelector('#realtime-indicador');
    const notificaciones = seccion.querySelector('#realtime-notificaciones');
    const contador = seccion.querySelector('#oportunidades-contador');

    if (!grid || !plantilla) {
        return;
    }

    const limite = parseInt(grid.dataset.visibleLimit || '6', 10);
    const clavesVistas = new Set(
        Array.from(grid.querySelectorAll('.opportunity-card')).map((card) => card.dataset.key)
    );

    let filtroActivo = 'todas';
    let expandido = false;

    /**
     * Aplica el filtro por categoría y el límite inicial de 6 tarjetas.
     */
    const aplicarVista = () => {
        const tarjetas = Array.from(grid.querySelectorAll('.opportunity-card'));
        let coincidencias = 0;

        tarjetas.forEach((tarjeta) => {
            const coincide = filtroActivo === 'todas' || tarjeta.dataset.categoria === filtroActivo;

            if (!coincide) {
                tarjeta.hidden = true;

                return;
            }

            coincidencias += 1;
            // Con un filtro activo se muestran todas las coincidencias.
            tarjeta.hidden = filtroActivo === 'todas' && !expandido && coincidencias > limite;
        });

        if (mensajeVacio) {
            mensajeVacio.hidden = coincidencias > 0;
        }

        if (botonVerTodas) {
            botonVerTodas.hidden = filtroActivo !== 'todas' || coincidencias <= limite;
            botonVerTodas.textContent = expandido ? 'Ver menos' : 'Ver todas las oportunidades';
            botonVerTodas.setAttribute('aria-expanded', expandido ? 'true' : 'false');
        }

        if (contador) {
            contador.textContent = coincidencias;
        }
    };

    const construirTarjeta = (publicacion) => {
        const fragmento = plantilla.content.cloneNode(true);
        const tarjeta = fragmento.querySelector('.opportunity-card');

        tarjeta.dataset.key = publicacion.key;
        tarjeta.dataset.categoria = publicacion.categoria;

        const asignar = (campo, valor) => {
            const nodo = tarjeta.querySelector(`[data-campo="${campo}"]`);

            if (nodo) {
                nodo.textContent = valor;
            }

            return nodo;
        };

        const contenedorMedia = tarjeta.querySelector('[data-campo="media"]');
        if (contenedorMedia) {
            if (publicacion.imagen) {
                contenedorMedia.innerHTML = '';
                const imagen = document.createElement('img');
                imagen.src = publicacion.imagen;
                imagen.alt = publicacion.titulo;
                imagen.loading = 'lazy';
                contenedorMedia.appendChild(imagen);
            } else {
                contenedorMedia.classList.add('is-placeholder');
                const icono = document.createElement('i');
                icono.className = 'bi ' + iconoPorCategoria(publicacion.categoria);
                icono.setAttribute('aria-hidden', 'true');
                contenedorMedia.innerHTML = '';
                contenedorMedia.appendChild(icono);
            }
        }

        asignar('categoria', publicacion.categoria_label);
        asignar('titulo', publicacion.titulo);
        asignar('institucion', publicacion.institucion || 'Emplea Local');
        asignar('descripcion', publicacion.descripcion);
        asignar('lugar', publicacion.lugar || 'Parinacochas');
        asignar('publicado', publicacion.publicado_legible || '');

        // Los postulantes autenticados ven "Postularme" directamente en las ofertas.
        const puedePostular = publicacion.tipo_publicacion === 'oferta'
            && document.body.dataset.role === 'postulante';

        const enlace = tarjeta.querySelector('[data-campo="url"]');
        if (enlace) {
            enlace.href = puedePostular ? publicacion.url + '#postular' : publicacion.url;
        }

        asignar('accion', puedePostular ? 'Postularme' : 'Ver detalles');

        const tituloEnlace = tarjeta.querySelector('[data-campo="titulo-enlace"]');
        if (tituloEnlace) {
            tituloEnlace.href = publicacion.url;
        }

        asignar('estado', publicacion.estado_label || '');

        const limiteContenedor = tarjeta.querySelector('[data-campo="limite-wrap"]');
        if (limiteContenedor) {
            if (publicacion.fecha_limite_legible) {
                asignar('limite', 'Hasta ' + publicacion.fecha_limite_legible);
            } else {
                limiteContenedor.hidden = true;
            }
        }

        const metaExtra = tarjeta.querySelector('[data-campo="tipo-trabajo"]');
        if (metaExtra) {
            if (publicacion.tipo_trabajo) {
                metaExtra.textContent = publicacion.tipo_trabajo;
            } else {
                metaExtra.hidden = true;
            }
        }

        const salario = tarjeta.querySelector('[data-campo="salario"]');
        if (salario) {
            if (publicacion.salario) {
                salario.textContent = publicacion.salario;
            } else {
                salario.hidden = true;
            }
        }

        return fragmento;
    };

    const marcarNueva = (tarjeta) => {
        tarjeta.classList.add('is-new');
        const etiqueta = tarjeta.querySelector('.opportunity-new');
        if (etiqueta) {
            etiqueta.hidden = false;
        }

        // La etiqueta NUEVO desaparece sola a los dos minutos, sin recargar.
        window.setTimeout(() => {
            tarjeta.classList.remove('is-new');
            if (etiqueta) {
                etiqueta.hidden = true;
            }
        }, 120000);
    };

    const insertarPublicacion = (publicacion) => {
        if (!publicacion?.key || clavesVistas.has(publicacion.key)) {
            return false;
        }

        clavesVistas.add(publicacion.key);
        const fragmento = construirTarjeta(publicacion);
        const tarjeta = fragmento.querySelector('.opportunity-card');
        marcarNueva(tarjeta);

        // La más reciente siempre entra en primera posición.
        grid.prepend(fragmento);
        aplicarVista();

        return true;
    };

    const actualizarPublicacion = (publicacion) => {
        if (!publicacion?.key) {
            return;
        }

        const existente = Array.from(grid.querySelectorAll('.opportunity-card'))
            .find((tarjeta) => tarjeta.dataset.key === publicacion.key);

        if (!existente) {
            insertarPublicacion(publicacion);

            return;
        }

        const fragmento = construirTarjeta(publicacion);
        const nueva = fragmento.querySelector('.opportunity-card');
        nueva.classList.add('opportunity-card--updated');
        existente.replaceWith(fragmento);
        aplicarVista();
    };

    const retirarPublicacion = (clave) => {
        const existente = Array.from(grid.querySelectorAll('.opportunity-card'))
            .find((tarjeta) => tarjeta.dataset.key === clave);

        if (existente) {
            existente.remove();
        }

        clavesVistas.delete(clave);
        aplicarVista();
    };

    const mostrarNotificacion = (publicacion) => {
        if (!notificaciones) {
            return;
        }

        const aviso = document.createElement('div');
        aviso.className = 'realtime-toast';
        aviso.setAttribute('role', 'status');

        const titulo = document.createElement('strong');
        titulo.textContent = '¡Nueva oportunidad disponible!';

        const detalle = document.createElement('span');
        detalle.textContent = publicacion.titulo;

        aviso.append(titulo, detalle);
        notificaciones.appendChild(aviso);

        // Como máximo tres avisos a la vez y sin repetir la misma publicación.
        while (notificaciones.children.length > 3) {
            notificaciones.firstElementChild.remove();
        }

        window.setTimeout(() => aviso.remove(), 6000);
    };

    const mostrarConexion = (estado) => {
        if (!aviso || !indicador) {
            return;
        }

        const mensajes = {
            connecting: 'Conectando al servicio en tiempo real…',
            connected: 'Tiempo real activo',
            unavailable: 'Sin conexión en tiempo real. Reintentando…',
            disconnected: 'Conexión en tiempo real perdida. Reintentando…',
            failed: 'No se pudo conectar al servicio en tiempo real. La página sigue disponible.',
        };

        if (estado === 'connected') {
            aviso.hidden = true;
            indicador.hidden = false;
            indicador.dataset.estado = 'conectado';
            indicador.textContent = 'En vivo';

            return;
        }

        aviso.textContent = mensajes[estado] || mensajes.unavailable;
        aviso.hidden = false;
        indicador.hidden = false;
        indicador.dataset.estado = 'desconectado';
        indicador.textContent = 'Reconectando';
    };

    if (botonVerTodas) {
        botonVerTodas.addEventListener('click', () => {
            expandido = !expandido;
            aplicarVista();
        });
    }

    seccion.querySelectorAll('[data-filtro]').forEach((boton) => {
        boton.addEventListener('click', () => {
            filtroActivo = boton.dataset.filtro || 'todas';

            seccion.querySelectorAll('[data-filtro]').forEach((otro) => {
                const activo = otro === boton;
                otro.classList.toggle('is-active', activo);
                otro.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });

            aplicarVista();
        });
    });

    aplicarVista();

    const clave = import.meta.env.VITE_REVERB_APP_KEY;
    if (!clave) {
        mostrarConexion('failed');

        return;
    }

    let echo;
    try {
        echo = new Echo({
            broadcaster: 'reverb',
            key: clave,
            wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
            wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
            wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (error) {
        console.error('No se pudo inicializar Laravel Echo:', error);
        mostrarConexion('failed');

        return;
    }

    if (typeof echo.connector?.onConnectionChange === 'function') {
        echo.connector.onConnectionChange(mostrarConexion);
        mostrarConexion(echo.connector.connectionStatus());
    } else if (echo.connector?.pusher?.connection) {
        echo.connector.pusher.connection.bind('state_change', (estados) => {
            mostrarConexion(estados.current);
        });
    }

    echo.channel('publicaciones')
        .listen('.publicacion.publicada', (evento) => {
            const publicacion = evento?.publicacion;
            const agregada = insertarPublicacion(publicacion);

            if (!agregada) {
                return;
            }

            // Si el visitante filtró otra categoría, se actualiza la lista sin
            // interrumpir su navegación y sin mostrar el aviso de esa tarjeta.
            if (filtroActivo === 'todas' || publicacion.categoria === filtroActivo) {
                mostrarNotificacion(publicacion);
            }
        })
        .listen('.publicacion.actualizada', (evento) => {
            actualizarPublicacion(evento?.publicacion);
        })
        .listen('.publicacion.retirada', (evento) => {
            if (evento?.key) {
                retirarPublicacion(evento.key);
            }
        });

    window.Echo = echo;
}

function iconoPorCategoria(categoria) {
    const iconos = {
        empleo: 'bi-briefcase',
        beca: 'bi-mortarboard',
        convocatoria: 'bi-megaphone',
        examen: 'bi-clipboard-check',
        practica: 'bi-person-workspace',
        capacitacion: 'bi-easel',
        curso: 'bi-journal-bookmark',
        charla: 'bi-chat-square-text',
        evento: 'bi-calendar-event',
        otra: 'bi-stars',
    };

    return iconos[categoria] || 'bi-stars';
}
