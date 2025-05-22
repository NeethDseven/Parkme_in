/**
 * Script pour gérer le formulaire de réservation
 */
document.addEventListener('DOMContentLoaded', function() {
    // Récupérer les éléments du formulaire
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    const prixContainer = document.getElementById('prix_container');
    const prixEstime = document.getElementById('prix_estime');
    
    // Récupérer les tarifs depuis les attributs data
    const tarifHoraire = parseFloat(dateDebutInput.dataset.tarifHoraire || 0);
    const tarifJournalier = parseFloat(dateDebutInput.dataset.tarifJournalier || 0);
    
    // Définir la date minimale aux champs date (aujourd'hui)
    const now = new Date();
    const tzOffset = now.getTimezoneOffset() * 60000; // Offset en millisecondes
    const localISOTime = (new Date(Date.now() - tzOffset)).toISOString().slice(0, 16);
    
    dateDebutInput.min = localISOTime;
    dateFinInput.min = localISOTime;
    
    // Fonction pour calculer le prix
    function calculerPrix() {
        // Vérifier que les deux dates sont renseignées
        if (!dateDebutInput.value || !dateFinInput.value) {
            return;
        }
        
        const debut = new Date(dateDebutInput.value);
        const fin = new Date(dateFinInput.value);
        
        // Vérifier que la date de fin est bien postérieure à la date de début
        if (fin <= debut) {
            prixEstime.textContent = "Date de fin invalide";
            prixContainer.classList.remove('alert-info');
            prixContainer.classList.add('alert-danger');
            return;
        }
        
        // Calculer la durée en heures
        const dureeHeures = (fin - debut) / 1000 / 60 / 60;
        
        let prix;
        if (dureeHeures <= 24) {
            // Tarif horaire pour moins de 24h
            prix = dureeHeures * tarifHoraire;
            prixEstime.textContent = `${prix.toFixed(2)} € (${Math.ceil(dureeHeures)} heure${dureeHeures > 1 ? 's' : ''})`;
        } else {
            // Tarif journalier au-delà de 24h
            const jours = Math.ceil(dureeHeures / 24);
            prix = jours * tarifJournalier;
            prixEstime.textContent = `${prix.toFixed(2)} € (${jours} jour${jours > 1 ? 's' : ''})`;
        }
        
        prixContainer.classList.remove('alert-danger');
        prixContainer.classList.add('alert-info');
    }
    
    // Ajouter les écouteurs d'événements
    dateDebutInput.addEventListener('change', function() {
        // Mettre à jour la date minimale de fin pour qu'elle soit au moins égale à la date de début
        dateFinInput.min = dateDebutInput.value;
        calculerPrix();
    });
    
    dateFinInput.addEventListener('change', calculerPrix);
    
    // Validation du formulaire avant soumission
    document.querySelector('.reservation-form').addEventListener('submit', function(event) {
        const debut = new Date(dateDebutInput.value);
        const fin = new Date(dateFinInput.value);
        
        if (fin <= debut) {
            event.preventDefault();
            alert("La date de fin doit être postérieure à la date de début");
        }
    });
});
