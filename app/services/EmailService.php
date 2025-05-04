<?php

class EmailService {
    /**
     * Envoyer un email
     *
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $message Contenu de l'email (peut être HTML)
     * @param array $headers En-têtes additionnels
     * @return bool Succès ou échec de l'envoi
     */
    public static function send($to, $subject, $message, $headers = []) {
        // Définir les en-têtes par défaut
        $defaultHeaders = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=utf-8',
            'From' => 'noreply@parkme-in.com',
            'Reply-To' => 'support@parkme-in.com'
        ];
        
        // Fusionner avec les en-têtes personnalisés
        $headers = array_merge($defaultHeaders, $headers);
        
        // Formatter les en-têtes pour mail()
        $headerStr = '';
        foreach ($headers as $name => $value) {
            $headerStr .= "$name: $value\r\n";
        }
        
        // Journaliser la tentative d'envoi (pour débogage en développement)
        error_log("Tentative d'envoi d'email à $to avec le sujet: $subject");
        
        // En production, décommenter la ligne ci-dessous pour envoyer réellement l'email
        // return mail($to, $subject, $message, $headerStr);
        
        // Pour le développement, simuler l'envoi d'email et toujours retourner true
        return true;
    }
    
    /**
     * Envoyer un email de confirmation de réservation
     *
     * @param string $email Email de l'utilisateur
     * @param string $name Nom de l'utilisateur
     * @param array $reservation Détails de la réservation
     * @return bool Succès ou échec de l'envoi
     */
    public static function sendReservationConfirmation($email, $name, $reservation) {
        $subject = "Confirmation de votre réservation #" . $reservation->id;
        
        // Préparer le message avec un template HTML
        $message = "
        <html>
        <head>
            <title>Confirmation de réservation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 0.8em; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
                .code { font-family: monospace; font-size: 1.2em; background-color: #f9f9f9; padding: 5px; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmation de Réservation</h2>
                </div>
                <div class='content'>
                    <p>Bonjour $name,</p>
                    <p>Votre réservation a été confirmée avec succès. Voici les détails :</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Numéro de réservation</th>
                                <td>" . $reservation->id . "</td>
                            </tr>
                            <tr>
                                <th>Date de début</th>
                                <td>" . $reservation->date_debut . "</td>
                            </tr>
                            <tr>
                                <th>Date de fin</th>
                                <td>" . $reservation->date_fin . "</td>
                            </tr>
                            <tr>
                                <th>Prix total</th>
                                <td>" . $reservation->prix . " €</td>
                            </tr>
                            <tr>
                                <th>Code d'accès</th>
                                <td><span class='code'>" . $reservation->code_acces . "</span></td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Veuillez présenter ce code lors de votre arrivée au parking.</p>
                    <p>Merci d'avoir choisi notre service!</p>
                </div>
                <div class='footer'>
                    <p>Ceci est un email automatique. Merci de ne pas y répondre.</p>
                    <p>© " . date('Y') . " ParkMeIn - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email de rappel avant réservation
     *
     * @param string $email Email de l'utilisateur
     * @param string $name Nom de l'utilisateur
     * @param array $reservation Détails de la réservation
     * @return bool Succès ou échec de l'envoi
     */
    public static function sendReservationReminder($email, $name, $reservation) {
        $subject = "Rappel - Votre réservation débute bientôt";
        
        $message = "
        <html>
        <head>
            <title>Rappel de réservation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2196F3; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 0.8em; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
                .code { font-family: monospace; font-size: 1.2em; background-color: #f9f9f9; padding: 5px; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Rappel de Réservation</h2>
                </div>
                <div class='content'>
                    <p>Bonjour $name,</p>
                    <p>Votre réservation débute bientôt! Voici un rappel des détails :</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Numéro de réservation</th>
                                <td>" . $reservation->id . "</td>
                            </tr>
                            <tr>
                                <th>Date de début</th>
                                <td>" . $reservation->date_debut . "</td>
                            </tr>
                            <tr>
                                <th>Date de fin</th>
                                <td>" . $reservation->date_fin . "</td>
                            </tr>
                            <tr>
                                <th>Code d'accès</th>
                                <td><span class='code'>" . $reservation->code_acces . "</span></td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>N'oubliez pas de présenter ce code lors de votre arrivée au parking.</p>
                </div>
                <div class='footer'>
                    <p>Ceci est un email automatique. Merci de ne pas y répondre.</p>
                    <p>© " . date('Y') . " ParkMeIn - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email d'annulation de réservation
     *
     * @param string $email Email de l'utilisateur
     * @param string $name Nom de l'utilisateur
     * @param array $reservation Détails de la réservation
     * @return bool Succès ou échec de l'envoi
     */
    public static function sendCancellationConfirmation($email, $name, $reservation) {
        $subject = "Confirmation d'annulation de réservation #" . $reservation->id;
        
        $message = "
        <html>
        <head>
            <title>Annulation de réservation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f44336; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; }
                .footer { background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 0.8em; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmation d'Annulation</h2>
                </div>
                <div class='content'>
                    <p>Bonjour $name,</p>
                    <p>Votre réservation a été annulée avec succès. Voici les détails de la réservation annulée :</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Numéro de réservation</th>
                                <td>" . $reservation->id . "</td>
                            </tr>
                            <tr>
                                <th>Date de début</th>
                                <td>" . $reservation->date_debut . "</td>
                            </tr>
                            <tr>
                                <th>Date de fin</th>
                                <td>" . $reservation->date_fin . "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>Si vous avez des questions concernant cette annulation, n'hésitez pas à contacter notre service client.</p>
                </div>
                <div class='footer'>
                    <p>Ceci est un email automatique. Merci de ne pas y répondre.</p>
                    <p>© " . date('Y') . " ParkMeIn - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
}
