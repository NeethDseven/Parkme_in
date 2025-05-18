<?php
require_once 'vendor/autoload.php';

use TCPDF;

class PDFService {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function generateInvoice($paiement_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, r.date_debut, r.date_fin, u.nom, u.prenom, u.email,
                   ps.numero as place_numero
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN parking_spaces ps ON r.place_id = ps.id
            WHERE p.id = ?
        ");
        $stmt->execute([$paiement_id]);
        $data = $stmt->fetch();

        // Création du PDF avec TCPDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        
        // En-tête
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'FACTURE', 0, 1, 'C');
        
        // Détails du client
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Client: ' . $data['nom'] . ' ' . $data['prenom'], 0, 1);
        $pdf->Cell(0, 10, 'Email: ' . $data['email'], 0, 1);
        
        // Détails de la réservation
        $pdf->Cell(0, 10, 'Place N°: ' . $data['place_numero'], 0, 1);
        $pdf->Cell(0, 10, 'Période: du ' . $data['date_debut'] . ' au ' . $data['date_fin'], 0, 1);
        $pdf->Cell(0, 10, 'Montant: ' . $data['montant'] . ' €', 0, 1);

        // Génération du nom de fichier
        $filename = 'facture_' . time() . '.pdf';
        $filepath = 'public/factures/' . $filename;
        
        // Sauvegarde du PDF
        $pdf->Output(ROOT_PATH . '/' . $filepath, 'F');
        
        // Enregistrement dans la base de données
        $stmt = $this->db->prepare("
            INSERT INTO factures (paiement_id, numero_facture, chemin_pdf)
            VALUES (?, ?, ?)
        ");
        $numero_facture = 'F' . date('Ymd') . str_pad($paiement_id, 4, '0', STR_PAD_LEFT);
        $stmt->execute([$paiement_id, $numero_facture, $filepath]);
        
        return $filepath;
    }

    public function generateRefundReceipt($remboursement_id) {
        $stmt = $this->db->prepare("
            SELECT r.*, p.montant as montant_initial, u.nom, u.prenom, u.email
            FROM remboursements r
            JOIN paiements p ON r.paiement_id = p.id
            JOIN reservations res ON p.reservation_id = res.id
            JOIN users u ON res.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$remboursement_id]);
        $data = $stmt->fetch();

        $pdf = new TCPDF();
        $pdf->AddPage();
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'REÇU DE REMBOURSEMENT', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Client: ' . $data['nom'] . ' ' . $data['prenom'], 0, 1);
        $pdf->Cell(0, 10, 'Montant remboursé: ' . $data['montant'] . ' €', 0, 1);
        $pdf->Cell(0, 10, 'Date: ' . date('d/m/Y'), 0, 1);

        $filename = 'remboursement_' . time() . '.pdf';
        $filepath = 'public/remboursements/' . $filename;
        
        $pdf->Output(ROOT_PATH . '/' . $filepath, 'F');
        return $filepath;
    }

    public function generateReservationReceipt($reservationData) {
        $pdf = new TCPDF();
        $pdf->AddPage();
        
        // En-tête
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'RÉCAPITULATIF DE RÉSERVATION', 0, 1, 'C');
        
        // Détails de la réservation
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Réservation #' . $reservationData['id'], 0, 1);
        $pdf->Cell(0, 10, 'Place n° ' . $reservationData['place_numero'] . ' (' . $reservationData['place_type'] . ')', 0, 1);
        $pdf->Cell(0, 10, 'Début : ' . date('d/m/Y H:i', strtotime($reservationData['date_debut'])), 0, 1);
        $pdf->Cell(0, 10, 'Fin : ' . date('d/m/Y H:i', strtotime($reservationData['date_fin'])), 0, 1);
        $pdf->Cell(0, 10, 'Montant : ' . number_format($reservationData['montant'], 2) . ' €', 0, 1);
        $pdf->Cell(0, 10, 'Statut : ' . $reservationData['status'], 0, 1);
        
        // Informations pratiques
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Informations pratiques', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Veuillez présenter ce document à l\'entrée du parking.', 0, 1);
        $pdf->Cell(0, 10, 'En cas de problème, contactez-nous au 01 23 45 67 89.', 0, 1);
        
        // Génération du fichier
        $directory = ROOT_PATH . '/public/receipts';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $filename = 'reservation_' . $reservationData['id'] . '_' . time() . '.pdf';
        $filepath = $directory . '/' . $filename;
        
        $pdf->Output($filepath, 'F');
        return $filepath;
    }
}
