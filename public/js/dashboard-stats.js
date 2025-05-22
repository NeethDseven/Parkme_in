/**
 * Script pour afficher les statistiques sur le tableau de bord
 */
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si les conteneurs de graphiques existent
    const weeklyOccupationChart = document.getElementById('weekly-occupation-chart');
    const monthlyRevenueChart = document.getElementById('monthly-revenue-chart');
    
    // Si on est sur le tableau de bord administrateur
    if (weeklyOccupationChart && typeof weeklyOccupationData !== 'undefined') {
        renderWeeklyOccupationChart(weeklyOccupationData);
    }
    
    if (monthlyRevenueChart && typeof monthlyRevenueData !== 'undefined') {
        renderMonthlyRevenueChart(monthlyRevenueData);
    }
    
    // Fonction pour rendre le graphique d'occupation hebdomadaire
    function renderWeeklyOccupationChart(data) {
        // Créer les tableaux de jours et de valeurs
        const jours = [];
        const occupations = [];
        
        // Traduction des jours en français
        const joursFrancais = {
            'Monday': 'Lundi',
            'Tuesday': 'Mardi',
            'Wednesday': 'Mercredi',
            'Thursday': 'Jeudi',
            'Friday': 'Vendredi',
            'Saturday': 'Samedi',
            'Sunday': 'Dimanche'
        };
        
        // Parcourir les données
        data.forEach(item => {
            jours.push(joursFrancais[item.jour] || item.jour);
            occupations.push(item.places_occupees);
        });
        
        // Création d'un canvas pour le graphique
        const canvas = document.createElement('canvas');
        weeklyOccupationChart.appendChild(canvas);
        
        // Créer le graphique
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: jours,
                datasets: [{
                    label: 'Places occupées',
                    data: occupations,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    }
    
    // Fonction pour rendre le graphique des revenus mensuels
    function renderMonthlyRevenueChart(data) {
        // Créer les tableaux de jours et de valeurs
        const jours = [];
        const revenus = [];
        
        // Parcourir les données
        data.forEach(item => {
            jours.push(item.jour);
            revenus.push(item.montant);
        });
        
        // Création d'un canvas pour le graphique
        const canvas = document.createElement('canvas');
        monthlyRevenueChart.appendChild(canvas);
        
        // Créer le graphique
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: jours,
                datasets: [{
                    label: 'Revenus (€)',
                    data: revenus,
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Ajouter des animations aux statistiques
    const statCounters = document.querySelectorAll('.stat-counter');
    statCounters.forEach(counter => {
        const target = parseInt(counter.dataset.target, 10);
        
        // Animation simple de compteur
        let count = 0;
        const duration = 1500; // 1.5 secondes
        const frameDuration = 1000 / 60; // 60 FPS
        const totalFrames = Math.round(duration / frameDuration);
        const increment = target / totalFrames;
        
        const animate = () => {
            count += increment;
            counter.textContent = Math.floor(count);
            
            if (count < target) {
                requestAnimationFrame(animate);
            } else {
                counter.textContent = target;
            }
        };
        
        animate();
    });
});
