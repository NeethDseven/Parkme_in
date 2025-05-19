/**
 * Fichier JavaScript principal pour l'application Parkme In
 */

// Initialisation des composants Bootstrap lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé - Initialisation des composants');
    
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialisation des popovers Bootstrap
    var popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0) {
        popoverTriggerList.forEach(function(popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // Initialisation des onglets Bootstrap
    var tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
    console.log('Nombre d\'onglets trouvés:', tabElements.length);
    
    if (tabElements.length > 0) {
        tabElements.forEach(function(tabElement) {
            try {
                new bootstrap.Tab(tabElement);
                console.log('Onglet initialisé:', tabElement.id || 'sans ID');
                
                // Ajouter un écouteur d'événements pour le débogage
                tabElement.addEventListener('shown.bs.tab', function(event) {
                    console.log('Onglet activé:', event.target.id || 'sans ID');
                });
            } catch (error) {
                console.error('Erreur lors de l\'initialisation de l\'onglet:', error);
            }
        });
    }
    
    // Fermeture automatique des alertes après 5 secondes
    var autoAlerts = document.querySelectorAll('.alert.auto-dismiss');
    autoAlerts.forEach(function(alert) {
        setTimeout(function() {
            var closeButton = alert.querySelector('.btn-close');
            if (closeButton) closeButton.click();
        }, 5000);
    });
});

/**
 * Fonctions utilitaires pour l'application
 */

// Formatage des dates
function formatDate(dateString) {
    if (!dateString) return '';
    const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Formatage des montants
function formatAmount(amount) {
    if (amount === null || amount === undefined) return '0,00 €';
    return parseFloat(amount).toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
}

// Validation des formulaires
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    
    form.classList.add('was-validated');
    return true;
}
