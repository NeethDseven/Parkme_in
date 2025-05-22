/**
 * Graphiques pour les statistiques de remboursements
 */
document.addEventListener('DOMContentLoaded', function() {
    const remboursementsCtx = document.getElementById('remboursements-chart');
    
    if (remboursementsCtx && typeof chartData !== 'undefined') {
        const chart = new Chart(remboursementsCtx, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Nombre de remboursements',
                        data: chartData.counts,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Montant remboursé (€)',
                        data: chartData.amounts,
                        type: 'line',
                        fill: false,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: {
                            display: false
                        },
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Nombre de remboursements'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Montant remboursé (€)'
                        }
                    }
                }
            }
        });
    }
});
