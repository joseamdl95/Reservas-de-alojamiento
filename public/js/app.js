document.addEventListener('DOMContentLoaded', () => {

    const formFiltros = document.getElementById('form-filtros');
    const tbody = document.getElementById('tabla-reservas');
    const btnLimpiar = document.getElementById('btn-limpiar');

    const btnNueva = document.getElementById('btn-nueva');
    const contenedorForm = document.getElementById('form-crear');
    const formReserva = document.getElementById('form-reserva');
    const btnCerrarForm = document.getElementById('btn-cerrar-form');
    const erroresForm = document.getElementById('errores-form');

    async function cargarReservas(filtros = {}) {
        const params = new URLSearchParams();

        for (const [clave, valor] of Object.entries(filtros)) {
            if (valor !== '' && valor !== null && valor !== undefined) {
                params.append(clave, valor);
            }
        }

        const url = '/api/reservas' + (params.toString() ? '?' + params.toString() : '');

        const respuesta = await fetch(url);
        const reservas = await respuesta.json();
        renderizarTabla(reservas);
    }

    if (formFiltros) {
        formFiltros.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(formFiltros);
            const filtros = Object.fromEntries(formData.entries());
            await cargarReservas(filtros);
        });
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', async () => {
            formFiltros.reset();
            await cargarReservas();
        });
    }

    if (btnNueva && contenedorForm) {
        btnNueva.addEventListener('click', () => {
            contenedorForm.style.display = 'block';
            btnNueva.style.display = 'none';
        });
    }

    if (btnCerrarForm && contenedorForm) {
        btnCerrarForm.addEventListener('click', () => {
            formReserva.reset();
            limpiarErroresForm();
            contenedorForm.style.display = 'none';
            btnNueva.style.display = 'inline-block';
        });
    }

    // Submit del formulario de creación/edición
    if (formReserva) {
        formReserva.addEventListener('submit', async (e) => {
            e.preventDefault();
            limpiarErroresForm();

            const modo = formReserva.dataset.modo;  
            const formData = new FormData(formReserva);
            const data = Object.fromEntries(formData.entries());

            data.importe = parseFloat(data.importe);

            if (data.notas === '') {
                delete data.notas;
            }

            const url = modo === 'editar'
                ? `/api/reservas/${data.id}`
                : '/api/reservas';

            const metodo = modo === 'editar' ? 'PUT' : 'POST';

            try {
                const respuesta = await fetch(url, {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });

                if (respuesta.status === 201 || respuesta.status === 200) {
                    formReserva.reset();
                    contenedorForm.style.display = 'none';
                    btnNueva.style.display = 'inline-block';
                    await cargarReservas();
                    return;
                }

                if (respuesta.status === 400) {
                    const errorData = await respuesta.json();
                    mostrarErroresForm(errorData.errors || { error: errorData.error });
                    return;
                }

                mostrarErroresForm({ general: 'Error inesperado del servidor' });
            } catch (err) {
                mostrarErroresForm({ general: 'Error de conexión: ' + err.message });
            }
        });
    }

    function mostrarErroresForm(errores) {
        if (!erroresForm) return;

        const html = Object.entries(errores)
            .map(([campo, msg]) => `<p><strong>${escaparHtml(campo)}:</strong> ${escaparHtml(msg)}</p>`)
            .join('');

        erroresForm.innerHTML = `<div class="alerta alerta-error">${html}</div>`;
    }

    function limpiarErroresForm() {
        if (erroresForm) {
            erroresForm.innerHTML = '';
        }
    }

    const accionesEstado = document.getElementById('acciones-estado');
    const estadoValor = document.getElementById('estado-valor');

    if (accionesEstado && estadoValor) {
        const reservaId = accionesEstado.dataset.reservaId;

        accionesEstado.addEventListener('click', async (e) => {
            const boton = e.target.closest('button');
            if (!boton) return;

            let nuevoEstado = null;
            let mensaje = '';

            if (boton.id === 'btn-cancelar') {
                nuevoEstado = 'cancelada';
                mensaje = '¿Seguro que quieres cancelar esta reserva?';
            } else if (boton.id === 'btn-confirmar') {
                nuevoEstado = 'confirmada';
                mensaje = '¿Confirmar esta reserva?';
            } else {
                return;
            }

            if (!confirm(mensaje)) return;

            await cambiarEstado(reservaId, nuevoEstado, boton);
        });
    }

   async function cambiarEstado(id, nuevoEstado, boton) {
    const textoOriginal = boton.textContent;
    boton.disabled = true;
    boton.textContent = 'Procesando...';

    try {
        const respuesta = await fetch(`/api/reservas/${id}/estado`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ estado: nuevoEstado }),
        });

        if (respuesta.ok) {
            const data = await respuesta.json();
            actualizarEstadoUI(nuevoEstado);
            await recargarTimeline(id);

            if (data.message && data.message.includes('ya era')) {
                alert('El estado ya era el mismo');
            }
            return;
        }

        if (respuesta.status === 404) {
            alert('La reserva ya no existe. Recargando...');
            window.location.reload();
            return;
        }

        const errorData = await respuesta.json();
        alert('Error: ' + (errorData.error || 'No se pudo cambiar el estado'));
        boton.disabled = false;
        boton.textContent = textoOriginal;
    } catch (err) {
        alert('Error de conexión: ' + err.message);
        boton.disabled = false;
        boton.textContent = textoOriginal;
    }
}

    function actualizarEstadoUI(nuevoEstado) {
        estadoValor.textContent = capitalizar(nuevoEstado);
        estadoValor.className = 'estado estado-' + nuevoEstado;

        accionesEstado.innerHTML = '';

        if (nuevoEstado !== 'cancelada') {
            const btnCancelar = document.createElement('button');
            btnCancelar.id = 'btn-cancelar';
            btnCancelar.textContent = 'Cancelar reserva';
            accionesEstado.appendChild(btnCancelar);
        }

        if (nuevoEstado === 'pendiente') {
            const btnConfirmar = document.createElement('button');
            btnConfirmar.id = 'btn-confirmar';
            btnConfirmar.textContent = 'Confirmar reserva';
            accionesEstado.appendChild(btnConfirmar);
        }

    }

    async function recargarTimeline(id) {
        const respuesta = await fetch(`/api/reservas/${id}/eventos`);
        if (!respuesta.ok) return;

        const eventos = await respuesta.json();
        const contenedorTimeline = document.querySelector('.timeline');

        if (!contenedorTimeline) {
            // No existía timeline (reserva sin eventos). Lo creamos.
            const h3Historial = Array.from(document.querySelectorAll('h3'))
                .find(h => h.textContent.trim() === 'Historial');
            if (!h3Historial) return;

            const ul = document.createElement('ul');
            ul.className = 'timeline';
            h3Historial.parentNode.insertBefore(ul, h3Historial.nextSibling);
            ul.innerHTML = construirItemsTimeline(eventos);
            return;
        }

        contenedorTimeline.innerHTML = construirItemsTimeline(eventos);
    }

    function construirItemsTimeline(eventos) {
        if (eventos.length === 0) return '';

        return eventos.map(ev => `
            <li>
                <strong>${capitalizar(ev.tipo)}</strong>
                <span class="fecha">${formatearFechaHora(ev.fecha_creacion)}</span>
                <p>${escaparHtml(ev.descripcion || '')}</p>
            </li>
        `).join('');
    }

    function formatearFechaHora(fechaISO) {
        // '2026-10-23 15:04:00' → '23/10/2026 15:04'
        const [fecha, hora] = fechaISO.split(' ');
        const [anio, mes, dia] = fecha.split('-');
        const [h, m] = hora.split(':');
        return `${dia}/${mes}/${anio} ${h}:${m}`;
    }

    
    function renderizarTabla(reservas) {
        if (reservas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">No hay reservas que mostrar.</td></tr>';
            return;
        }

        tbody.innerHTML = reservas.map(r => `
            <tr>
                <td>${escaparHtml(r.nombre_huesped)}</td>
                <td>${escaparHtml(r.nombre_alojamiento)}</td>
                <td>${formatearFecha(r.fecha_entrada)}</td>
                <td>${formatearFecha(r.fecha_salida)}</td>
                <td>${capitalizar(r.estado)}</td>
                <td>${formatearImporte(r.importe)} €</td>
                <td><a href="/reserva/${r.id}">Ver</a></td>
            </tr>
        `).join('');
    }

    function escaparHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function formatearFecha(fechaISO) {
        const [anio, mes, dia] = fechaISO.split('-');
        return `${dia}/${mes}/${anio}`;
    }

    function capitalizar(texto) {
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }

    function formatearImporte(importe) {
        return parseFloat(importe).toFixed(2).replace('.', ',');
    }
});