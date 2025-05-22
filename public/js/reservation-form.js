/**
 * Gestion des formulaires de réservation
 */
class ReservationForm {
    constructor() {
        // Éléments du formulaire
        this.form = document.querySelector('.reservation-form');
        if (!this.form) return;
        
        this.dateDebutInput = document.getElementById('date_debut');
        this.dateFinInput = document.getElementById('date_fin');
        this.typePlaceElement = document.getElementById('place_type');
        this.numeroPLaceElement = document.getElementById('place_numero');
        this.prixEstimeElement = document.getElementById('prix_estime');
        
        // Récupérer les tarifs
        this.tarifHoraire = parseFloat(this.form.dataset.tarifHoraire || 0);
        this.tarifJournee = parseFloat(this.form.dataset.tarifJournee || 0);
        
        // Créneaux indisponibles (depuis la variable JS)
        this.creneauxIndisponibles = typeof creneauxIndisponibles !== 'undefined' ? creneauxIndisponibles : [];
        
        // Attacher les événements
        this.attachEvents();
    }
    
    attachEvents() {
        // S'assurer que les éléments existent avant d'attacher les événements
        if (this.dateDebutInput && this.dateFinInput) {
            // Définir les dates minimales
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            const time = now.toTimeString().split(' ')[0].slice(0, 5);
            
            this.dateDebutInput.min = today + 'T' + time;
            
            // Gérer les changements de date
            this.dateDebutInput.addEventListener('change', () => {
                this.dateFinInput.min = this.dateDebutInput.value;
                this.checkDisponibilite();
                this.calculatePrice();
            });
            
            this.dateFinInput.addEventListener('change', () => {
                this.checkDisponibilite();
                this.calculatePrice();
            });
            
            // Validation au moment de la soumission
            this.form.addEventListener('submit', (e) => this.validateForm(e));
        }
    }
    
    checkDisponibilite() {
        if (!this.dateDebutInput.value || !this.dateFinInput.value) return;
        
        const debut = new Date(this.dateDebutInput.value);
        const fin = new Date(this.dateFinInput.value);
        
        // Vérifier chaque créneau indisponible pour voir s'il y a chevauchement
        let conflit = false;
        
        this.creneauxIndisponibles.forEach(creneau => {
            // Ne vérifier que les réservations confirmées
            if (creneau.status === 'confirmée') {
                const creneauDebut = new Date(creneau.date_debut);
                const creneauFin = new Date(creneau.date_fin);
                
                // Vérifier s'il y a chevauchement
                if ((debut >= creneauDebut && debut < creneauFin) || 
                    (fin > creneauDebut && fin <= creneauFin) ||
                    (debut <= creneauDebut && fin >= creneauFin)) {
                    conflit = true;
                }
            }
        });
        
        // Afficher un avertissement si conflit
        const alertEl = document.getElementById('disponibilite-alert');
        if (conflit) {
            if (!alertEl) {
                const alert = document.createElement('div');
                alert.id = 'disponibilite-alert';
                alert.className = 'alert alert-danger mt-3';
                alert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Ce créneau chevauche une réservation existante. Veuillez choisir d\'autres dates.';
                this.dateFinInput.parentNode.insertAdjacentElement('afterend', alert);
            }
            // Désactiver le bouton de soumission
            this.form.querySelector('button[type="submit"]').disabled = true;
        } else {
            if (alertEl) alertEl.remove();
            // Réactiver le bouton de soumission
            this.form.querySelector('button[type="submit"]').disabled = false;
        }
    }
    
    calculatePrice() {
        if (!this.dateDebutInput.value || !this.dateFinInput.value) return;
        
        const debut = new Date(this.dateDebutInput.value);
        const fin = new Date(this.dateFinInput.value);
        
        // Vérifier la validité des dates
        if (fin <= debut) {
            this.prixEstimeElement.textContent = "Date de fin invalide";
            this.prixEstimeElement.parentElement.classList.add('text-danger');
            return;
        }
        
        // Calculer la durée en heures
        const durationMs = fin.getTime() - debut.getTime();
        const durationHours = Math.ceil(durationMs / (1000 * 60 * 60));
        
        // Calculer le prix
        let prix;
        if (durationHours <= 24) {
            prix = durationHours * this.tarifHoraire;
            this.prixEstimeElement.textContent = `${prix.toFixed(2)}€ (${durationHours}h)`;
        } else {
            const days = Math.ceil(durationHours / 24);
            prix = days * this.tarifJournee;
            this.prixEstimeElement.textContent = `${prix.toFixed(2)}€ (${days} jours)`;
        }
        
        this.prixEstimeElement.parentElement.classList.remove('text-danger');
        this.prixEstimeElement.parentElement.classList.add('text-success');
    }
    
    validateForm(e) {
        const debut = new Date(this.dateDebutInput.value);
        const fin = new Date(this.dateFinInput.value);
        
        if (fin <= debut) {
            e.preventDefault();
            alert("La date de fin doit être postérieure à la date de début");
            return false;
        }
        
        // Vérifier les conflits une dernière fois
        let conflit = false;
        this.creneauxIndisponibles.forEach(creneau => {
            if (creneau.status === 'confirmée') {
                const creneauDebut = new Date(creneau.date_debut);
                const creneauFin = new Date(creneau.date_fin);
                
                if ((debut >= creneauDebut && debut < creneauFin) || 
                    (fin > creneauDebut && fin <= creneauFin) ||
                    (debut <= creneauDebut && fin >= creneauFin)) {
                    conflit = true;
                }
            }
        });
        
        if (conflit) {
            e.preventDefault();
            alert("Ce créneau chevauche une réservation existante. Veuillez choisir d'autres dates.");
            return false;
        }
        
        return true;
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    new ReservationForm();
});
