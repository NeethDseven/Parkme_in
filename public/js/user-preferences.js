/**
 * Gestion des préférences utilisateur
 */
class UserPreferences {
    constructor() {
        this.preferencesForm = document.getElementById('user-preferences-form');
        if (!this.preferencesForm) return;
        
        this.paymentMethodSelect = document.getElementById('payment-method');
        this.notificationToggle = document.getElementById('notifications-toggle');
        this.saveBtn = document.getElementById('save-preferences');
        
        this.attachEvents();
    }
    
    attachEvents() {
        // Écouter l'événement de soumission du formulaire
        this.preferencesForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.savePreferences();
        });
        
        // Affichage conditionnel des champs de carte
        if (this.paymentMethodSelect) {
            this.paymentMethodSelect.addEventListener('change', () => {
                this.togglePaymentFields();
            });
            
            // Initialiser l'affichage
            this.togglePaymentFields();
        }
    }
    
    togglePaymentFields() {
        const method = this.paymentMethodSelect.value;
        const cardFields = document.getElementById('card-fields');
        const paypalFields = document.getElementById('paypal-fields');
        
        if (cardFields) {
            cardFields.style.display = method === 'carte' ? 'block' : 'none';
        }
        
        if (paypalFields) {
            paypalFields.style.display = method === 'paypal' ? 'block' : 'none';
        }
    }
    
    savePreferences() {
        // Récupérer les données du formulaire
        const formData = new FormData(this.preferencesForm);
        
        // Créer une requête XHR
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '?page=user&action=save_preferences', true);
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        this.showMessage('Préférences enregistrées avec succès', 'success');
                    } else {
                        this.showMessage(response.message || 'Erreur lors de l\'enregistrement', 'danger');
                    }
                } catch (e) {
                    this.showMessage('Erreur lors du traitement de la réponse', 'danger');
                }
            } else {
                this.showMessage('Erreur de communication avec le serveur', 'danger');
            }
        };
        
        xhr.onerror = () => {
            this.showMessage('Erreur de connexion', 'danger');
        };
        
        xhr.send(formData);
    }
    
    showMessage(message, type) {
        // Créer ou récupérer l'élément de message
        let alertBox = document.getElementById('preferences-alert');
        
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'preferences-alert';
            alertBox.className = 'alert mt-3';
            this.preferencesForm.insertAdjacentElement('beforebegin', alertBox);
        }
        
        // Définir le message et le type
        alertBox.className = `alert alert-${type} mt-3`;
        alertBox.textContent = message;
        
        // Faire disparaître le message après 3 secondes
        setTimeout(() => {
            alertBox.style.opacity = '1';
            
            setTimeout(() => {
                alertBox.style.opacity = '0';
            }, 3000);
        }, 100);
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    new UserPreferences();
});
