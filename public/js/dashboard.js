/**
 * Système de tableau de bord visuel pour l'admin et les utilisateurs
 */
class Dashboard {
    constructor() {
        // Initialiser les composants du tableau de bord
        this.initCards();
        this.initOccupationChart();
        this.initRevenueChart();
    }
    
    initCards() {
        // Animation des cartes de stats
        const statCounters = document.querySelectorAll('.stat-counter');
        
        statCounters.forEach(counter => {
            const target = parseInt(counter.dataset.value || 0, 10);
            
            // Compteur simple
            if (target) {
                let count = 0;
                const increment = Math.ceil(target / 30); // Compter en 30 étapes
                
                const timer = setInterval(() => {
                    count += increment;
                    
                    if (count >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = count;
                    }
                }, 50);
            }
        });
    }
    
    initOccupationChart() {
        const occupationCanvas = document.getElementById('occupation-chart');
        if (!occupationCanvas || typeof occupationData === 'undefined') return;
        
        // Données pré-formatées depuis PHP
        const labels = [];
        const data = [];
        
        // Récupérer les données
        occupationData.forEach(item => {
            labels.push(item.jour);
            data.push(item.places_occupees);
        });
        
        // Dessiner un graphique en barres simple
        this.drawBarChart(occupationCanvas, labels, data, 'Places occupées');
    }
    
    initRevenueChart() {
        const revenueCanvas = document.getElementById('revenue-chart');
        if (!revenueCanvas || typeof revenueData === 'undefined') return;
        
        // Données pré-formatées depuis PHP
        const labels = [];
        const data = [];
        
        // Récupérer les données
        revenueData.forEach(item => {
            labels.push(item.jour);
            data.push(item.montant);
        });
        
        // Dessiner un graphique en ligne simple
        this.drawLineChart(revenueCanvas, labels, data, 'Revenus (€)');
    }
    
    drawBarChart(canvas, labels, data, label) {
        const ctx = canvas.getContext('2d');
        
        // Définir les dimensions
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = 300;
        
        // Taille des barres
        const barWidth = canvas.width / (labels.length * 2); 
        const spacing = barWidth / 2;
        const maxValue = Math.max(...data) || 1;
        
        // Effacer le canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Dessiner les axes
        ctx.beginPath();
        ctx.moveTo(40, 20);
        ctx.lineTo(40, canvas.height - 40);
        ctx.lineTo(canvas.width - 20, canvas.height - 40);
        ctx.strokeStyle = '#333';
        ctx.stroke();
        
        // Dessiner le titre
        ctx.font = '14px Arial';
        ctx.fillStyle = '#333';
        ctx.fillText(label, canvas.width / 2 - 50, 15);
        
        // Dessiner chaque barre
        ctx.fillStyle = 'rgba(52, 152, 219, 0.7)';
        
        data.forEach((value, index) => {
            const x = 40 + (index * 2 + 1) * barWidth;
            const barHeight = (value / maxValue) * (canvas.height - 60);
            const y = canvas.height - 40 - barHeight;
            
            // Dessiner la barre
            ctx.fillRect(x, y, barWidth, barHeight);
            
            // Dessiner l'étiquette
            ctx.fillStyle = '#333';
            ctx.fillText(labels[index], x, canvas.height - 25);
            ctx.fillText(value, x, y - 5);
            
            // Réinitialiser pour la prochaine barre
            ctx.fillStyle = 'rgba(52, 152, 219, 0.7)';
        });
    }
    
    drawLineChart(canvas, labels, data, label) {
        const ctx = canvas.getContext('2d');
        
        // Définir les dimensions
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = 300;
        
        const maxValue = Math.max(...data) || 1;
        const pointSpacing = (canvas.width - 60) / (labels.length - 1);
        
        // Effacer le canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Dessiner les axes
        ctx.beginPath();
        ctx.moveTo(40, 20);
        ctx.lineTo(40, canvas.height - 40);
        ctx.lineTo(canvas.width - 20, canvas.height - 40);
        ctx.strokeStyle = '#333';
        ctx.stroke();
        
        // Dessiner le titre
        ctx.font = '14px Arial';
        ctx.fillStyle = '#333';
        ctx.fillText(label, canvas.width / 2 - 50, 15);
        
        // Dessiner la ligne
        ctx.beginPath();
        
        data.forEach((value, index) => {
            const x = 40 + index * pointSpacing;
            const y = canvas.height - 40 - (value / maxValue) * (canvas.height - 60);
            
            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
            
            // Dessiner un point
            ctx.fillStyle = 'rgba(46, 204, 113, 0.7)';
            ctx.beginPath();
            ctx.arc(x, y, 5, 0, Math.PI * 2);
            ctx.fill();
            
            // Dessiner l'étiquette
            ctx.fillStyle = '#333';
            ctx.fillText(labels[index], x - 10, canvas.height - 25);
            ctx.fillText(value + '€', x - 10, y - 10);
        });
        
        ctx.strokeStyle = 'rgba(46, 204, 113, 0.7)';
        ctx.lineWidth = 2;
        ctx.stroke();
    }
}

// Initialisation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    new Dashboard();
});
