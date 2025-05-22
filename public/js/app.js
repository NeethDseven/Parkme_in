/**
 * Fichier JavaScript principal pour l'application Parkme In
 */

// Fonction exécutée quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si Bootstrap est chargé
    if (typeof bootstrap !== 'undefined') {
        // Initialiser les tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialiser les popovers Bootstrap
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function(popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Initialiser les alertes auto-fermantes
        var autoAlerts = document.querySelectorAll('.alert-dismissible.auto-dismiss');
        autoAlerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    }
    
    // Gestion des formulaires
    setupFormValidation();
    
    // Amélioration de l'expérience utilisateur
    setupUIEnhancements();
});

// Fonction pour configurer la validation des formulaires
function setupFormValidation() {
    // Validation des formulaires avec la classe 'needs-validation'
    var forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Validation spécifique pour les formulaires de réservation
    var reservationForms = document.querySelectorAll('.reservation-form');
    reservationForms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var dateDebut = form.querySelector('[name="date_debut"]');
            var dateFin = form.querySelector('[name="date_fin"]');
            
            if (dateDebut && dateFin && new Date(dateFin.value) <= new Date(dateDebut.value)) {
                event.preventDefault();
                alert("La date de fin doit être postérieure à la date de début");
            }
        });
    });
}

// Fonction pour améliorer l'expérience utilisateur
function setupUIEnhancements() {
    // Activer les liens actifs dans la navigation
    highlightActiveNavLinks();
    
    // Ajouter des effets de scroll fluide
    setupSmoothScrolling();
    
    // Ajouter des animations aux éléments avec data-animate
    setupAnimations();
}

// Fonction pour mettre en évidence les liens actifs dans la navigation
function highlightActiveNavLinks() {
    // Récupérer l'URL courante
    var currentPage = window.location.href;
    
    // Trouver tous les liens de navigation
    var navLinks = document.querySelectorAll('nav a.nav-link');
    
    // Parcourir les liens et ajouter la classe active si nécessaire
    navLinks.forEach(function(link) {
        if (currentPage.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
}

// Fonction pour configurer le défilement fluide
function setupSmoothScrolling() {
    // Trouver tous les liens internes (commençant par #)
    var smoothLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
    
    smoothLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            var targetId = this.getAttribute('href');
            var targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Fonction pour configurer les animations
function setupAnimations() {
    // Sélectionner tous les éléments avec l'attribut data-animate
    var animatedElements = document.querySelectorAll('[data-animate]');
    
    function checkInView() {
        var windowHeight = window.innerHeight;
        var windowTopPosition = window.pageYOffset;
        var windowBottomPosition = windowTopPosition + windowHeight;
        
        animatedElements.forEach(function(element) {
            var elementHeight = element.offsetHeight;
            var elementTopPosition = element.offsetTop;
            var elementBottomPosition = elementTopPosition + elementHeight;
            
            // Vérifier si l'élément est visible
            if ((elementBottomPosition >= windowTopPosition) && 
                (elementTopPosition <= windowBottomPosition)) {
                
                // Ajouter la classe d'animation
                var animationClass = element.getAttribute('data-animate');
                element.classList.add('animated', animationClass);
                
                // Supprimer l'attribut pour éviter de répéter l'animation
                element.removeAttribute('data-animate');
            }
        });
    }
    
    // Vérifier les éléments au chargement de la page
    checkInView();
    
    // Vérifier les éléments lors du défilement
    window.addEventListener('scroll', checkInView);
}
