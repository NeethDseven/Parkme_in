/**
 * Fichier JavaScript principal pour l'application Parkme In
 */

// Fonction exécutée quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log("Application JS chargée");
    
    // Vérifier si Bootstrap est bien chargé
    if (typeof bootstrap !== 'undefined') {
        console.log("Bootstrap JS est bien chargé");
        
        // Initialiser les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Initialiser les popovers Bootstrap
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
        
        // Initialiser les alertes auto-fermantes
        var autoAlerts = document.querySelectorAll('.alert-dismissible.auto-dismiss');
        autoAlerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    } else {
        console.error("Bootstrap JS n'est pas chargé !");
    }
});

// Implémentation POO pour le frontend
class ParkingApp {
    constructor() {
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Recherche de tous les formulaires de réservation
        const reservationForms = document.querySelectorAll('.reservation-form');
        reservationForms.forEach(form => {
            form.addEventListener('submit', this.validateReservationForm.bind(this));
        });
        
        // Mise en place des onglets
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', this.handleTabClick.bind(this));
        });
    }
    
    validateReservationForm(event) {
        const form = event.target;
        const dateDebut = form.querySelector('[name="date_debut"]');
        const dateFin = form.querySelector('[name="date_fin"]');
        
        if (new Date(dateFin.value) <= new Date(dateDebut.value)) {
            event.preventDefault();
            alert("La date de fin doit être après la date de début");
        }
    }
    
    handleTabClick(event) {
        const tabName = event.target.getAttribute('data-tab');
        this.showTab(tabName);
    }
    
    showTab(tabName) {
        // Masquer tous les contenus des onglets
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Désactiver tous les boutons d'onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activer l'onglet sélectionné
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Activer le bouton correspondant
        document.querySelector(`.tab-btn[data-tab="${tabName}"]`).classList.add('active');
    }
}

// Initialisation de l'application quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const app = new ParkingApp();
});
