<?php
require_once 'backend/Services/PaginationService.php';

class ParkingController {
    private $db;
    private $itemsPerPage = 9; // Nombre de places par page

    public function __construct() {
        $this->db = Database::connect();
    }

    public function listAvailable() {
        // Mise à jour des places avec des réservations actives
        $this->updatePlacesStatus();
        
        // Récupérer le filtre de type
        $typeFilter = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        $currentPage = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT) ?: 1;
        
        // Construire la requête SQL de base
        $countSql = "SELECT COUNT(*) FROM parking_spaces WHERE status = 'libre'";
        $sql = "SELECT * FROM parking_spaces WHERE status = 'libre'";
        $params = [];
        
        // Ajouter le filtre par type si spécifié
        if ($typeFilter) {
            $countSql .= " AND type = ?";
            $sql .= " AND type = ?";
            $params[] = $typeFilter;
        }
        
        // Compter le nombre total de places disponibles
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetchColumn();
        
        // Initialiser la pagination
        $pagination = new PaginationService($totalItems, $this->itemsPerPage, $currentPage);
        
        // Compléter la requête avec l'ordre et la pagination
        $sql .= " ORDER BY numero LIMIT ?, ?";
        $params[] = $pagination->getOffset();
        $params[] = $pagination->getLimit();
        
        // Exécuter la requête paginée
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $places = $stmt->fetchAll();

        // Récupérer les types de places pour le filtrage
        $stmt = $this->db->query("
            SELECT DISTINCT type 
            FROM parking_spaces
        ");
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

            // Récupération des informations de la place
            $stmt = $this->db->prepare("
                SELECT p.*, t.prix_heure, t.prix_journee
                FROM parking_spaces p
                JOIN tarifs t ON p.type = t.type_place
                WHERE p.id = ? AND p.status = 'libre'
            ");
            $stmt->execute([$id]);
            $place = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$place) {
                throw new Exception("Place non disponible");
            }

            // Traitement du formulaire de réservation
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dateDebut = $_POST['date_debut'];
                $dateFin = $_POST['date_fin'];
                
                if (empty($dateDebut) || empty($dateFin)) {
                    throw new Exception("Les dates sont obligatoires");
                }
                
                if (strtotime($dateFin) <= strtotime($dateDebut)) {
                    throw new Exception("La date de fin doit être après la date de début");
                }

                // Vérification des chevauchements
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM reservations 
                    WHERE place_id = ? 
                    AND status != 'annulée'
                    AND (
                        (date_debut BETWEEN ? AND ?) 
                        OR (date_fin BETWEEN ? AND ?)
                    )
                ");
                $stmt->execute([$id, $dateDebut, $dateFin, $dateDebut, $dateFin]);
                $overlapCount = $stmt->fetchColumn();
                
                if ($overlapCount > 0) {
                    throw new Exception("La place est déjà réservée pour les dates sélectionnées");
                }
                
                // Début de transaction
                $this->db->beginTransaction();
                
                try {
                    // Créer la réservation
                    $stmt = $this->db->prepare("
                        INSERT INTO reservations (user_id, place_id, date_debut, date_fin, status)
                        VALUES (?, ?, ?, ?, 'confirmée')
                    ");
                    
                    if (!$stmt->execute([$_SESSION['user_id'], $id, $dateDebut, $dateFin])) {
                        throw new Exception("Erreur lors de la création de la réservation");
                    }
                    
                    $reservation_id = $this->db->lastInsertId();
                    
                    // Mettre à jour le statut de la place
                    $stmt = $this->db->prepare("UPDATE parking_spaces SET status = 'occupe' WHERE id = ?");
                    if (!$stmt->execute([$id])) {
                        throw new Exception("Erreur lors de la mise à jour du statut de la place");
                    }
                    
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
                    
                    // Ajouter une notification
                    require_once 'backend/Services/NotificationService.php';
                    $notificationService = new NotificationService();
                    $notificationService->createNotification(
                        $_SESSION['user_id'],
                        'Réservation confirmée',
                        "Votre réservation de la place n°{$place['numero']} du " . 
                        date('d/m/Y H:i', strtotime($dateDebut)) . " au " . 
                        date('d/m/Y H:i', strtotime($dateFin)) . " a été confirmée.",
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

    private function calculerPrix($heures, $place) {
        if ($heures <= 24) {
            return $heures * $place['prix_heure'];
        } elseif ($heures <= 720) { // 30 jours
            $jours = ceil($heures / 24);
            return $jours * $place['prix_journee'];
        } else {
            $mois = ceil($heures / 720);
            return $mois * $place['prix_mois'];
        }
    }

    /**
     * Met à jour le statut des places en fonction des réservations actives
     */
    private function updatePlacesStatus() {
        // Trouver les places avec des réservations actives mais marquées comme libres
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
        
        // Trouver les places sans réservations actives mais marquées comme occupées
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
