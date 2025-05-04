/**
 * Gestion des notifications en temps réel
 */
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si l'utilisateur est connecté (existence du badge de notification)
    const notificationBadge = document.getElementById('notification-badge');
    if (!notificationBadge) return;
    
    // Fonction pour mettre à jour le compteur de notifications
    function updateNotificationCount() {
        fetch('index.php?controller=notification&action=getUnread')
            .then(response => {
                // Vérifier si la réponse est OK et contient du JSON
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                
                // Vérifier le Content-Type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Format de réponse invalide');
                }
                
                return response.json();
            })
            .then(data => {
                if (data.count > 0) {
                    notificationBadge.textContent = data.count;
                    notificationBadge.classList.remove('d-none');
                    
                    // Si les nouvelles notifications sont arrivées, afficher une notification toaster
                    if (window.lastNotificationCount !== undefined && data.count > window.lastNotificationCount) {
                        showToast('Nouvelle notification', 'Vous avez reçu une nouvelle notification!');
                    }
                    
                    // Mettre à jour la liste déroulante des notifications si elle existe
                    const dropdownMenu = document.getElementById('notification-dropdown-menu');
                    if (dropdownMenu) {
                        updateNotificationDropdown(dropdownMenu, data.notifications);
                    }
                } else {
                    notificationBadge.textContent = '0';
                    notificationBadge.classList.add('d-none');
                }
                
                // Stocker le nombre actuel de notifications
                window.lastNotificationCount = data.count;
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des notifications:', error);
                // En cas d'erreur, essayons de ne pas casser l'UI - on ne fait rien
                // Réduire l'intervalle de polling pour ne pas surcharger le serveur en cas d'erreur
                setTimeout(updateNotificationCount, 60000); // Réessayer dans 1 minute
                return; // Ne pas continuer l'intervalle normal
            });
    }
    
    // Fonction pour mettre à jour la liste déroulante des notifications
    function updateNotificationDropdown(dropdownMenu, notifications) {
        // Effacer le contenu actuel
        dropdownMenu.innerHTML = '';
        
        if (notifications.length === 0) {
            const emptyItem = document.createElement('div');
            emptyItem.className = 'dropdown-item text-center text-muted';
            emptyItem.textContent = 'Aucune notification';
            dropdownMenu.appendChild(emptyItem);
        } else {
            // Ajouter chaque notification
            notifications.forEach(notification => {
                const item = document.createElement('a');
                item.className = 'dropdown-item';
                item.href = `index.php?controller=notification&action=markAsRead&id=${notification.id}`;
                
                // Déterminer l'icône en fonction du type
                let icon = '';
                switch (notification.type) {
                    case 'reservation':
                        icon = '<i class="bi bi-calendar-check text-success me-2"></i>';
                        break;
                    case 'rappel':
                        icon = '<i class="bi bi-clock-history text-warning me-2"></i>';
                        break;
                    case 'paiement':
                        icon = '<i class="bi bi-credit-card text-info me-2"></i>';
                        break;
                    case 'annulation':
                        icon = '<i class="bi bi-x-circle text-danger me-2"></i>';
                        break;
                    default:
                        icon = '<i class="bi bi-bell me-2"></i>';
                }
                
                // Extraire la première phrase du message
                const firstLine = notification.message.split('.')[0];
                
                item.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <span>${icon}${firstLine}</span>
                        <small class="text-muted">${formatDate(notification.date_creation)}</small>
                    </div>
                `;
                
                dropdownMenu.appendChild(item);
            });
            
            // Ajouter un lien vers toutes les notifications
            const divider = document.createElement('div');
            divider.className = 'dropdown-divider';
            dropdownMenu.appendChild(divider);
            
            const viewAllLink = document.createElement('a');
            viewAllLink.className = 'dropdown-item text-center';
            viewAllLink.href = 'index.php?controller=notification';
            viewAllLink.innerHTML = 'Voir toutes les notifications';
            dropdownMenu.appendChild(viewAllLink);
        }
    }
    
    // Fonction pour afficher une notification toast
    function showToast(title, message) {
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            // Créer le conteneur s'il n'existe pas
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }
        
        // Créer le toast
        const toastId = 'toast-' + Date.now();
        const toastElement = document.createElement('div');
        toastElement.id = toastId;
        toastElement.className = 'toast';
        toastElement.setAttribute('role', 'alert');
        toastElement.setAttribute('aria-live', 'assertive');
        toastElement.setAttribute('aria-atomic', 'true');
        
        toastElement.innerHTML = `
            <div class="toast-header">
                <i class="bi bi-bell-fill me-2 text-primary"></i>
                <strong class="me-auto">${title}</strong>
                <small>${formatTime(new Date())}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        document.getElementById('toast-container').appendChild(toastElement);
        
        // Initialiser et afficher le toast
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Supprimer le toast après qu'il soit masqué
        toastElement.addEventListener('hidden.bs.toast', function () {
            toastElement.remove();
        });
    }
    
    // Fonction d'aide pour formater la date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Fonction d'aide pour formater l'heure
    function formatTime(date) {
        return date.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Mise à jour initiale
    updateNotificationCount();
    
    // Mettre à jour les notifications toutes les 30 secondes
    setInterval(updateNotificationCount, 30000);
});
