# Matrice RACI - Projet ParkMeIn

## Légende
- **R** : Responsible (Responsable de l'exécution)
- **A** : Accountable (Responsable final avec droit de veto)
- **C** : Consulted (Consulté avant la décision)
- **I** : Informed (Informé après la décision)

## Matrice des responsabilités

| Activité / Tâche | Chef de Projet | Développeur Frontend | Développeur Backend | UI/UX Designer | Testeur | Client |
|------------------|----------------|----------------------|---------------------|---------------|---------|--------|
| **Phase de cadrage** |
| Note de cadrage | A/R | I | I | I | I | C |
| CDCF | A/R | C | C | C | C | C/I |
| CDCT | A | C | R | C | C | I |
| Planning (Gantt) | A/R | C | C | C | I | I |
| **Phase de conception** |
| Architecture technique | A | C | R | I | I | I |
| Maquettes UI/UX | A | C | I | R | I | C |
| Modèle de données | A | I | R | I | I | I |
| **Phase de développement** |
| Développement Frontend | I | R | C | C | I | I |
| Développement Backend | I | C | R | I | I | I |
| Intégration UI | I | R | C | A/C | I | I |
| **Phase de tests** |
| Tests unitaires | I | R | R | I | A/C | I |
| Tests d'intégration | I | C | C | I | R | I |
| Tests utilisateur | A | I | I | C | R | C |
| **Phase de déploiement** |
| Mise en production | A | C | R | I | C | I |
| Formation utilisateurs | A | C | C | I | I | R |
| **Gestion de projet** |
| Animation des sprints | R | I | I | I | I | I |
| Suivi des risques | A/R | C | C | I | C | I |
| Bilan de projet | A/R | C | C | C | C | C |
