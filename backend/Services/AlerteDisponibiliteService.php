<?php
require_once 'backend/Services/EmailService.php';
require_once 'backend/Services/NotificationService.php';
require_once 'backend/Services/LoggerService.php';

/**
 * Service pour gérer les alertes de disponibilité
 */
class AlerteDisponibiliteService {
    private $db;
    private $emailService;
    private $notificationService;
    private $logger;
    
    public function __construct() {
        $this->db = Database::connect();
        $this->emailService = new EmailService();
        $this->notificationService = new NotificationService();
        $this->logger = new LoggerService();
    }
    
    /**
     * Vérifie si des réservations ont été annulées et notifie les utilisateurs en attente
     * 
     * @param int $placeId ID de la place pour laquelle il faut vérifier les alertes
     * @param string $dateDebut Date de début du créneau libéré
     * @param string $dateFin Date de fin du créneau libéré
     * @return int Nombre d'alertes envoyées
     */
    public function checkAlertsForCancellation($placeId, $dateDebut, $dateFin) {
        // Journalisation pour débogage
        $this->logger->info("Vérification des alertes pour cancellation", [
            'placeId' => $placeId,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
        
        // Trouver toutes les alertes actives (en attente) pour cette place qui pourraient correspondre au créneau libéré
        $stmt = $this->db->prepare("
            SELECT a.*, p.numero, u.email, u.prenom, u.nom, u.id as user_id
            FROM alertes_disponibilite a
            JOIN parking_spaces p ON a.place_id = p.id
            JOIN users u ON a.user_id = u.id
            WHERE a.place_id = ?
            AND a.statut = 'en_attente'
            AND (
                -- L'alerte est entièrement contenue dans le créneau libéré
                (a.date_debut >= ? AND a.date_fin <= ?) OR
                -- Le début de l'alerte est dans le créneau
                (a.date_debut >= ? AND a.date_debut < ?) OR
                -- La fin de l'alerte est dans le créneau
                (a.date_fin > ? AND a.date_fin <= ?) OR
                -- L'alerte englobe entièrement le créneau
                (a.date_debut <= ? AND a.date_fin >= ?)
            )
        ");
        
        $stmt->execute([
            $placeId, 
            $dateDebut, $dateFin,  // Alerte entièrement dans le créneau
            $dateDebut, $dateFin,  // Début de l'alerte dans le créneau
            $dateDebut, $dateFin,  // Fin de l'alerte dans le créneau
            $dateDebut, $dateFin   // Alerte englobant le créneau
        ]);
        
        $alertes = $stmt->fetchAll();
        $alertesCount = count($alertes);
        
        $this->logger->info("Alertes trouvées: $alertesCount", [
            'placeId' => $placeId,
            'alertes' => json_encode($alertes)
        ]);
        
        if ($alertesCount > 0) {
            // Formater les dates pour l'affichage
            $debutFormatted = date('d/m/Y H:i', strtotime($dateDebut));
            $finFormatted = date('d/m/Y H:i', strtotime($dateFin));
            
            foreach ($alertes as $alerte) {
                // Marquer l'alerte comme notifiée
                $stmt = $this->db->prepare("
                    UPDATE alertes_disponibilite 
                    SET statut = 'notifiee' 
                    WHERE id = ?
                ");
                $stmt->execute([$alerte['id']]);
                
                // Créer une notification dans l'application
                $this->notificationService->createNotification(
                    $alerte['user_id'],
                    'Créneau disponible !',
                    "La place n°{$alerte['numero']} est maintenant disponible du $debutFormatted au $finFormatted suite à une annulation. Réservez-la rapidement !",
                    'system'
                );
                
                // Journal de notification
                $this->logger->info("Notification envoyée à l'utilisateur", [
                    'userId' => $alerte['user_id'],
                    'alerteId' => $alerte['id'],
                    'placeNumero' => $alerte['numero']
                ]);
                
                // Envoyer un email à l'utilisateur
                $sujet = "Alerte Parkme In - Place disponible!";
                $message = "
                    <h2>Bonne nouvelle !</h2>
                    <p>Bonjour {$alerte['prenom']},</p>
                    <p>La place n°{$alerte['numero']} que vous souhaitiez est maintenant disponible pour la période du <strong>$debutFormatted</strong> au <strong>$finFormatted</strong> suite à une annulation.</p>
                    <p>Réservez-la rapidement avant qu'un autre utilisateur ne le fasse !</p>
                    <p>
                        <a href='" . BASE_URL . "/?page=parking&action=view&id={$alerte['place_id']}' 
                           style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>
                            Réserver maintenant
                        </a>
                    </p>
                    <p>À bientôt sur Parkme In !</p>
                ";
                
                $this->emailService->sendEmail($alerte['email'], $sujet, $message);
            }
        }
        
        return $alertesCount;
    }
    
    /**
     * Vérifie si des créneaux sont devenus disponibles et notifie les utilisateurs en attente
     * Cette méthode peut être appelée par un cron job régulièrement
     */
    public function checkForAvailabilities() {
        // Trouver toutes les alertes en attente
        $alertes = $this->db->query("
            SELECT a.*, p.numero, u.email, u.prenom, u.nom
            FROM alertes_disponibilite a
            JOIN parking_spaces p ON a.place_id = p.id
            JOIN users u ON a.user_id = u.id
            WHERE a.statut = 'en_attente'
            AND a.date_debut > NOW()
        ")->fetchAll();
        
        foreach ($alertes as $alerte) {
            // Vérifier si le créneau est maintenant disponible (aucune réservation confirmée ne le chevauche)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM reservations 
                WHERE place_id = ? 
                AND status = 'confirmée'
                AND (
                    (date_debut <= ? AND date_fin > ?) OR
                    (date_debut < ? AND date_fin >= ?) OR
                    (date_debut >= ? AND date_fin <= ?)
                )
            ");
            $stmt->execute([
                $alerte['place_id'], 
                $alerte['date_debut'], $alerte['date_debut'],
                $alerte['date_fin'], $alerte['date_fin'],
                $alerte['date_debut'], $alerte['date_fin']
            ]);
            
            $reservationsCount = $stmt->fetchColumn();
            
            // Si aucune réservation pour ce créneau, la place est disponible
            if ($reservationsCount == 0) {
                // Marquer l'alerte comme notifiée
                $stmt = $this->db->prepare("
                    UPDATE alertes_disponibilite 
                    SET statut = 'notifiee' 
                    WHERE id = ?
                ");
                $stmt->execute([$alerte['id']]);
                
                // Formater les dates pour les notifications
                $debutFormatted = date('d/m/Y H:i', strtotime($alerte['date_debut']));
                $finFormatted = date('d/m/Y H:i', strtotime($alerte['date_fin']));
                
                // Créer une notification dans l'application
                $this->notificationService->createNotification(
                    $alerte['user_id'],
                    'Place disponible !',
                    "La place n°{$alerte['numero']} est maintenant disponible du $debutFormatted au $finFormatted. Réservez-la rapidement !",
                    'system'
                );
                
                // Envoyer un email à l'utilisateur
                $sujet = "Alerte Parkme In - Place disponible!";
                $message = "
                    <h2>Bonne nouvelle !</h2>
                    <p>Bonjour {$alerte['prenom']},</p>
                    <p>La place n°{$alerte['numero']} que vous souhaitiez est maintenant disponible pour la période du <strong>$debutFormatted</strong> au <strong>$finFormatted</strong>.</p>
                    <p>Réservez-la rapidement avant qu'un autre utilisateur ne le fasse !</p>
                    <p>
                        <a href='" . BASE_URL . "/?page=parking&action=view&id={$alerte['place_id']}' 
                           style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>
                            Réserver maintenant
                        </a>
                    </p>
                    <p>À bientôt sur Parkme In !</p>
                ";
                
                $this->emailService->sendEmail($alerte['email'], $sujet, $message);
            }
        }
        
        // Marquer les alertes expirées (date de début passée)
        $this->db->query("
            UPDATE alertes_disponibilite 
            SET statut = 'expiree'
            WHERE date_debut <= NOW()
            AND statut = 'en_attente'
        ");
        
        return true;
    }
    
    /**
     * Crée une alerte de disponibilité
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $placeId ID de la place
     * @param string $dateDebut Date de début souhaitée
     * @param string $dateFin Date de fin souhaitée
     * @return bool Succès de la création
     */
    public function createAlert($userId, $placeId, $dateDebut, $dateFin) {
        try {
            $this->logger->info("Création d'alerte de disponibilité", [
                'userId' => $userId,
                'placeId' => $placeId,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ]);
            
            // Vérifier s'il existe déjà une alerte similaire (tolérance de 30 minutes)
            $stmt = $this->db->prepare("
                SELECT id FROM alertes_disponibilite 
                WHERE user_id = ? AND place_id = ? 
                AND ABS(TIMESTAMPDIFF(MINUTE, date_debut, ?)) < 30
                AND ABS(TIMESTAMPDIFF(MINUTE, date_fin, ?)) < 30
                AND statut = 'en_attente'
            ");
            $stmt->execute([$userId, $placeId, $dateDebut, $dateFin]);
            
            if ($stmt->fetch()) {
                // Si une alerte similaire existe déjà, ne pas en créer une nouvelle
                $this->logger->info("Alerte similaire déjà existante, ignorée");
                return true;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO alertes_disponibilite (user_id, place_id, date_debut, date_fin, statut)
                VALUES (?, ?, ?, ?, 'en_attente')
            ");
            
            $success = $stmt->execute([$userId, $placeId, $dateDebut, $dateFin]);
            
            if ($success) {
                $alerteId = $this->db->lastInsertId();
                
                // Récupérer les infos de l'utilisateur et de la place pour les logs
                $stmt = $this->db->prepare("
                    SELECT u.prenom, u.nom, p.numero
                    FROM users u
                    JOIN parking_spaces p ON p.id = ?
                    WHERE u.id = ?
                ");
                $stmt->execute([$placeId, $userId]);
                $info = $stmt->fetch();
                
                if ($info) {
                    $this->logger->info("Alerte créée avec succès", [
                        'alerteId' => $alerteId,
                        'user' => $info['prenom'] . ' ' . $info['nom'],
                        'place' => $info['numero'],
                        'debut' => date('d/m/Y H:i', strtotime($dateDebut)),
                        'fin' => date('d/m/Y H:i', strtotime($dateFin))
                    ]);
                }
                
                // Tester immédiatement si le créneau est disponible
                $this->checkImmediateAvailability($alerteId);
            }
            
            return $success;
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de la création d'alerte: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier immédiatement si un créneau est disponible pour une alerte nouvellement créée
     * 
     * @param int $alerteId ID de l'alerte
     * @return bool Disponibilité trouvée
     */
    private function checkImmediateAvailability($alerteId) {
        try {
            // Récupérer les détails de l'alerte
            $stmt = $this->db->prepare("
                SELECT a.*, p.numero, u.email, u.prenom, u.nom, u.id as user_id
                FROM alertes_disponibilite a
                JOIN parking_spaces p ON a.place_id = p.id
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?
            ");
            $stmt->execute([$alerteId]);
            $alerte = $stmt->fetch();
            
            if (!$alerte) return false;
            
            // Vérifier s'il existe des réservations qui chevauchent le créneau demandé
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM reservations 
                WHERE place_id = ? 
                AND status = 'confirmée'
                AND (
                    (date_debut <= ? AND date_fin > ?) OR
                    (date_debut < ? AND date_fin >= ?) OR
                    (date_debut >= ? AND date_fin <= ?)
                )
            ");
            $stmt->execute([
                $alerte['place_id'],
                $alerte['date_debut'], $alerte['date_debut'],
                $alerte['date_fin'], $alerte['date_fin'],
                $alerte['date_debut'], $alerte['date_fin']
            ]);
            
            $hasConflict = $stmt->fetchColumn() > 0;
            
            // Si aucun conflit n'est trouvé, le créneau est disponible immédiatement!
            if (!$hasConflict) {
                // Formater les dates pour les notifications
                $debutFormatted = date('d/m/Y H:i', strtotime($alerte['date_debut']));
                $finFormatted = date('d/m/Y H:i', strtotime($alerte['date_fin']));
                
                // Marquer l'alerte comme notifiée
                $stmt = $this->db->prepare("UPDATE alertes_disponibilite SET statut = 'notifiee' WHERE id = ?");
                $stmt->execute([$alerteId]);
                
                // Créer une notification
                $this->notificationService->createNotification(
                    $alerte['user_id'],
                    'Bonne nouvelle ! Place déjà disponible',
                    "La place n°{$alerte['numero']} est actuellement disponible du $debutFormatted au $finFormatted. Vous pouvez la réserver dès maintenant !",
                    'system'
                );
                
                // Envoyer un email
                $sujet = "Place disponible - Réservez maintenant !";
                $message = "
                    <h2>Place disponible immédiatement !</h2>
                    <p>Bonjour {$alerte['prenom']},</p>
                    <p>Bonne nouvelle ! La place n°{$alerte['numero']} que vous recherchiez est actuellement disponible pour la période du <strong>$debutFormatted</strong> au <strong>$finFormatted</strong>.</p>
                    <p>Aucune réservation n'existe pour ce créneau, vous pouvez la réserver immédiatement !</p>
                    <p>
                        <a href='" . BASE_URL . "/?page=parking&action=view&id={$alerte['place_id']}' 
                           style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>
                            Réserver maintenant
                        </a>
                    </p>
                    <p>À bientôt sur Parkme In !</p>
                ";
                
                $this->emailService->sendEmail($alerte['email'], $sujet, $message);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de la vérification immédiate: " . $e->getMessage());
            return false;
        }
    }
}
