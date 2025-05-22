<?php
require_once 'backend/Services/PaginationService.php';

class ParkingController {
    private $db;
    private $itemsPerPage = 9; // Nombre de places par page

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Liste les places disponibles avec support du filtrage dynamique
     */
    public function listAvailable() {
        // Mise à jour des places avec des réservations actives
        $this->updatePlacesStatus();
        
        // Récupérer le filtre de type
        $typeFilter = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1;
        
        // Construire la requête SQL de base
        $countSql = "SELECT COUNT(*) FROM parking_spaces WHERE status != 'maintenance'"; // Changé de 'libre' à 'maintenance'
        
        // Modifier la requête SQL pour inclure les informations de tarif
        $sql = "SELECT p.*, t.prix_heure, t.prix_journee, t.prix_mois 
                FROM parking_spaces p
                LEFT JOIN tarifs t ON p.type = t.type_place 
                WHERE p.status != 'maintenance'"; // Changé de 'libre' à 'maintenance'
        
        $params = [];
        
        // Ajouter le filtre par type si spécifié
        if ($typeFilter) {
            $countSql .= " AND type = ?";
            $sql .= " AND p.type = ?";
            $params[] = $typeFilter;
        }
        
        // Compter le nombre total de places disponibles
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetchColumn();
        
        // Initialiser la pagination
        $pagination = new PaginationService($totalItems, $this->itemsPerPage, $currentPage);
        
        // Compléter la requête avec l'ordre et la pagination
        $sql .= " ORDER BY p.numero LIMIT ?, ?";
        $params[] = $pagination->getOffset();
        $params[] = $pagination->getLimit();
        
        // Exécuter la requête paginée
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $places = $stmt->fetchAll();

        // Pour chaque place, récupérer les prochaines réservations
        foreach ($places as &$place) {
            $stmt = $this->db->prepare("
                SELECT date_debut, date_fin 
                FROM reservations 
                WHERE place_id = ? 
                AND status = 'confirmée' 
                AND date_fin > NOW() 
                ORDER BY date_debut 
                LIMIT 3
            ");
            $stmt->execute([$place['id']]);
            $place['prochaines_reservations'] = $stmt->fetchAll();
        }

        // Récupérer les types de places pour le filtrage dynamique
        $stmt = $this->db->query("SELECT DISTINCT type FROM parking_spaces");
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Construire l'URL de base pour la pagination (sans les paramètres GET)
        $baseUrl = BASE_URL;
        
        // Préparer les paramètres pour les liens de pagination (conserver le filtre de type)
        $queryParams = [
            'page' => 'parking',
            'action' => 'list'
        ];
        
        if ($typeFilter) {
            $queryParams['type'] = $typeFilter;
        }
        
        // Générer les liens de pagination en passant explicitement 'page' et 'action'
        $paginationLinks = $pagination->createLinks($baseUrl, $queryParams);
        
        // Ajouter les scripts JS pour le filtrage dynamique
        $extraJS = ['place-filter.js'];
        
        // Charger la vue
        require_once 'frontend/Views/parking/list.php';
    }

    public function viewPlace() {
        try {
            // Vérification de l'authentification
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . '/?page=login');
                exit;
            }

            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID de place invalide");
            }

            // Récupération des informations de la place avec plus de détails pour JS
            $stmt = $this->db->prepare("
                SELECT p.*, t.prix_heure, t.prix_journee, t.prix_mois
                FROM parking_spaces p
                JOIN tarifs t ON p.type = t.type_place
                WHERE p.id = ? AND p.status != 'maintenance'
            "); // Changé de 'libre' à 'maintenance'
            $stmt->execute([$id]);
            $place = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$place) {
                throw new Exception("Place non disponible");
            }
            
