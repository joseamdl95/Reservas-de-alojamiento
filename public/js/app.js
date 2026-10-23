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