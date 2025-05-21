<?php
class EmailService {
    public function sendPaymentConfirmation($email, $reservation) {
        $to = $email;
        $subject = 'Confirmation de paiement - Parking';
        
        $message = "Bonjour,\n\n";
        $message .= "Votre paiement pour la réservation suivante a été confirmé :\n";
        $message .= "Place n° : " . $reservation['numero'] . "\n";
        $message .= "Date début : " . $reservation['date_debut'] . "\n";
        $message .= "Date fin : " . $reservation['date_fin'] . "\n";
        $message .= "Montant : " . $reservation['montant'] . " €\n\n";
        $message .= "Merci de votre confiance.";
        
        $headers = 'From: parking@example.com';
        
        return mail($to, $subject, $message, $headers);
    }

    public function sendRefundRequestConfirmation($email, $refund) {
        $subject = 'Demande de remboursement reçue';
        $message = "Votre demande de remboursement de {$refund['montant']}€ a été reçue.\n";
        $message .= "Nous la traiterons dans les plus brefs délais.";
        
        return mail($email, $subject, $message);
    }

    public function sendRefundStatusUpdate($email, $refund) {
        $subject = 'Mise à jour de votre demande de remboursement';
        $message = "Votre demande de remboursement a été {$refund['status']}.\n";
        if ($refund['status'] === 'effectue') {
            $message .= "Le montant de {$refund['montant']}€ sera crédité sous 5 jours ouvrés.";
        }
        
        return mail($email, $subject, $message);
    }

    public function sendReservationReminder($email, $nom, $placeNumero, $dateDebut) {
        $subject = 'Rappel de réservation';
        
        // Formater la date pour l'affichage
        $dateFormatee = date('d/m/Y à H:i', strtotime($dateDebut));
        
        $message = "Bonjour " . $nom . ",\n\n";
        $message .= "Ceci est un rappel pour votre réservation de la place n°" . $placeNumero . ".\n";
        $message .= "Votre réservation commence le " . $dateFormatee . ".\n\n";
        $message .= "N'oubliez pas d'apporter votre confirmation de réservation.\n\n";
        $message .= "Cordialement,\nL'équipe Parking App";
        
        return mail($email, $subject, $message);
    }
}
