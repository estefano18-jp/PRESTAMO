/* ===============================================
   SCRIPTS GENERALES DEL SISTEMA DE PRÉSTAMOS
   =============================================== */

// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
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
            input.addEventListener('input', function(e) {
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
            input.addEventListener('input', function(e) {
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
            input.addEventListener('blur', function() {
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
        // Agregar campo de búsqueda a las tablas
        const tables = document.querySelectorAll('.table');
        
        tables.forEach(table => {
            // Crear input de búsqueda
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control mb-3';
            searchInput.placeholder = '🔍 Buscar en la tabla...';
            
            // Insertar antes de la tabla
            if (table.parentElement.classList.contains('table-responsive')) {
                table.parentElement.insertBefore(searchInput, table);
            } else {
                table.insertBefore(searchInput, table);
            }
            
            // Función de filtrado
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        row.style.animation = 'fadeIn 0.5s';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Mostrar mensaje si no hay resultados
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
        // Agregar botón de imprimir en páginas con tablas
        const cardHeaders = document.querySelectorAll('.card-header');
        
        cardHeaders.forEach(header => {
            if (header.closest('.card').querySelector('table')) {
                const printBtn = document.createElement('button');
                printBtn.className = 'btn btn-sm btn-light float-end ms-2';
                printBtn.innerHTML = '<i class="fas fa-print"></i>';
                printBtn.title = 'Imprimir tabla';
                
                printBtn.addEventListener('click', function() {
                    window.print();
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
                window.location.href = 'index.php';
            }, TIMEOUT);
        }
        
        // Eventos que resetean el timer
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });
        
        resetTimer();
    }
    
    // ===============================================
    // FORMATO DE NÚMEROS
    // ===============================================
    function initNumberFormatting() {
        // Formatear números mientras se escribe
        const moneyInputs = document.querySelectorAll('input[name="monto"], input[name="penalidad"]');
        
        moneyInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value) {
                    const number = parseFloat(this.value);
                    this.value = number.toFixed(2);
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
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Función para formatear moneda
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN'
    }).format(amount);
}

// Función para validar email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Función para exportar tabla a Excel
function exportTableToExcel(tableId, filename = 'export') {
    const table = document.getElementById(tableId);
    const wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    XLSX.writeFile(wb, filename + '.xlsx');
}

// ===============================================
// FUNCIONES ESPECÍFICAS DEL SISTEMA
// ===============================================

// Calcular edad de préstamo
function calcularEdadPrestamo(fechaInicio) {
    const inicio = new Date(fechaInicio);
    const hoy = new Date();
    const meses = (hoy.getFullYear() - inicio.getFullYear()) * 12 + (hoy.getMonth() - inicio.getMonth());
    
    if (meses < 1) return 'Nuevo';
    if (meses < 6) return meses + ' meses';
    if (meses < 12) return 'Medio año';
    return Math.floor(meses / 12) + ' años';
}

// Validar CURP (si lo necesitas en el futuro)
function validarCURP(curp) {
    const re = /^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/;
    return re.test(curp);
}

// ===============================================
// GRÁFICOS (Si usas Chart.js)
// ===============================================
function createChart(canvasId, type, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: type,
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...options
        }
    });
}

// ===============================================
// UTILIDADES DE FECHA
// ===============================================
function addMonths(date, months) {
    const result = new Date(date);
    result.setMonth(result.getMonth() + months);
    return result;
}

function formatDate(date) {
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

// ===============================================
// MODO OSCURO (Opcional)
// ===============================================
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

// Verificar preferencia guardada
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
}

// ===============================================
// PROTECCIÓN CONTRA COPIAR
// ===============================================
/*
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('selectstart', e => e.preventDefault());
*/

// ===============================================
// CONSOLE MESSAGE
// ===============================================
console.log('%c¡Sistema de Préstamos Activo!', 'color: #1e3c72; font-size: 20px; font-weight: bold;');
console.log('%cDesarrollado con ❤️ usando PHP y Bootstrap', 'color: #2a5298; font-size: 14px;');