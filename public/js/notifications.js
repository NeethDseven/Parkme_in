/**
 * Système de notifications simples en temps réel
 */
class NotificationManager {
    constructor() {
        this.notificationBadge = document.getElementById('notification-badge');
        this.notificationList = document.getElementById('notification-list');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        this.unreadCount = 0;
        
        // Si l'utilisateur n'est pas connecté, ne rien faire
        if (!document.body.classList.contains('user-logged')) return;
        
        this.attachEvents();
        this.startPolling();
    }
    
    attachEvents() {
        // Bouton pour marquer toutes les notifications comme lues
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', (e) => {
                e.preventDefault(); // Empêcher le comportement par défaut
                this.markAllAsRead();
            });
        }
        
        // Boutons pour marquer une notification comme lue
        const readButtons = document.querySelectorAll('.mark-read-btn');
        readButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Empêcher le comportement par défaut
                const notifId = button.dataset.id;
                this.markAsRead(notifId, button.closest('.notification-item'));
            });
        });
    }
    
    startPolling() {
        // Vérifier les notifications toutes les 30 secondes
        setInterval(() => this.checkNewNotifications(), 30000);
        
        // Vérifier immédiatement au chargement
        this.checkNewNotifications();
    }
    
    checkNewNotifications() {
        // Utiliser XMLHttpRequest pour la compatibilité
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '?page=user&action=check_notifications', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    // Mettre à jour le compteur interne
                    this.unreadCount = parseInt(response.unread_count) || 0;
                    this.updateNotificationBadge(this.unreadCount);
                    
                    if (response.new_notifications && response.new_notifications.length > 0) {
                        this.addNewNotifications(response.new_notifications);
                    }
                } catch (e) {
                    console.error('Erreur lors de la vérification des notifications:', e);
                }
            }
        };
        
        xhr.send();
    }
    
    updateNotificationBadge(count) {
        if (!this.notificationBadge) return;
        
        // Mettre à jour la classe visible et le style en fonction du compteur
        if (count > 0) {
            this.notificationBadge.textContent = count;
            this.notificationBadge.style.display = 'inline-block';
            this.notificationBadge.classList.add('visible');
            // Forcer le recalcul du style
            void this.notificationBadge.offsetWidth;
        } else {
            // Si aucune notification, cacher la pastille
            this.notificationBadge.style.display = 'none';
            this.notificationBadge.classList.remove('visible');
        }
    }
    
    addNewNotifications(notifications) {
        if (!this.notificationList) return;
        
        notifications.forEach(notif => {
            // Créer un élément de notification
            const notifElement = document.createElement('div');
            notifElement.className = 'notification-item unread';
            
            // Formater la date
            const date = new Date(notif.created_at);
            const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            
            // Contenu HTML
            notifElement.innerHTML = `
                <div class="notification-header">
                    <h5><span class="badge bg-primary me-2">Nouveau</span>${notif.titre}</h5>
                    <span class="notification-date">${formattedDate}</span>
                </div>
                <p>${notif.message}</p>
                <div class="notification-actions">
                    <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="${notif.id}">
                        <i class="fas fa-check me-1"></i> Marquer comme lu
                    </button>
                </div>
            `;
            
            // Ajouter l'événement sur le bouton
            const readBtn = notifElement.querySelector('.mark-read-btn');
            readBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.markAsRead(notif.id, notifElement);
            });
            
            // Ajouter au début de la liste
            this.notificationList.insertBefore(notifElement, this.notificationList.firstChild);
        });
    }
    
    markAsRead(id, element) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `?page=user&action=markNotificationRead&id=${id}`, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        if (element) {
                            element.classList.remove('unread');
                            const actionBtn = element.querySelector('.notification-actions');
                            if (actionBtn) actionBtn.remove();
                            
                            // Retirer le badge "Nouveau"
                            const badge = element.querySelector('.badge.bg-primary');
                            if (badge) badge.remove();
                        }
                        
                        // Mettre à jour le compteur interne
                        this.unreadCount = parseInt(response.unread_count) || 0;
                        this.updateNotificationBadge(this.unreadCount);
                    }
                } catch (e) {
                    console.error('Erreur lors du traitement de la réponse:', e);
                }
            }
        };
        
        xhr.send();
    }
    
    markAllAsRead() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '?page=user&action=mark_all_read', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Marquer visuellement toutes les notifications comme lues
                        const unreadItems = document.querySelectorAll('.notification-item.unread');
                        unreadItems.forEach(item => {
                            item.classList.remove('unread');
                            const actions = item.querySelector('.notification-actions');
                            if (actions) actions.remove();
                            
                            // Retirer le badge "Nouveau"
                            const badge = item.querySelector('.badge.bg-primary');
                            if (badge) badge.remove();
                        });
                        
                        // Mettre à jour le compteur interne
                        this.unreadCount = 0;
                        this.updateNotificationBadge(0);
                        
                        // Feedback utilisateur si on est sur la page de notifications
                        if (window.location.href.includes('action=notifications') && this.markAllReadBtn) {
                            this.markAllReadBtn.style.display = 'none';
                            
                            // Afficher un message de confirmation
                            const alertBox = document.createElement('div');
                            alertBox.className = 'alert alert-success mt-3';
                            alertBox.innerHTML = '<i class="fas fa-check-circle me-2"></i> Toutes les notifications ont été marquées comme lues';
                            document.querySelector('.container').prepend(alertBox);
                            
                            // Faire disparaître l'alerte après 3 secondes
                            setTimeout(() => {
                                alertBox.style.opacity = '0';
                                alertBox.style.transition = 'opacity 0.5s';
                                setTimeout(() => alertBox.remove(), 500);
                            }, 3000);
                        }
                    }
                } catch (e) {
                    console.error('Erreur lors du traitement de la réponse:', e);
                }
            }
        };
        
        xhr.send();
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    new NotificationManager();
});
