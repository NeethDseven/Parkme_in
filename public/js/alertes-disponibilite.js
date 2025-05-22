/**
 * Gestion des alertes de disponibilité
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM pour le modal d'alerte
    const alerteModal = document.getElementById('alerteModal');
    const alerteDateDebut = document.getElementById('alerteDateDebut');
    const alerteDateFin = document.getElementById('alerteDateFin');
    const confirmAlerteBtn = document.getElementById('confirmAlerte');
    
    // Variables pour stocker les données du créneau sélectionné
    let placeId, dateDebut, dateFin;
    
    // Si le modal n'existe pas, sortir
    if (!alerteModal) return;
    
    // Créer une instance de Modal Bootstrap
    const modal = new bootstrap.Modal(alerteModal);
    
    // Gérer le clic sur les boutons "M'alerter si disponible"
    document.querySelectorAll('.btn-alert-disponibilite').forEach(button => {
        button.addEventListener('click', function() {
            // Récupérer les données du créneau
            placeId = button.dataset.placeId;
            dateDebut = button.dataset.debut;
            dateFin = button.dataset.fin;
            
            // Formater les dates pour l'affichage
            const debutFormatted = formatDate(dateDebut);
            const finFormatted = formatDate(dateFin);
            
            // Mettre à jour le contenu du modal
            alerteDateDebut.textContent = debutFormatted;
            alerteDateFin.textContent = finFormatted;
            
            // Afficher le modal
            modal.show();
        });
    });
    
    // Gérer le clic sur le bouton "Activer l'alerte" du modal
    confirmAlerteBtn.addEventListener('click', function() {
        // Désactiver le bouton et afficher un indicateur de chargement
        confirmAlerteBtn.disabled = true;
        confirmAlerteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Activation...';
        
        // Envoyer la requête AJAX pour créer l'alerte
        createAlert(placeId, dateDebut, dateFin)
            .then(response => {
                // Fermer le modal
                modal.hide();
                
                // Afficher un message de succès
                showAlert('Alerte activée avec succès! Vous serez notifié si ce créneau se libère.', 'success');
                
                // Recharger la page pour mettre à jour l'affichage des alertes
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            })
            .catch(error => {
                // Afficher un message d'erreur
                console.error('Erreur:', error);
                showAlert('Erreur lors de l\'activation de l\'alerte: ' + error, 'danger');
                
                // Réactiver le bouton
                confirmAlerteBtn.disabled = false;
                confirmAlerteBtn.innerHTML = '<i class="fas fa-bell me-1"></i> Activer l\'alerte';
            });
    });
    
    // Gérer le clic sur les boutons "Supprimer" pour les alertes
    document.querySelectorAll('.btn-delete-alerte').forEach(button => {
        button.addEventListener('click', function() {
            const alerteId = button.dataset.alerteId;
            
            if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte?')) {
                // Désactiver le bouton
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Suppression...';
                
                // Envoyer la requête AJAX pour supprimer l'alerte
                deleteAlert(alerteId)
                    .then(response => {
                        // Supprimer la ligne du tableau
                        const row = button.closest('tr');
                        row.classList.add('fade-out');
                        
                        setTimeout(() => {
                            row.remove();
                            showAlert('Alerte supprimée avec succès!', 'success');
                            
                            // Si c'était la dernière alerte, recharger la page
                            const remainingRows = document.querySelectorAll('.btn-delete-alerte').length;
                            if (remainingRows === 0) {
                                window.location.reload();
                            }
                        }, 500);
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        showAlert('Erreur lors de la suppression de l\'alerte: ' + error, 'danger');
                        
                        // Réactiver le bouton
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-trash me-1"></i> Supprimer';
                    });
            }
        });
    });
    
    // Fonction pour créer une alerte via AJAX
    function createAlert(placeId, dateDebut, dateFin) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?page=parking&action=createAlert', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(response.message || 'Erreur lors de la création de l\'alerte');
                        }
                    } catch (e) {
                        reject('Erreur lors de l\'analyse de la réponse');
                    }
                } else {
                    reject('Erreur réseau: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                reject('Erreur de connexion');
            };
            
            const data = `place_id=${placeId}&date_debut=${dateDebut}&date_fin=${dateFin}`;
            xhr.send(data);
        });
    }
    
    // Fonction pour supprimer une alerte via AJAX
    function deleteAlert(alerteId) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '?page=parking&action=deleteAlert', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(response.message || 'Erreur lors de la suppression de l\'alerte');
                        }
                    } catch (e) {
                        reject('Erreur lors de l\'analyse de la réponse');
                    }
                } else {
                    reject('Erreur réseau: ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                reject('Erreur de connexion');
            };
            
            const data = `alerte_id=${alerteId}`;
            xhr.send(data);
        });
    }
    
    // Fonction pour formater une date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Fonction pour afficher une alerte
    function showAlert(message, type) {
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3`;
        alertElement.style.maxWidth = '500px';
        alertElement.style.zIndex = '9999';
        
        alertElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alertElement);
        
        // Supprimer l'alerte après 5 secondes
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertElement);
            bsAlert.close();
        }, 5000);
    }
});

// Ajouter une classe CSS pour l'animation de suppression
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .fade-out {
            opacity: 0;
            transition: opacity 0.5s;
        }
    </style>
`);
