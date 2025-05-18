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
