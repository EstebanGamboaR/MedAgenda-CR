// =====================================================
// SCRIPT PRINCIPAL - MEDAGENDA-CR
// =====================================================

// URL relativa al backend en XAMPP
const API_URL = 'backend/api';

// --- FUNCIONES DE TRIAGE ---
function calcularScoreTriage(sintomas) {
    let score = 0;
    // Lógica básica de ejemplo
    if (sintomas.includes('dolor_pecho')) score += 5;
    if (sintomas.includes('dificultad_respirar')) score += 4;
    if (sintomas.includes('fiebre_alta')) score += 3;
    return score;
}

function obtenerPrioridad(score) {
    if (score >= 5) return 'emergencia';
    if (score >= 3) return 'urgente';
    return 'no-urgente';
}

// --- CONSUMO DE API ---

async function crearCita(datos) {
    try {
        const respuesta = await fetch(`${API_URL}/crear_cita.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        return await respuesta.json();
    } catch (error) {
        console.error('Error:', error);
        return { ok: false, error: 'Error de red' };
    }
}

async function login(email, password) {
    const respuesta = await fetch(`${API_URL}/login.php`, {
        method: 'POST',
        body: JSON.stringify({ email, password })
    });
    return await respuesta.json();
}

// Funciones globales para HTML
window.medAgenda = {
    crearCita,
    login,
    calcularScoreTriage,
    obtenerPrioridad
};