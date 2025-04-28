<?php

class Payment extends BaseModel {
    protected static $table = 'paiements';
    protected static $fillable = [
        'reservation_id', 'montant', 'methode', 'date_paiement', 'reference', 'statut'
    ];
    
    // Generate payment reference
    public static function generateReference() {
        return 'PMI-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    }
    
    // Mark payment as paid
    public function markAsPaid() {
        $this->statut = 'payé';
        return $this->save();
    }
    
    // Mark payment as failed
    public function markAsFailed() {
        $this->statut = 'échoué';
        return $this->save();
    }
    
    // Get reservation
    public function getReservation() {
        return Reservation::findById($this->reservation_id);
    }
    
    // Process a new payment
    public static function process($reservationId, $amount, $method) {
        $payment = new self();
        $payment->reservation_id = $reservationId;
        $payment->montant = $amount;
        $payment->methode = $method;
        $payment->date_paiement = date('Y-m-d H:i:s');
        $payment->reference = self::generateReference();
        $payment->statut = 'payé';
        
        if ($payment->save()) {
            $reservation = Reservation::findById($reservationId);
            if ($reservation) {
                $reservation->confirm();
            }
            return $payment;
        }
        return false;
    }
}
