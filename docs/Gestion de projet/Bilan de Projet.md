# Bilan de Projet - ParkMeIn

**Date du bilan :** 15/09/2025  
**Période du projet :** 01/06/2025 - 15/09/2025  
**Rédacteur :** Labidi Sami  
**Version :** 1.0

## 1. Résumé du projet

Le projet ParkMeIn visait à développer une application web de gestion de parking permettant aux utilisateurs de réserver des places, de gérer leurs réservations et d'effectuer des paiements en ligne. L'application devait également offrir une interface d'administration complète pour gérer les utilisateurs, les places, les tarifs et suivre les revenus.

Le projet a été mené sur une période de 3,5 mois, en utilisant une méthodologie Agile avec 4 sprints. L'application a été développée en utilisant les technologies PHP, JavaScript et MySQL, en respectant une architecture MVC et les principes de programmation orientée objet.

## 2. Objectifs vs. Réalisations

### Objectifs initiaux
1. Développer une application web de gestion de parking responsive et intuitive
2. Permettre la réservation et le paiement des places de parking en ligne
3. Fournir un système de notification et d'alertes
4. Offrir une interface d'administration complète
5. Respecter les contraintes techniques (sans framework externe, architecture MVC)

### Réalisations
| Objectif | Statut | Commentaire |
|----------|--------|-------------|
| Application web responsive | ✅ Atteint | Interface responsive testée sur différents appareils |
| Système de réservation | ✅ Atteint | Fonctionnalité complète avec calendrier et validation |
| Système de paiement | ✅ Atteint | Simulation de paiement par CB et PayPal |
| Système de notification | ⚠️ Partiellement | Notifications système complètes, emails partiellement implémentés |
| Interface d'administration | ✅ Atteint | Toutes les fonctionnalités d'administration livrées |
| Respect des contraintes techniques | ✅ Atteint | Architecture MVC, POO, sans framework externe |

## 3. Performances du projet

### Délais
- **Planifié :** 3,5 mois (01/06/2025 - 15/09/2025)
- **Réalisé :** 3,5 mois (01/06/2025 - 15/09/2025)
- **Écart :** Aucun retard global, mais certaines phases ont connu des variations :
  - Phase de conception : +3 jours
  - Phase de développement : -2 jours
  - Phase de tests : -1 jour

### Budget
- **Budget initial :** 75 000 €
- **Budget consommé :** 74 200 €
- **Écart :** -800 € (1,1% d'économie)

### Qualité
- **Tests unitaires :** 89% de couverture de code
- **Bugs critiques :** 0 en production
- **Bugs mineurs :** 5 identifiés, 4 résolus

## 4. Analyse des écarts

### Écarts positifs
- **Performance technique :** L'application répond plus rapidement que prévu dans les spécifications
- **Budget :** Légère économie réalisée grâce à l'efficacité du développement
- **Qualité :** Taux de bugs inférieur aux prévisions grâce à l'approche TDD

### Écarts négatifs
- **Notifications par email :** Fonctionnalité partiellement implémentée par manque de temps
- **Documentation utilisateur :** Plus longue à produire que prévu initialement
- **Phase de conception :** Retard dû à des itérations supplémentaires sur les maquettes UI

## 5. Risques identifiés vs. survenus

| Risque identifié | Probabilité initiale | Impact initial | Est survenu ? | Actions prises |
|------------------|----------------------|--------------|---------------|----------------|
| Délai de développement sous-estimé | Moyenne | Fort | Non | N/A |
| Problèmes techniques imprévus | Moyenne | Moyen | Oui | Résolution rapide par l'équipe |
| Évolution des besoins | Faible | Moyen | Non | N/A |
| Indisponibilité des ressources | Faible | Fort | Non | N/A |
| **Non identifié initialement :** Complexité d'intégration du système de notification | - | - | Oui | Priorisation des notifications système, report partiel des notifications email |

## 6. Bénéfices réalisés

### Bénéfices tangibles
- Application opérationnelle répondant à 95% des exigences initiales
- Système de réservation performant avec un temps de réponse < 1s
- Interface utilisateur intuitive nécessitant peu de formation

### Bénéfices intangibles
- Amélioration des compétences de l'équipe en architecture MVC
- Renforcement de la méthodologie Agile au sein de l'équipe
- Développement d'un socle technique réutilisable pour d'autres projets

## 7. Problèmes rencontrés et solutions

| Problème | Impact | Solution |
|----------|--------|----------|
| Complexité du système de notification | Retard sur cette fonctionnalité | Priorisation et livraison partielle |
| Difficultés d'intégration du calendrier de réservation | Retard de 2 jours | Session de pair programming |
| Faille de sécurité identifiée dans le processus d'authentification | Critique | Refactoring immédiat et tests sécurité |
| Performance des requêtes SQL pour le tableau de bord administrateur | Lenteur | Optimisation des requêtes et mise en cache |

## 8. Retours d'expérience et bonnes pratiques

### Ce qui a bien fonctionné
- Méthodologie Agile avec sprints de 2 semaines
- Tests unitaires systématiques
- Architecture MVC bien structurée
- Reviews de code régulières

### Ce qui pourrait être amélioré
- Estimation plus précise de la complexité des fonctionnalités
- Anticipation des dépendances entre fonctionnalités
- Documentation technique plus détaillée et continue
- Implication plus précoce des utilisateurs finaux

## 9. Recommandations pour les futurs projets

1. **Planification :** Prévoir plus de temps pour les phases de tests et de documentation
2. **Technique :** Mettre en place un environnement CI/CD dès le début du projet
3. **Méthodologie :** Continuer avec l'approche Agile mais améliorer le processus d'estimation
4. **Formation :** Renforcer les compétences en sécurité web et optimisation de performance
5. **Documentation :** Mettre en place un processus de documentation continue

## 10. Conclusion générale

Le projet ParkMeIn a été globalement un succès, livré dans les délais et le budget prévus, avec un niveau de qualité satisfaisant. Les principales fonctionnalités ont été implémentées conformément aux exigences initiales, à l'exception du système de notification par email qui n'est que partiellement opérationnel.

L'architecture technique mise en place est solide et évolutive, ce qui facilitera la maintenance future et les évolutions de l'application. L'expérience utilisateur a été particulièrement soignée, ce qui devrait faciliter l'adoption de l'outil.

Les retours d'expérience de ce projet seront précieux pour améliorer la conduite des projets futurs, notamment en termes d'estimation de la complexité des fonctionnalités et de gestion des dépendances techniques.

## 11. Signatures

**Chef de projet : Labidi Sami  Date : 17/05/2025

**Client :** CODA School Date : 12/04/2025

**Responsable technique :** LABIDI Sami Date : 12/04/2025