            // Récupérer les créneaux indisponibles (tous types de réservations)
            // Inclure toutes les réservations pour afficher les créneaux grisés
            $stmt = $this->db->prepare("
                SELECT 
                    date_debut, 
                    date_fin,
                    status
                FROM reservations 
                WHERE place_id = ? 
                AND (status = 'confirmée' OR status = 'en_attente')
                AND date_fin > NOW()
                ORDER BY date_debut
            ");
            $stmt->execute([$id]);
            $creneauxIndisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Récupérer les alertes de l'utilisateur pour cette place
            $stmt = $this->db->prepare("
                SELECT * 
                FROM alertes_disponibilite 
                WHERE user_id = ? AND place_id = ? AND statut != 'expiree'
                ORDER BY date_debut
            ");
            $stmt->execute([$_SESSION['user_id'], $id]);
            $alertesUtilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ajouter les scripts JS
            $extraJS = ['reservation-calendar.js'];
            
            // Traitement du formulaire de réservation
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dateDebut = $_POST['date_debut'];
                $dateFin = $_POST['date_fin'];
                
                if (empty($dateDebut) || empty($dateFin)) {
                    throw new Exception("Les dates sont obligatoires");
                }
                
                // Validation de base des dates
                if (strtotime($dateFin) <= strtotime($dateDebut)) {
                    throw new Exception("La date de fin doit être après la date de début");
                }

                // Vérification des chevauchements UNIQUEMENT avec les réservations CONFIRMÉES
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM reservations 
                    WHERE place_id = ? 
                    AND status = 'confirmée'  -- Ignorer les réservations en attente
                    AND (
                        (date_debut BETWEEN ? AND ?) 
                        OR (date_fin BETWEEN ? AND ?)
                        OR (date_debut <= ? AND date_fin >= ?)
                    )
                ");
                $stmt->execute([$id, $dateDebut, $dateFin, $dateDebut, $dateFin, $dateDebut, $dateFin]);
                $overlapCount = $stmt->fetchColumn();
                
                if ($overlapCount > 0) {
                    throw new Exception("La place est déjà réservée pour les dates sélectionnées");
                }
                
                // Début de transaction
                $this->db->beginTransaction();
                
                try {
                    // Créer la réservation avec statut "en_attente"
                    $stmt = $this->db->prepare("
                        INSERT INTO reservations (user_id, place_id, date_debut, date_fin, status)
                        VALUES (?, ?, ?, ?, 'en_attente')
                    ");
                    
                    if (!$stmt->execute([$_SESSION['user_id'], $id, $dateDebut, $dateFin])) {
                        throw new Exception("Erreur lors de la création de la réservation");
                    }
                    
                    $reservation_id = $this->db->lastInsertId();
                    
                    // Calculer le prix et créer le paiement
                    $duree = (strtotime($dateFin) - strtotime($dateDebut)) / 3600; // en heures
                    $prix = $duree <= 24 ? $duree * $place['prix_heure'] : ceil($duree/24) * $place['prix_journee'];
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO paiements (reservation_id, montant, status)
                        VALUES (?, ?, 'en_attente')
                    ");
                    if (!$stmt->execute([$reservation_id, $prix])) {
                        throw new Exception("Erreur lors de la création du paiement");
                    }
                    
                    // Ajouter une notification pour la réservation en attente
                    require_once 'backend/Services/NotificationService.php';
                    $notificationService = new NotificationService();
                    $notificationService->createNotification(
                        $_SESSION['user_id'],
                        'Réservation en attente de paiement',
                        "Votre réservation de la place n°{$place['numero']} du " . 
                        date('d/m/Y H:i', strtotime($dateDebut)) . " au " . 
                        date('d/m/Y H:i', strtotime($dateFin)) . " a été créée. Veuillez finaliser votre paiement pour la confirmer.",
                        'reservation'
                    );
                    
                    $this->db->commit();
                    
                    // Redirection vers la page de paiement
                    header('Location: ' . BASE_URL . '/?page=user&action=payment&reservation_id=' . $reservation_id);
                    exit;
                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            }

            require_once 'frontend/Views/parking/view.php';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=parking&action=list');
            exit;
        }
    }
    
    /**
     * Crée une alerte de disponibilité pour un créneau
     */
    public function createAlert() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
            exit;
        }
        
