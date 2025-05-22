/**
 * Gestion du calendrier de réservation
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const calendarEl = document.getElementById('reservation-calendar');
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    const dateDebutDisplay = document.getElementById('date_debut_display');
    const dateFinDisplay = document.getElementById('date_fin_display');
    const heureDebutSelect = document.getElementById('heure_debut');
    const heureFinSelect = document.getElementById('heure_fin');
    const prixEstime = document.getElementById('prix_estime');
    const submitBtn = document.getElementById('submit-reservation');
    const form = document.querySelector('.reservation-form');
    
    // Tarifs
    const tarifHoraire = parseFloat(form.dataset.tarifHoraire || 0);
    const tarifJournee = parseFloat(form.dataset.tarifJournee || 0);
    
    // Préparer les créneaux indisponibles
    let bookedTimeSlots = [];
    if (typeof creneauxIndisponibles !== 'undefined' && creneauxIndisponibles.length > 0) {
        creneauxIndisponibles.forEach(creneau => {
            if (creneau.status === 'confirmée') {
                bookedTimeSlots.push({
                    start: new Date(creneau.date_debut),
                    end: new Date(creneau.date_fin)
                });
            }
        });
    }
    
    // Variables pour stocker les dates sélectionnées
    let selectedStartDate = null;
    let selectedEndDate = null;
    
    // Initialiser le calendrier Flatpickr
    const calendar = flatpickr(calendarEl, {
        mode: "range",
        inline: true,
        locale: "fr",
        minDate: "today",
        dateFormat: "Y-m-d",
        disableMobile: true,
        showMonths: 2,
        
        // Personnaliser l'affichage des jours
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const date = dayElem.dateObj;
            const dateStr = formatDateString(date);
            
            // Vérifier si le jour a des réservations
            const hasBooking = bookedTimeSlots.some(slot => {
                const slotDate = formatDateString(slot.start);
                const slotEndDate = formatDateString(slot.end);
                return dateStr >= slotDate && dateStr <= slotEndDate;
            });
            
            if (hasBooking) {
                dayElem.classList.add('has-partial-booking');
            }
        },
        
        // Quand l'utilisateur sélectionne des dates
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                // Mettre à jour les dates sélectionnées
                selectedStartDate = new Date(selectedDates[0]);
                selectedEndDate = new Date(selectedDates[1]);
                
                // Mettre à jour l'affichage
                updateDateDisplay();
                
                // Vérifier les conflits
                checkReservationConflicts();
                
                // Calculer le prix
                calculatePrice();
            }
        }
    });
    
    // Écouteurs d'événements pour les sélecteurs d'heures
    heureDebutSelect.addEventListener('change', function() {
        updateDateTimeValues();
        checkReservationConflicts();
        calculatePrice();
    });
    
    heureFinSelect.addEventListener('change', function() {
        updateDateTimeValues();
        checkReservationConflicts();
        calculatePrice();
    });
    
    // Mise à jour des valeurs complètes (date + heure)
    function updateDateTimeValues() {
        if (!selectedStartDate || !selectedEndDate) return;
        
        // Récupérer les heures sélectionnées
        const heureDebut = heureDebutSelect.value;
        const heureFin = heureFinSelect.value;
        
        // Construire les dates complètes
        const startDateStr = formatDateString(selectedStartDate);
        const endDateStr = formatDateString(selectedEndDate);
        
        // Créer les objets Date complets
        const startDateTime = new Date(`${startDateStr}T${heureDebut}:00`);
        const endDateTime = new Date(`${endDateStr}T${heureFin}:00`);
        
        // Mettre à jour les champs cachés
        dateDebutInput.value = startDateTime.toISOString().slice(0, 16);
        dateFinInput.value = endDateTime.toISOString().slice(0, 16);
        
        // Mettre à jour l'affichage
        dateDebutDisplay.textContent = formatDateTimeForDisplay(startDateTime);
        dateFinDisplay.textContent = formatDateTimeForDisplay(endDateTime);
    }
    
    // Mise à jour de l'affichage des dates
    function updateDateDisplay() {
        if (selectedStartDate && selectedEndDate) {
            // Activer le bouton si les dates sont valides
            submitBtn.disabled = false;
            
            // Mettre à jour les valeurs
            updateDateTimeValues();
        } else {
            // Réinitialiser l'affichage
            dateDebutDisplay.textContent = "Non sélectionné";
            dateFinDisplay.textContent = "Non sélectionné";
            submitBtn.disabled = true;
        }
    }
    
    // Vérifier les conflits de réservation
    function checkReservationConflicts(startDate, endDate) {
        if (!dateDebutInput.value || !dateFinInput.value) return;
        
        // Convertir les valeurs en objets Date
        const startDate = new Date(dateDebutInput.value);
        const endDate = new Date(dateFinInput.value);
        
        // Vérifier les erreurs de base
        if (endDate <= startDate) {
            showError("La date de fin doit être postérieure à la date de début");
            submitBtn.disabled = true;
            return;
        }
        
        // Vérifier les chevauchements
        let hasConflict = false;
        let conflictDetails = [];
        
        bookedTimeSlots.forEach(slot => {
            // Vérifier le chevauchement
            if ((startDate >= slot.start && startDate < slot.end) ||
                (endDate > slot.start && endDate <= slot.end) ||
                (startDate <= slot.start && endDate >= slot.end)) {
                
                hasConflict = true;
                
                // Stocker les détails du conflit pour l'alerte
                conflictDetails.push({
                    start: slot.start,
                    end: slot.end
                });
            }
        });
        
        // Si conflit détecté, afficher l'erreur avec option d'alerte
        if (hasConflict) {
            // Formatage des dates pour affichage
            const formattedStart = startDate.toLocaleDateString('fr-FR', { 
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            const formattedEnd = endDate.toLocaleDateString('fr-FR', { 
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            
            // Supprimer l'alerte précédente si elle existe
            let errorContainer = document.getElementById('reservation-error');
            if (errorContainer) {
                errorContainer.remove();
            }
            
            // Créer une nouvelle alerte avec bouton pour créer une alerte automatique
            errorContainer = document.createElement('div');
            errorContainer.id = 'reservation-error';
            errorContainer.className = 'alert alert-warning mt-3';
            errorContainer.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span>Ce créneau chevauche une réservation existante</span>
                <button id="create-auto-alert" class="btn btn-sm btn-warning float-end">
                    <i class="fas fa-bell me-1"></i>Créer une alerte pour ce créneau
                </button>
            `;
            
            // Insérer l'alerte après le calendrier
            const form = document.querySelector('.reservation-form');
            form.insertBefore(errorContainer, form.querySelector('#prix_container'));
            
            // Ajouter l'événement au bouton d'alerte
            document.getElementById('create-auto-alert').addEventListener('click', function(e) {
                e.preventDefault();
                createAutomaticAlert(startDate, endDate);
            });
            
            // Désactiver le bouton de soumission
            submitBtn.disabled = true;
        } else {
            // Si pas de conflit, enlever le message d'erreur
            hideError();
            submitBtn.disabled = false;
        }
    }
    
    // Fonction pour créer automatiquement une alerte pour un créneau indisponible
    function createAutomaticAlert(startDate, endDate) {
        // Récupérer l'ID de la place depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const placeId = urlParams.get('id');
        
        // Formater les dates au format attendu par le serveur
        const formatDateForServer = (date) => {
            return date.toISOString().slice(0, 16);
        };
        
        // Créer un FormData pour l'envoi
        const formData = new FormData();
        formData.append('place_id', placeId);
        formData.append('date_debut', formatDateForServer(startDate));
        formData.append('date_fin', formatDateForServer(endDate));
        
        // Appel AJAX pour créer l'alerte
        const xhr = new XMLHttpRequest();
        xhr.open('POST', `${BASE_URL}/?page=parking&action=createAlert`, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    // Convertir l'alerte d'erreur en alerte de succès
                    const errorContainer = document.getElementById('reservation-error');
                    if (errorContainer && response.success) {
                        errorContainer.className = 'alert alert-success mt-3';
                        errorContainer.innerHTML = `
                            <i class="fas fa-check-circle me-2"></i>
                            <span>${response.message}</span>
                        `;
                        
                        // Auto-reload après 3 secondes pour rafraîchir la liste des alertes
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    } else if (errorContainer) {
                        errorContainer.className = 'alert alert-danger mt-3';
                        errorContainer.innerHTML = `
                            <i class="fas fa-times-circle me-2"></i>
                            <span>${response.message}</span>
                        `;
                    }
                } catch (e) {
                    console.error('Erreur lors du traitement de la réponse:', e);
                }
            }
        };
        
        xhr.onerror = function() {
            const errorContainer = document.getElementById('reservation-error');
            if (errorContainer) {
                errorContainer.className = 'alert alert-danger mt-3';
                errorContainer.innerHTML = `
                    <i class="fas fa-times-circle me-2"></i>
                    <span>Erreur de connexion au serveur. Veuillez réessayer.</span>
                `;
            }
        };
        
        xhr.send(formData);
    }
    
    // Calculer le prix de la réservation
    function calculatePrice() {
        if (!dateDebutInput.value || !dateFinInput.value) return;
        
        const startDate = new Date(dateDebutInput.value);
        const endDate = new Date(dateFinInput.value);
        
        // Si les dates sont invalides, ne rien faire
        if (endDate <= startDate) return;
        
        // Calculer la durée en heures
        const durationHours = (endDate - startDate) / (1000 * 60 * 60);
        let price;
        
        if (durationHours <= 24) {
            // Tarif horaire pour moins de 24h
            price = durationHours * tarifHoraire;
            prixEstime.textContent = `${price.toFixed(2)} € (${Math.ceil(durationHours)} heure${durationHours > 1 ? 's' : ''})`;
        } else {
            // Tarif journalier au-delà de 24h
            const days = Math.ceil(durationHours / 24);
            price = days * tarifJournee;
            prixEstime.textContent = `${price.toFixed(2)} € (${days} jour${days > 1 ? 's' : ''})`;
        }
    }
    
    // Afficher un message d'erreur
    function showError(message) {
        let errorContainer = document.getElementById('reservation-error');
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'reservation-error';
            errorContainer.className = 'alert alert-danger mt-3';
            const form = document.querySelector('.reservation-form');
            form.insertBefore(errorContainer, form.querySelector('#prix_container'));
        }
        
        errorContainer.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
    }
    
    // Masquer le message d'erreur
    function hideError() {
        const errorContainer = document.getElementById('reservation-error');
        if (errorContainer) {
            errorContainer.remove();
        }
    }
    
    // Formater une date pour l'affichage
    function formatDateTimeForDisplay(date) {
        return date.toLocaleDateString('fr-FR', { 
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Formater une date en chaine YYYY-MM-DD
    function formatDateString(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        if (!dateDebutInput.value || !dateFinInput.value) {
            e.preventDefault();
            showError("Veuillez sélectionner des dates de réservation");
            return false;
        }
        
        const startDate = new Date(dateDebutInput.value);
        const endDate = new Date(dateFinInput.value);
        
        if (endDate <= startDate) {
            e.preventDefault();
            showError("La date de fin doit être postérieure à la date de début");
            return false;
        }
        
        // Vérifier une dernière fois les conflits
        let hasConflict = false;
        bookedTimeSlots.forEach(slot => {
            if ((startDate >= slot.start && startDate < slot.end) ||
                (endDate > slot.start && endDate <= slot.end) ||
                (startDate <= slot.start && endDate >= slot.end)) {
                hasConflict = true;
            }
        });
        
        if (hasConflict) {
            e.preventDefault();
            showError("Ce créneau chevauche une réservation existante");
            return false;
        }
    });
    
    // Gérer les alertes de disponibilité
    const alertForm = document.getElementById('alert-form');
    const createAlertBtn = document.getElementById('create-alert-btn');
    
    if (createAlertBtn && alertForm) {
        createAlertBtn.addEventListener('click', function() {
            const formData = new FormData(alertForm);
            
            // Valider le formulaire
            const dateDebut = formData.get('date_debut');
            const dateFin = formData.get('date_fin');
            
            if (!dateDebut || !dateFin) {
                alert('Veuillez remplir tous les champs');
                return;
            }
            
            // Envoyer une requête AJAX pour créer l'alerte
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `${BASE_URL}/?page=parking&action=createAlert`, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Fermer le modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('alertModal'));
                            modal.hide();
                            
                            // Afficher un message de succès
                            alert(response.message);
                            
                            // Recharger la page pour afficher la nouvelle alerte
                            window.location.reload();
                        } else {
                            alert(response.message || 'Erreur lors de la création de l\'alerte');
                        }
                    } catch (e) {
                        alert('Erreur lors du traitement de la réponse');
                    }
                }
            };
            
            xhr.send(formData);
        });
    }
    
    // Supprimer les alertes
    const deleteAlertBtns = document.querySelectorAll('.delete-alert-btn');
    
    deleteAlertBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?')) {
                const alerteId = this.dataset.alerteId;
                
                const formData = new FormData();
                formData.append('alerte_id', alerteId);
                
                const xhr = new XMLHttpRequest();
                xhr.open('POST', `${BASE_URL}/?page=parking&action=deleteAlert`, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Supprimer l'élément de la liste
                                btn.closest('li').remove();
                                
                                // Si la liste est vide, masquer ou mettre à jour le conteneur
                                const alertsList = document.querySelector('.list-group');
                                if (alertsList && alertsList.children.length === 0) {
                                    const alertsContainer = document.querySelector('.card.border-info');
                                    if (alertsContainer) {
                                        alertsContainer.remove();
                                    }
                                }
                            } else {
                                alert(response.message || 'Erreur lors de la suppression de l\'alerte');
                            }
                        } catch (e) {
                            alert('Erreur lors du traitement de la réponse');
                        }
                    }
                };
                
                xhr.send(formData);
            }
        });
    });
});

// Ajouter des styles personnalisés pour le calendrier
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .has-partial-booking {
            background-color: rgba(255, 165, 0, 0.3) !important;
            color: #333 !important;
            position: relative;
        }
        .has-partial-booking::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 8px 8px 0;
            border-color: transparent #ff6b6b transparent transparent;
        }
        .flatpickr-day.selected.has-partial-booking,
        .flatpickr-day.startRange.has-partial-booking,
        .flatpickr-day.endRange.has-partial-booking {
            background-color: #4e98d0 !important;
            border-color: #4e98d0 !important;
            color: white !important;
        }
        .flatpickr-calendar {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto;
        }
        .flatpickr-calendar.inline {
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .flatpickr-month {
            background-color: #4e98d0;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .flatpickr-current-month {
            font-size: 1.2rem;
            padding: 10px 0;
        }
        .flatpickr-weekday {
            background-color: #f8f9fa;
            color: #666;
            font-weight: bold;
        }
        .flatpickr-day {
            border-radius: 0;
            border: none;
            margin: 0;
        }
        .flatpickr-day.selected, 
        .flatpickr-day.startRange, 
        .flatpickr-day.endRange {
            background-color: #4e98d0;
            border-color: #4e98d0;
        }
        .flatpickr-day.inRange {
            background-color: rgba(78, 152, 208, 0.2);
            border-color: transparent;
        }
    `;
    document.head.appendChild(style);
});
