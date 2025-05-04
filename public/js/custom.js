/**
 * Scripts personnalisés pour ParkMeIn
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    if (tooltipTriggerList.length > 0) {
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    }
    
    // Initialiser les popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    if (popoverTriggerList.length > 0) {
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
    }
    
    // Validation des formulaires
    var forms = document.querySelectorAll('.needs-validation');
    
    if (forms.length > 0) {
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
        });
    }
    
    // Formattage des champs de date et heure
    var dateTimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    if (dateTimeInputs.length > 0) {
        dateTimeInputs.forEach(function(input) {
            // S'assurer que le format est correct
            input.addEventListener('change', function() {
                let dateObj = new Date(this.value);
                if (!isNaN(dateObj)) {
                    // Rien à faire, la date est valide
                } else {
                    // Date invalide, réinitialiser
                    this.value = '';
                }
            });
        });
    }
    
    // Formattage des champs monétaires
    var currencyInputs = document.querySelectorAll('.currency-input');
    if (currencyInputs.length > 0) {
        currencyInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                // Supprimer tous les caractères non numériques sauf le point
                let value = this.value.replace(/[^\d.]/g, '');
                
                // S'assurer qu'il n'y a qu'un seul point décimal
                let parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                
                // Limiter à deux décimales
                if (parts.length === 2 && parts[1].length > 2) {
                    value = parts[0] + '.' + parts[1].substring(0, 2);
                }
                
                this.value = value;
            });
            
            // Formater l'affichage quand le focus est perdu
            input.addEventListener('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        });
    }
    
    // Gestion des messages flash
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            // Fermer automatiquement les alertes après 5 secondes
            var bsAlert = new bootstrap.Alert(alert);
            setTimeout(function() {
                bsAlert.close();
            }, 5000);
        });
    }, 0);
});

/**
 * Fonction pour confirmer une action
 * @param {string} message Message de confirmation
 * @returns {boolean} True si confirmé, false sinon
 */
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}

/**
 * Fonction pour formater une date en format français
 * @param {Date|string} date Date à formater
 * @returns {string} Date formatée
 */
function formatDate(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }
    
    if (isNaN(date)) {
        return '';
    }
    
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Fonction pour formater un montant en euros
 * @param {number} amount Montant à formater
 * @returns {string} Montant formaté
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}
