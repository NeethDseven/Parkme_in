/**
 * Système de graphiques simplifié pour l'application Parkme In
 * Utilise Chart.js
 */
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si les données pour les graphiques sont disponibles
    if (typeof appData === 'undefined' || !appData.hasCharts) {
        console.warn('Aucune donnée pour les graphiques n\'est disponible.');
        return;
    }
    
    console.log('Initialisation des graphiques avec les données:', appData);
    
    // Fonction pour créer un graphique en barres
    function createBarChart(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`Conteneur ${containerId} introuvable.`);
            return;
        }
        
        // Créer un canvas dans le conteneur
        const canvas = document.createElement('canvas');
        container.appendChild(canvas);
        
        // Fusionner les options par défaut avec les options personnalisées
        const chartOptions = Object.assign({
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: options.title || 'Graphique'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }, options.chartOptions || {});
        
        // Créer le graphique
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label || 'Valeurs',
                    data: data.values,
                    backgroundColor: options.backgroundColor || 'rgba(52, 152, 219, 0.6)',
                    borderColor: options.borderColor || 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: chartOptions
        });
    }
    
    // Fonction pour créer un graphique en ligne
    function createLineChart(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`Conteneur ${containerId} introuvable.`);
            return;
        }
        
        // Créer un canvas dans le conteneur
        const canvas = document.createElement('canvas');
        container.appendChild(canvas);
        
        // Fusionner les options par défaut avec les options personnalisées
        const chartOptions = Object.assign({
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: options.title || 'Graphique'
                }
            }
        }, options.chartOptions || {});
        
        // Créer le graphique
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label || 'Valeurs',
                    data: data.values,
                    fill: false,
                    backgroundColor: options.backgroundColor || 'rgba(46, 204, 113, 0.2)',
                    borderColor: options.borderColor || 'rgba(46, 204, 113, 1)',
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: chartOptions
        });
    }
    
    // Créer les graphiques définis dans appData
    if (appData.charts) {
        appData.charts.forEach(chart => {
            if (chart.type === 'bar') {
                createBarChart(chart.container, chart.data, chart.options);
            } else if (chart.type === 'line') {
                createLineChart(chart.container, chart.data, chart.options);
            } else {
                console.warn(`Type de graphique inconnu: ${chart.type}`);
            }
        });
    }
    
    // Créer des graphiques spécifiques si les données existent
    if (appData.occupationData) {
        const labels = [];
        const values = [];
        
        appData.occupationData.forEach(item => {
            // Traduction des jours en français si nécessaire
            if (item.jour) {
                const joursFrancais = {
                    'Monday': 'Lundi',
                    'Tuesday': 'Mardi',
                    'Wednesday': 'Mercredi',
                    'Thursday': 'Jeudi',
                    'Friday': 'Vendredi',
                    'Saturday': 'Samedi',
                    'Sunday': 'Dimanche'
                };
                labels.push(joursFrancais[item.jour] || item.jour);
            } else {
                labels.push(item.date || item.heure || '');
            }
            
            values.push(item.places_occupees || item.nombre || 0);
        });
        
        createBarChart('occupation-chart', {
            labels: labels,
            values: values,
            label: 'Places occupées'
        }, {
            title: 'Occupation des places par jour'
        });
    }
    
    if (appData.revenueData) {
        const labels = [];
        const values = [];
        
        appData.revenueData.forEach(item => {
            labels.push(item.jour || item.date || item.heure || '');
            values.push(item.montant || item.total || 0);
        });
        
        createLineChart('revenue-chart', {
            labels: labels,
            values: values,
            label: 'Revenus (€)'
        }, {
            title: 'Revenus par jour',
            chartOptions: {
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
});
