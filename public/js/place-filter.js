/**
 * Système de filtrage des places de parking en temps réel
 */
(function() {
    // Éléments DOM
    const filterForm = document.getElementById('filter-form');
    const typeSelect = document.getElementById('type-filter');
    const resetButton = document.getElementById('reset-filter');
    const placeCards = document.querySelectorAll('.place-card');
    const countElement = document.getElementById('places-count');
    
    if (!filterForm) return;
    
    // Attacher les événements
    function attachEvents() {
        // Filtrage au changement de type
        if (typeSelect) {
            typeSelect.addEventListener('change', () => filterPlaces());
        }
        
        // Réinitialisation des filtres
        if (resetButton) {
            resetButton.addEventListener('click', () => resetFilters());
        }
    }
    
    function filterPlaces() {
        const selectedType = typeSelect ? typeSelect.value : '';
        let visibleCount = 0;
        
        placeCards.forEach(card => {
            const placeType = card.dataset.type;
            
            // Vérifier si la place correspond au type sélectionné
            const matchesType = !selectedType || placeType === selectedType;
            
            // Afficher ou masquer la place
            if (matchesType) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Mettre à jour le compteur
        if (countElement) {
            countElement.textContent = visibleCount;
        }
        
        // Mettre à jour l'URL sans recharger la page
        const url = new URL(window.location.href);
        if (selectedType) {
            url.searchParams.set('type', selectedType);
        } else {
            url.searchParams.delete('type');
        }
        window.history.replaceState({}, '', url);
    }
    
    function resetFilters() {
        if (typeSelect) {
            typeSelect.value = '';
        }
        
        // Réafficher toutes les places
        placeCards.forEach(card => {
            card.style.display = '';
        });
        
        // Mettre à jour le compteur
        if (countElement) {
            countElement.textContent = placeCards.length;
        }
        
        // Mettre à jour l'URL sans recharger la page
        const url = new URL(window.location.href);
        url.searchParams.delete('type');
        window.history.replaceState({}, '', url);
    }
    
    // Initialiser les événements
    attachEvents();
})();
