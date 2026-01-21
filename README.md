# 🏥 MedAgenda-CR

> Sistema de Gestión de Citas Médicas con Triage Inteligente | PHP + MySQL + JS

Aplicación web Full Stack diseñada para optimizar el flujo de atención en centros de salud (EBAIS). Incluye agendamiento en línea, panel administrativo para recepcionistas, cálculo automático de prioridad (Triage) y auditoría de base de datos.

## 🚀 Características Técnicas
* **Arquitectura:** MVC simplificado (API REST en PHP nativo).
* **Base de Datos:** MySQL con uso avanzado de `Stored Procedures` y `Triggers` para auditoría.
* **Frontend:** HTML5, CSS3 moderno (Variables CSS) y JS Vanilla (ES6+).
* **Seguridad:** Manejo de sesiones PHP y Hash de contraseñas.

## 🛠️ Tecnologías
* **Backend:** PHP 8.x
* **Base de Datos:** MySQL / MariaDB
* **Servidor Local Recomendado:** XAMPP / WAMP

## 📦 Instalación (XAMPP)

1. **Base de Datos:**
   - Abre **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Crea una base de datos llamada `medagenda`.
   - Importa el archivo `medagenda.sql` incluido en este repositorio.

2. **Despliegue:**
   - Clona este repositorio dentro de la carpeta `htdocs` de XAMPP:
     ```bash
     C:\xampp\htdocs\MedAgenda-CR
     ```

3. **Ejecución:**
   - Enciende Apache y MySQL desde el panel de XAMPP.
   - Abre tu navegador en: `http://localhost/MedAgenda-CR`

## 👤 Credenciales de Acceso
| Rol | Email | Contraseña |
|-----|-------|------------|
| **Admin** | admin@medagenda.com | admin123 |
| **Recepción** | recepcion@medagenda.com | recepcion123 |

---
Desarrollado por **Esteban Gamboa**