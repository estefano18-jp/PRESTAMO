/* ===============================================
   SCRIPTS GENERALES DEL SISTEMA DE PRÉSTAMOS
   =============================================== */

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function () {

    // ===============================================
    // INICIALIZACIÓN
    // ===============================================
    initAnimations();
    initTooltips();
    initFormValidations();
    initTableFilters();
    initPrintButton();
    initAutoLogout();
    initNumberFormatting();

    // ===============================================
    // ANIMACIONES DE ENTRADA
    // ===============================================
    function initAnimations() {
        // Animar cards al cargar
        const cards = document.querySelectorAll('.card-custom, .stats-card, .dashboard-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';

            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Animar filas de tabla
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';

            setTimeout(() => {
                row.style.transition = 'opacity 0.5s ease';
                row.style.opacity = '1';
            }, index * 50);
        });
    }

    // ===============================================
    // TOOLTIPS DE BOOTSTRAP
    // ===============================================
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // ===============================================
    // VALIDACIONES DE FORMULARIO MEJORADAS
    // ===============================================
    function initFormValidations() {
        // Validación DNI (8 dígitos)
        const dniInputs = document.querySelectorAll('input[name="dni"]');
        dniInputs.forEach(input => {
            input.addEventListener('input', function (e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);

                if (this.value.length === 8) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });

        // Validación Teléfono (9 dígitos)
        const phoneInputs = document.querySelectorAll('input[name="telefono"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function (e) {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);

                if (this.value.length === 9) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });

        // Validación de montos
        const montoInputs = document.querySelectorAll('input[type="number"]');
        montoInputs.forEach(input => {
            input.addEventListener('blur', function () {
                if (this.value && parseFloat(this.value) > 0) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else if (this.value) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });
    }

    // ===============================================
    // FILTRO DE TABLAS EN TIEMPO REAL
    // ===============================================
    function initTableFilters() {
        // Selecciona todas las tablas con la clase .table
        const tables = document.querySelectorAll('.table');

        tables.forEach(table => {
            // Crea un input de búsqueda para cada tabla
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control mb-3';
            searchInput.placeholder = '🔍 Buscar en la tabla...';

            // Inserta el input antes de la tabla, dentro del contenedor .table-responsive si existe
            if (table.parentElement.classList.contains('table-responsive')) {
                table.parentElement.insertBefore(searchInput, table);
            } else {
                table.insertBefore(searchInput, table);
            }

            // Evento para filtrar filas al escribir en el input
            searchInput.addEventListener('keyup', function () {
                const searchTerm = this.value.toLowerCase(); // Texto a buscar en minúsculas
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase(); // Texto de la fila
                    if (text.includes(searchTerm)) {
                        row.style.display = ''; // Muestra la fila si coincide
                        row.style.animation = 'fadeIn 0.5s'; // Aplica animación
                    } else {
                        row.style.display = 'none'; // Oculta la fila si no coincide
                    }
                });

                // Mostrar mensaje si no hay resultados visibles
                const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
                let noResultsMsg = table.querySelector('.no-results-msg');

                if (visibleRows.length === 0) {
                    if (!noResultsMsg) {
                        noResultsMsg = document.createElement('tr');
                        noResultsMsg.className = 'no-results-msg';
                        noResultsMsg.innerHTML = '<td colspan="100%" class="text-center py-4 text-muted">No se encontraron resultados</td>';
                        table.querySelector('tbody').appendChild(noResultsMsg);
                    }
                } else if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            });
        });
    }

    // ===============================================
    // BOTÓN DE IMPRIMIR
    // ===============================================
    function initPrintButton() {
        // Selecciona todos los encabezados de tarjetas
        const cardHeaders = document.querySelectorAll('.card-header');

        cardHeaders.forEach(header => {
            // Si la tarjeta contiene una tabla, agrega el botón de imprimir
            if (header.closest('.card').querySelector('table')) {
                const printBtn = document.createElement('button');
                printBtn.className = 'btn btn-sm btn-light float-end ms-2';
                printBtn.innerHTML = '<i class="fas fa-print"></i>';
                printBtn.title = 'Imprimir tabla';

                printBtn.addEventListener('click', function () {
                    window.print(); // Imprime la página
                });

                header.appendChild(printBtn);
            }
        });
    }

    // ===============================================
    // AUTO LOGOUT POR INACTIVIDAD
    // ===============================================
    function initAutoLogout() {
        let inactivityTimer;
        const TIMEOUT = 30 * 60 * 1000; // 30 minutos

        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                alert('Sesión expirada por inactividad');
                window.location.href = 'index.php'; // Redirige al login
            }, TIMEOUT);
        }

        // Resetea el timer en cada interacción del usuario
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });

        resetTimer(); // Inicia el timer al cargar
    }

    // ===============================================
    // FORMATO DE NÚMEROS
    // ===============================================
    function initNumberFormatting() {
        // Selecciona los inputs de monto y penalidad
        const moneyInputs = document.querySelectorAll('input[name="monto"], input[name="penalidad"]');

        moneyInputs.forEach(input => {
            input.addEventListener('blur', function () {
                if (this.value) {
                    const number = parseFloat(this.value);
                    this.value = number.toFixed(2); // Formatea a dos decimales
                }
            });
        });
    }
});

