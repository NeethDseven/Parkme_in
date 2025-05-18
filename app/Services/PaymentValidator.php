<?php
class PaymentValidator {
    public function validateCard($number, $expiry, $cvv) {
        if (!preg_match('/^\d{16}$/', $number)) {
            return 'Numéro de carte invalide';
        }
        
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
            return 'Date d\'expiration invalide';
        }
        
        if (!preg_match('/^\d{3}$/', $cvv)) {
            return 'CVV invalide';
        }
        
        return null; // Pas d'erreur
    }
}