        // Vérifier si c'est une requête AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            exit;
        }
        
        try {
            $placeId = filter_input(INPUT_POST, 'place_id', FILTER_VALIDATE_INT);
            $dateDebut = filter_input(INPUT_POST, 'date_debut', FILTER_SANITIZE_STRING);
            $dateFin = filter_input(INPUT_POST, 'date_fin', FILTER_SANITIZE_STRING);
            
            if (!$placeId || !$dateDebut || !$dateFin) {
                throw new Exception("Paramètres invalides");
            }
            
            // Vérifier que la place existe
            $stmt = $this->db->prepare("SELECT numero FROM parking_spaces WHERE id = ?");
            $stmt->execute([$placeId]);
            $place = $stmt->fetch();
            
            if (!$place) {
                throw new Exception("Place non trouvée");
            }
            
            // Vérifier si la date n'est pas déjà passée
            if (strtotime($dateDebut) < time()) {
                throw new Exception("Vous ne pouvez pas créer d'alerte pour un créneau déjà passé");
            }
            
            // Vérifier la disponibilité actuelle pour informer l'utilisateur
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations
                WHERE place_id = ?
                AND status = 'confirmée'
                AND (
                    (date_debut <= ? AND date_fin > ?) OR
                    (date_debut < ? AND date_fin >= ?) OR
                    (date_debut >= ? AND date_fin <= ?)
                )
            ");
            $stmt->execute([
                $placeId,
                $dateDebut, $dateDebut,
                $dateFin, $dateFin,
                $dateDebut, $dateFin
            ]);
            
            $isUnavailable = $stmt->fetchColumn() > 0;
            
            // Vérifier que l'alerte n'existe pas déjà
            $stmt = $this->db->prepare("
                SELECT id FROM alertes_disponibilite 
                WHERE user_id = ? AND place_id = ? 
                AND date_debut = ? AND date_fin = ? 
                AND statut IN ('en_attente', 'notifiee')
            ");
            $stmt->execute([$_SESSION['user_id'], $placeId, $dateDebut, $dateFin]);
            
            if ($stmt->fetch()) {
                throw new Exception("Une alerte existe déjà pour ce créneau");
            }
            
            // Utiliser le service d'alertes pour créer l'alerte
            require_once 'backend/Services/AlerteDisponibiliteService.php';
            $alerteService = new AlerteDisponibiliteService();
            
            $success = $alerteService->createAlert($_SESSION['user_id'], $placeId, $dateDebut, $dateFin);
            
            if (!$success) {
                throw new Exception("Erreur lors de la création de l'alerte");
            }
            
            // Formater les dates pour le message de confirmation
            $debutFormatted = date('d/m/Y H:i', strtotime($dateDebut));
            $finFormatted = date('d/m/Y H:i', strtotime($dateFin));
            
            // Message personnalisé en fonction de la disponibilité
            if ($isUnavailable) {
                $message = "Alerte créée. Vous serez notifié lorsque la place n°{$place['numero']} sera disponible entre le $debutFormatted et le $finFormatted.";
            } else {
                $message = "Alerte créée. La place n°{$place['numero']} est actuellement disponible entre le $debutFormatted et le $finFormatted. Vous pouvez la réserver maintenant!";
            }
            
            echo json_encode(['success' => true, 'message' => $message]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Supprime une alerte de disponibilité
     */
    public function deleteAlert() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
            exit;
        }
        
        // Vérifier si c'est une requête AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!$isAjax || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
            exit;
        }
        
        try {
            $alerteId = filter_input(INPUT_POST, 'alerte_id', FILTER_VALIDATE_INT);
            
            if (!$alerteId) {
                throw new Exception("ID d'alerte invalide");
            }
            
            // Vérifier que l'alerte appartient à l'utilisateur
            $stmt = $this->db->prepare("
                SELECT a.*, p.numero 
                FROM alertes_disponibilite a
                JOIN parking_spaces p ON a.place_id = p.id
                WHERE a.id = ? AND a.user_id = ?
            ");
            $stmt->execute([$alerteId, $_SESSION['user_id']]);
            $alerte = $stmt->fetch();
            
            if (!$alerte) {
                throw new Exception("Alerte non trouvée ou non autorisée");
            }
            
            // Supprimer l'alerte
            $stmt = $this->db->prepare("DELETE FROM alertes_disponibilite WHERE id = ?");
            
            if (!$stmt->execute([$alerteId])) {
                throw new Exception("Erreur lors de la suppression de l'alerte");
            }
            
            // Ajouter une notification pour confirmer la suppression
            require_once 'backend/Services/NotificationService.php';
            $notificationService = new NotificationService();
            
            // Formater les dates pour la notification
            $debutFormatted = date('d/m/Y H:i', strtotime($alerte['date_debut']));
            $finFormatted = date('d/m/Y H:i', strtotime($alerte['date_fin']));
            
            $notificationService->createNotification(
                $_SESSION['user_id'],
                'Alerte de disponibilité supprimée',
                "Votre alerte pour la place n°{$alerte['numero']} du $debutFormatted au $finFormatted a été supprimée.",
                'system'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Met à jour le statut des places en fonction des réservations actives
     */
    private function updatePlacesStatus() {
        // Trouver les places avec des réservations actives actuellement (à l'instant présent)
        $stmt = $this->db->query("
            UPDATE parking_spaces ps
            SET ps.status = 'occupe'
            WHERE ps.status = 'libre'
            AND EXISTS (
                SELECT 1 
                FROM reservations r
                WHERE r.place_id = ps.id
                AND r.status = 'confirmée'
                AND r.date_debut <= NOW()
                AND r.date_fin > NOW()
            )
        ");
        
        // Trouver les places qui n'ont pas de réservations actives actuellement mais marquées comme occupées
        $stmt = $this->db->query("
            UPDATE parking_spaces ps
            SET ps.status = 'libre'
            WHERE ps.status = 'occupe'
            AND NOT EXISTS (
                SELECT 1 
                FROM reservations r
                WHERE r.place_id = ps.id
                AND r.status = 'confirmée'
                AND r.date_debut <= NOW()
                AND r.date_fin > NOW()
            )
        ");
    }
}
