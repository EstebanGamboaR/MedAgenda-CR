// =====================================================
// MEDAGENDA-CR - SCRIPT PRINCIPAL (CON BACKEND)
// =====================================================

// Configuración de la API
const API_URL = '/backend/api';

// Variables globales
let todasLasCitas = [];
let citasEnEspera = [];
let usuarioActual = null;

// =====================================================
// AUTENTICACIÓN
// =====================================================

// Verificar si hay sesión activa
async function verificarSesion() {
    try {
        const response = await fetch(`${API_URL}/verificar_sesion.php`);
        const data = await response.json();
        
        if (data.ok && data.usuario) {
            usuarioActual = data.usuario;
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error al verificar sesión:', error);
        return false;
    }
}

// Cerrar sesión
async function cerrarSesion() {
    try {
        await fetch(`${API_URL}/logout.php`, { method: 'POST' });
        window.location.href = 'login.html';
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    }
}

// =====================================================
// GESTIÓN DE CITAS - CRUD
// =====================================================

// Cargar todas las citas desde el backend
async function cargarCitas() {
    try {
        const response = await fetch(`${API_URL}/obtener_citas.php`);
        const data = await response.json();
        
        if (data.ok) {
            todasLasCitas = data.citas || [];
            citasEnEspera = todasLasCitas.filter(c => c.estado === 'en_espera');
            return todasLasCitas;
        } else {
            console.error('Error al cargar citas:', data.error);
            return [];
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        mostrarNotificacion('Error al cargar las citas', 'error');
        return [];
    }
}

// Crear nueva cita
async function crearCita(datosFormulario) {
    try {
        // Calcular triage
        const sintomas = obtenerSintomasSeleccionados();
        const score = calcularScoreTriage(sintomas);
        const prioridad = obtenerPrioridadDesdScore(score);
        
        // Preparar datos para backend
        const citaData = {
            nombre_paciente: datosFormulario.get('name'),
            telefono: datosFormulario.get('phone'),
            email: datosFormulario.get('email') || '',
            fecha: datosFormulario.get('date'),
            hora: datosFormulario.get('time'),
            motivo: datosFormulario.get('reason') || '',
            sintomas: JSON.stringify(convertirSintomasAObjeto(sintomas)),
            puntaje_triage: score,
            prioridad: prioridad.label
        };

        const response = await fetch(`${API_URL}/crear_cita.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(citaData)
        });

        const data = await response.json();

        if (data.ok) {
            mostrarNotificacion('¡Cita agendada exitosamente!', 'success');
            return data.cita;
        } else {
            mostrarNotificacion(data.error || 'Error al crear la cita', 'error');
            return null;
        }
    } catch (error) {
        console.error('Error al crear cita:', error);
        mostrarNotificacion('Error de conexión al crear la cita', 'error');
        return null;
    }
}

// Actualizar cita existente
async function actualizarCita(citaId, datosActualizados) {
    try {
        const response = await fetch(`${API_URL}/actualizar_cita.php`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: citaId,
                ...datosActualizados
            })
        });

        const data = await response.json();

        if (data.ok) {
            mostrarNotificacion('Cita actualizada correctamente', 'success');
            await cargarCitas();
            return true;
        } else {
            mostrarNotificacion(data.error || 'Error al actualizar', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error al actualizar cita:', error);
        mostrarNotificacion('Error de conexión', 'error');
        return false;
    }
}

// Eliminar cita
async function eliminarCita(citaId) {
    if (!confirm('¿Estás seguro de eliminar esta cita?')) {
        return false;
    }

    try {
        const response = await fetch(`${API_URL}/eliminar_cita.php`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: citaId })
        });

        const data = await response.json();

        if (data.ok) {
            mostrarNotificacion('Cita eliminada correctamente', 'success');
            await cargarCitas();
            return true;
        } else {
            mostrarNotificacion(data.error || 'Error al eliminar', 'error');
            return false;
        }
    } catch (error) {
        console.error('Error al eliminar cita:', error);
        mostrarNotificacion('Error de conexión', 'error');
        return false;
    }
}

// Cambiar estado de cita
async function cambiarEstadoCita(citaId, nuevoEstado) {
    return await actualizarCita(citaId, { estado: nuevoEstado });
}

// =====================================================
// ESTADÍSTICAS
// =====================================================

async function obtenerEstadisticas() {
    try {
        const response = await fetch(`${API_URL}/estadisticas.php`);
        const data = await response.json();
        
        if (data.ok) {
            return data.estadisticas;
        }
        return null;
    } catch (error) {
        console.error('Error al obtener estadísticas:', error);
        return null;
    }
}

// =====================================================
// SISTEMA DE TRIAGE
// =====================================================

const SINTOMAS = [
    { id: 1, nombre: 'Fiebre mayor a 38°C', valor: 3, emoji: '🌡️' },
    { id: 2, nombre: 'Dolor en el pecho', valor: 5, emoji: '💔' },
    { id: 3, nombre: 'Dificultad para respirar', valor: 4, emoji: '😮‍💨' },
    { id: 4, nombre: 'Sangrado abundante', valor: 5, emoji: '🩸' },
    { id: 5, nombre: 'Dolor abdominal intenso', valor: 4, emoji: '🤰' },
    { id: 6, nombre: 'Mareos o pérdida de conciencia', valor: 5, emoji: '😵' },
    { id: 7, nombre: 'Lesión por accidente', valor: 3, emoji: '🤕' },
    { id: 8, nombre: 'Complicación en el embarazo', valor: 5, emoji: '🤰' },
    { id: 9, nombre: 'Tos persistente', valor: 2, emoji: '😷' },
    { id: 10, nombre: 'Control de rutina', valor: 0, emoji: '📋' }
];

function calcularScoreTriage(sintomasIds) {
    let score = 0;
    sintomasIds.forEach(id => {
        const sintoma = SINTOMAS.find(s => s.id === id);
        if (sintoma) score += sintoma.valor;
    });
    return score;
}

function obtenerPrioridadDesdScore(score) {
    if (score >= 8) {
        return { label: 'Emergencia', key: 'emergency', className: 'priority-emergency' };
    } else if (score >= 4) {
        return { label: 'Muy urgente', key: 'very-urgent', className: 'priority-very-urgent' };
    } else if (score >= 2) {
        return { label: 'Urgente / Moderada', key: 'medium', className: 'priority-medium' };
    } else if (score >= 1) {
        return { label: 'No urgente', key: 'low', className: 'priority-low' };
    } else {
        return { label: 'Control de rutina', key: 'routine', className: 'priority-routine' };
    }
}

function obtenerSintomasSeleccionados() {
    const checkboxes = document.querySelectorAll('input[name="symptoms"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

function convertirSintomasAObjeto(sintomasIds) {
    const objeto = {};
    SINTOMAS.forEach(sintoma => {
        const key = sintoma.nombre.toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_]/g, '');
        objeto[key] = sintomasIds.includes(sintoma.id);
    });
    return objeto;
}

// =====================================================
// PANEL ADMINISTRATIVO
// =====================================================

async function inicializarPanelAdmin() {
    // Verificar sesión
    const sesionValida = await verificarSesion();
    if (!sesionValida) {
        window.location.href = 'login.html';
        return;
    }

    // Mostrar nombre de usuario
    const userNameElement = document.getElementById('userName');
    if (userNameElement && usuarioActual) {
        userNameElement.textContent = usuarioActual.nombre;
    }

    // Cargar datos
    await cargarCitas();
    await actualizarEstadisticas();
    mostrarCitas();
    mostrarListaEspera();

    // Event listeners
    configurarEventListeners();
}

async function actualizarEstadisticas() {
    const stats = await obtenerEstadisticas();
    if (!stats) return;

    // Actualizar cards
    document.getElementById('totalCitas').textContent = stats.total_citas || 0;
    document.getElementById('emergencias').textContent = stats.emergencias || 0;
    document.getElementById('muyUrgentes').textContent = stats.muy_urgentes || 0;
    document.getElementById('enEspera').textContent = stats.en_espera || 0;
}

function mostrarCitas() {
    const tbody = document.getElementById('citasTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (todasLasCitas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay citas registradas</td></tr>';
        return;
    }

    todasLasCitas
        .filter(c => c.estado !== 'en_espera')
        .forEach(cita => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${cita.nombre_paciente}</td>
                <td>${cita.telefono}</td>
                <td>${formatearFecha(cita.fecha)}</td>
                <td>${cita.hora}</td>
                <td><span class="badge-priority ${obtenerClasePrioridad(cita.prioridad)}">${cita.prioridad}</span></td>
                <td><span class="badge-status ${obtenerClaseEstado(cita.estado)}">${formatearEstado(cita.estado)}</span></td>
                <td>
                    <button class="btn-icon" onclick="editarCita(${cita.id})" title="Editar">✏️</button>
                    <button class="btn-icon" onclick="cambiarEstadoCita(${cita.id}, 'en_espera')" title="Lista de espera">⏳</button>
                    <button class="btn-icon" onclick="eliminarCita(${cita.id})" title="Eliminar">🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
}

function mostrarListaEspera() {
    const container = document.getElementById('listaEsperaContainer');
    if (!container) return;

    container.innerHTML = '';

    if (citasEnEspera.length === 0) {
        container.innerHTML = '<p class="text-muted">No hay citas en lista de espera</p>';
        return;
    }

    citasEnEspera.forEach(cita => {
        const div = document.createElement('div');
        div.className = 'espera-item';
        div.innerHTML = `
            <div>
                <strong>${cita.nombre_paciente}</strong><br>
                <small>${formatearFecha(cita.fecha)} - ${cita.hora}</small><br>
                <span class="badge-priority ${obtenerClasePrioridad(cita.prioridad)}">${cita.prioridad}</span>
            </div>
            <div>
                <button class="btn-sm" onclick="cambiarEstadoCita(${cita.id}, 'pendiente')">Reasignar</button>
                <button class="btn-sm btn-danger" onclick="eliminarCita(${cita.id})">Eliminar</button>
            </div>
        `;
        container.appendChild(div);
    });
}

// =====================================================
// UTILIDADES
// =====================================================

function formatearFecha(fecha) {
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-CR');
}

function formatearEstado(estado) {
    const estados = {
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmada',
        'en_espera': 'En espera',
        'atendida': 'Atendida',
        'cancelada': 'Cancelada'
    };
    return estados[estado] || estado;
}

function obtenerClasePrioridad(prioridad) {
    const clases = {
        'Emergencia': 'priority-emergency',
        'Muy urgente': 'priority-very-urgent',
        'Urgente / Moderada': 'priority-medium',
        'No urgente': 'priority-low',
        'Control de rutina': 'priority-routine'
    };
    return clases[prioridad] || 'priority-low';
}

function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'status-pending',
        'confirmada': 'status-confirmed',
        'en_espera': 'status-waiting',
        'atendida': 'status-completed',
        'cancelada': 'status-cancelled'
    };
    return clases[estado] || 'status-pending';
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    // Crear elemento de notificación
    const notif = document.createElement('div');
    notif.className = `notification notification-${tipo}`;
    notif.textContent = mensaje;
    notif.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// =====================================================
// INICIALIZACIÓN
// =====================================================

document.addEventListener('DOMContentLoaded', async () => {
    const currentPage = window.location.pathname.split('/').pop();

    // Páginas que requieren autenticación
    if (currentPage === 'admin.html' || currentPage === 'dashboard.html') {
        const sesionValida = await verificarSesion();
        if (!sesionValida) {
            window.location.href = 'login.html';
            return;
        }
    }

    // Inicializar según la página
    if (currentPage === 'admin.html') {
        await inicializarPanelAdmin();
    } else if (currentPage === 'agendar.html') {
        inicializarFormularioAgendamiento();
    }

    // Event listener para cerrar sesión
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', cerrarSesion);
    }
});

// =====================================================
// FORMULARIO DE AGENDAMIENTO
// =====================================================

function inicializarFormularioAgendamiento() {
    const form = document.getElementById('appointmentForm');
    if (!form) return;

    // Configurar fecha mínima (hoy)
    const dateInput = form.querySelector('input[type="date"]');
    if (dateInput) {
        dateInput.min = new Date().toISOString().split('T')[0];
    }

    // Event listener para cambios en síntomas
    const checkboxes = form.querySelectorAll('input[name="symptoms"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', actualizarTriageUI);
    });

    // Submit del formulario
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const cita = await crearCita(formData);
        
        if (cita) {
            form.reset();
            actualizarTriageUI();
            
            // Mostrar información de la cita creada
            mostrarResumenCita(cita);
        }
    });
}

function actualizarTriageUI() {
    const sintomasIds = obtenerSintomasSeleccionados();
    const score = calcularScoreTriage(sintomasIds);
    const prioridad = obtenerPrioridadDesdScore(score);

    // Actualizar UI
    const scoreElement = document.getElementById('triageScore');
    const prioridadElement = document.getElementById('triagePriority');

    if (scoreElement) {
        scoreElement.textContent = score;
    }

    if (prioridadElement) {
        prioridadElement.textContent = prioridad.label;
        prioridadElement.className = `badge-priority ${prioridad.className}`;
    }
}

function mostrarResumenCita(cita) {
    const mensaje = `
        ✅ Cita agendada exitosamente
        
        Paciente: ${cita.nombre_paciente}
        Fecha: ${formatearFecha(cita.fecha)}
        Hora: ${cita.hora}
        Prioridad: ${cita.prioridad}
    `;
    
    alert(mensaje);
}

// Hacer funciones globales para uso en HTML
window.cargarCitas = cargarCitas;
window.crearCita = crearCita;
window.actualizarCita = actualizarCita;
window.eliminarCita = eliminarCita;
window.cambiarEstadoCita = cambiarEstadoCita;
window.cerrarSesion = cerrarSesion;
window.inicializarPanelAdmin = inicializarPanelAdmin;