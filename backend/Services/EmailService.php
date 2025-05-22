<?php
/**
 * Service pour l'envoi d'emails
 */
class EmailService {
    private $from;
    private $replyTo;
    
    public function __construct() {
        $this->from = 'noreply@parkmein.com';
        $this->replyTo = 'support@parkmein.com';
    }
    
    /**
     * Envoie un email de confirmation après un paiement réussi
     * 
     * @param string $to Email du destinataire
     * @param array $paymentDetails Détails du paiement
     * @return bool Succès de l'envoi
     */
    public function sendPaymentConfirmation($to, $paymentDetails) {
        $subject = "Confirmation de paiement - Parkme In";
        
        // Formater les dates pour une meilleure lisibilité
        $dateDebut = date('d/m/Y H:i', strtotime($paymentDetails['date_debut']));
        $dateFin = date('d/m/Y H:i', strtotime($paymentDetails['date_fin']));
        
        $message = "
            <html>
            <head>
                <title>Confirmation de paiement</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Confirmation de paiement</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour,</p>
                        <p>Nous vous confirmons le paiement de votre réservation:</p>
                        <ul>
                            <li><strong>Place:</strong> N° {$paymentDetails['numero']}</li>
                            <li><strong>Début:</strong> $dateDebut</li>
                            <li><strong>Fin:</strong> $dateFin</li>
                            <li><strong>Montant:</strong> {$paymentDetails['montant']} €</li>
                        </ul>
                        <p>Votre réservation est maintenant confirmée. Vous recevrez un code d'accès par email le jour de votre arrivée.</p>
                        <p>
                            <a href='" . BASE_URL . "/?page=user&action=reservations' class='button'>Voir mes réservations</a>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " Parkme In - Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Envoie un email de rappel avant une réservation
     * 
     * @param string $to Email du destinataire
     * @param array $reservation Détails de la réservation
     * @return bool Succès de l'envoi
     */
    public function sendReminderEmail($to, $reservation) {
        $subject = "Rappel de réservation - Parkme In";
        
        // Formater les dates
        $dateDebut = date('d/m/Y H:i', strtotime($reservation['date_debut']));
        $dateFin = date('d/m/Y H:i', strtotime($reservation['date_fin']));
        
        $message = "
            <html>
            <head>
                <title>Rappel de réservation</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #2196F3; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #777; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Rappel de réservation</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour,</p>
                        <p>Nous vous rappelons votre réservation de parking prévue pour demain:</p>
                        <ul>
                            <li><strong>Place:</strong> N° {$reservation['place_numero']}</li>
                            <li><strong>Début:</strong> $dateDebut</li>
                            <li><strong>Fin:</strong> $dateFin</li>
                            <li><strong>Code d'accès:</strong> {$reservation['code_acces']}</li>
                        </ul>
                        <p>Nous vous souhaitons un bon stationnement chez Parkme In !</p>
                        <p>
                            <a href='" . BASE_URL . "/?page=user&action=reservations' class='button'>Voir mes réservations</a>
                        </p>
                    </div>
                    <div class='footer'>
                        <p>© " . date('Y') . " Parkme In - Ce message est généré automatiquement, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    /**
     * Envoie un email générique
     * 
     * @param string $to Email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $message Corps de l'email (HTML)
     * @param array $attachments Pièces jointes éventuelles
     * @return bool Succès de l'envoi
     */
    public function sendEmail($to, $subject, $message, $attachments = []) {
        // Définir les en-têtes
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->from,
            'Reply-To: ' . $this->replyTo,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // En environnement de développement, on peut simplement simuler l'envoi
        if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], 'dev') !== false) {
            // Journaliser l'email au lieu de l'envoyer
            error_log("Email to: $to, Subject: $subject");
            error_log("Message: $message");
            return true;
        }
        
        // En production, on enverrait réellement l'email
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