// ===============================================
// FUNCIONES GLOBALES ÚTILES
// ===============================================

// Función para mostrar notificaciones
function showNotification(message, type = 'success') {
    // Crea un div para la notificación con clases de Bootstrap y posición fija
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999'; // Asegura que esté por encima de otros elementos
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification); // Agrega la notificación al body

    setTimeout(() => {
        notification.remove(); // Elimina la notificación después de 5 segundos
    }, 5000);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
    // Muestra un cuadro de confirmación y ejecuta el callback si el usuario acepta
    if (confirm(message)) {
        callback();
    }
}

// Función para formatear moneda
function formatCurrency(amount) {
    // Formatea un número como moneda en soles peruanos (PEN)
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
}

// Función para validar email
function validateEmail(email) {
    // Usa una expresión regular para validar el formato del email
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email); // Retorna true si es válido, false si no
}

// Función para exportar tabla a Excel
function exportTableToExcel(tableId, filename = 'export') {
    // Obtiene la tabla por su ID
    const table = document.getElementById(tableId);
    // Convierte la tabla a un libro de Excel usando SheetJS (XLSX)
    const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
    // Descarga el archivo Excel con el nombre indicado
    XLSX.writeFile(wb, filename + '.xlsx');
}

// ===============================================
// FUNCIONES ESPECÍFICAS DEL SISTEMA
// ===============================================

// Calcular edad de préstamo
function calcularEdadPrestamo(fechaInicio) {
    const inicio = new Date(fechaInicio); // Convierte la fecha de inicio a objeto Date
    const hoy = new Date(); // Obtiene la fecha actual
    // Calcula la diferencia en meses entre la fecha actual y la fecha de inicio
    const meses = (hoy.getFullYear() - inicio.getFullYear()) * 12 + (hoy.getMonth() - inicio.getMonth());

    if (meses < 1) return 'Nuevo'; // Si tiene menos de 1 mes, retorna "Nuevo"
    if (meses < 6) return meses + ' meses'; // Si tiene menos de 6 meses, retorna la cantidad de meses
    if (meses < 12) return 'Medio año'; // Si tiene menos de 12 meses, retorna "Medio año"
    return Math.floor(meses / 12) + ' años'; // Si tiene 1 año o más, retorna la cantidad de años
}

// Validar CURP (si lo necesitas en el futuro)
function validarCURP(curp) {
    // Expresión regular para validar el formato de una CURP mexicana
    const re = /^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/;
    return re.test(curp); // Retorna true si la CURP es válida, false si no
}

// ===============================================
// GRÁFICOS (Si usas Chart.js)
// ===============================================
function createChart(canvasId, type, data, options = {}) {
    // Obtiene el contexto del canvas donde se dibujará el gráfico
    const ctx = document.getElementById(canvasId).getContext('2d');
    // Crea y retorna un nuevo gráfico usando Chart.js
    return new Chart(ctx, {
        type: type, // Tipo de gráfico (bar, line, pie, etc.)
        data: data, // Datos del gráfico
        options: {
            responsive: true, // Hace el gráfico responsivo
            maintainAspectRatio: false, // Permite ajustar el tamaño libremente
            ...options // Permite agregar opciones adicionales
        }
    });
}

// ===============================================
// UTILIDADES DE FECHA
// ===============================================

// Suma una cantidad de meses a una fecha dada y retorna la nueva fecha
function addMonths(date, months) {
    const result = new Date(date); // Crea un nuevo objeto Date a partir del parámetro
    result.setMonth(result.getMonth() + months); // Suma los meses indicados
    return result; // Retorna la nueva fecha
}

// Formatea una fecha en formato dd/mm/yyyy
function formatDate(date) {
    const d = new Date(date); // Convierte el parámetro a objeto Date
    const day = String(d.getDate()).padStart(2, '0'); // Obtiene el día y lo rellena con 0 si es necesario
    const month = String(d.getMonth() + 1).padStart(2, '0'); // Obtiene el mes (0-indexado) y lo rellena
    const year = d.getFullYear(); // Obtiene el año
    return `${day}/${month}/${year}`; // Retorna la fecha formateada
}

// ===============================================
// MODO OSCURO (Opcional)
// ===============================================

// Activa o desactiva el modo oscuro y guarda la preferencia en localStorage
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode'); // Alterna la clase dark-mode en el body
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode')); // Guarda la preferencia
}

// Al cargar la página, verifica si el usuario tenía activado el modo oscuro y lo aplica
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

// ===============================================
// PROTECCIÓN CONTRA COPIAR
// ===============================================

// Si quieres evitar que copien contenido, descomenta estas líneas:
/*
document.addEventListener('contextmenu', e => e.preventDefault()); // Bloquea el menú contextual (clic derecho)
document.addEventListener('selectstart', e => e.preventDefault()); // Bloquea la selección de texto
*/

// ===============================================
// CONSOLE MESSAGE
// ===============================================

// Mensaje personalizado en la consola para identificar el sistema
console.log('%c¡Sistema de Préstamos Activo!', 'color: #1e3c72; font-size: 20px; font-weight: bold;');
console.log('%cDesarrollado con ❤️ usando PHP y Bootstrap', 'color: #2a5298; font-size: 14px;');