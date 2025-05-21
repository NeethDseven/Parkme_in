<?php
/**
 * Service de génération de PDF simplifié sans dépendance externe
 */
class PDFService {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Génère une facture au format HTML à télécharger
     */
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

        if (!$data) {
            return false;
        }

        // Générer un numéro de facture
        $numero_facture = 'F' . date('Ymd') . str_pad($paiement_id, 4, '0', STR_PAD_LEFT);
        
        // Préparer le chemin pour sauvegarder le fichier
        $directory = ROOT_PATH . '/public/factures';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $filename = 'facture_' . $paiement_id . '_' . time() . '.html';
        $filepath = 'public/factures/' . $filename;
        $fullPath = $directory . '/' . $filename;
        
        // Générer le contenu HTML
        $html = $this->generateInvoiceHTML($data, $numero_facture);
        
        // Enregistrer le fichier HTML
        file_put_contents($fullPath, $html);
        
        // Enregistrer dans la base de données
        $stmt = $this->db->prepare("
            INSERT INTO factures (paiement_id, numero_facture, chemin_pdf)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$paiement_id, $numero_facture, $filepath]);
        
        return $filepath;
    }
    
    /**
     * Génère un reçu de réservation au format HTML
     */
    public function generateReservationReceipt($reservationData) {
        // Préparer le dossier de destination
        $directory = ROOT_PATH . '/public/receipts';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $filename = 'reservation_' . $reservationData['id'] . '_' . time() . '.html';
        $filepath = $directory . '/' . $filename;
        
        // Générer le contenu HTML
        $html = $this->generateReceiptHTML($reservationData);
        
        // Enregistrer le fichier HTML
        file_put_contents($filepath, $html);
        
        return $filepath;
    }
    
    /**
     * Génère un reçu de remboursement au format HTML
     */
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

        if (!$data) {
            return false;
        }
        
        // Préparer le dossier de destination
        $directory = ROOT_PATH . '/public/remboursements';
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        
        $filename = 'remboursement_' . $remboursement_id . '_' . time() . '.html';
        $filepath = $directory . '/' . $filename;
        
        // Générer le contenu HTML
        $html = $this->generateRefundHTML($data);
        
        // Enregistrer le fichier HTML
        file_put_contents($filepath, $html);
        
        return $filepath;
    }
    
    /**
     * Crée le contenu HTML pour une facture
     */
    private function generateInvoiceHTML($data, $numero_facture) {
        $dateFacture = date('d/m/Y');
        $dateDebut = date('d/m/Y H:i', strtotime($data['date_debut']));
        $dateFin = date('d/m/Y H:i', strtotime($data['date_fin']));
        $montant = number_format($data['montant'], 2, ',', ' ');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #{$numero_facture}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .invoice-header h1 {
            color: #3498db;
            margin: 0;
        }
        .company-details, .client-details {
            margin-bottom: 20px;
        }
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .invoice-items th, .invoice-items td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .invoice-items th {
            background-color: #f9f9f9;
        }
        .total {
            text-align: right;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>FACTURE</h1>
            <p>Numéro: {$numero_facture}</p>
            <p>Date: {$dateFacture}</p>
        </div>
        
        <div class="company-details">
            <h3>PARKME IN</h3>
            <p>123 Avenue des Places<br>75000 Paris<br>France</p>
            <p>Téléphone: 01 23 45 67 89<br>Email: contact@parkmein.com</p>
        </div>
        
        <div class="client-details">
            <h3>CLIENT</h3>
            <p><strong>{$data['nom']} {$data['prenom']}</strong><br>
            Email: {$data['email']}</p>
        </div>
        
        <table class="invoice-items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Dates</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Réservation place de parking N°{$data['place_numero']}</td>
                    <td>Du {$dateDebut} au {$dateFin}</td>
                    <td>{$montant} €</td>
                </tr>
            </tbody>
        </table>
        
        <div class="total">
            <p><strong>Total: {$montant} €</strong></p>
        </div>
        
        <div class="footer">
            <p>Merci pour votre confiance. Pour toute question relative à cette facture, veuillez contacter notre service client.</p>
            <p>PARKME IN - SIRET: 123 456 789 00012 - TVA: FR12345678900</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Crée le contenu HTML pour un reçu de réservation
     */
    private function generateReceiptHTML($data) {
        $dateRecu = date('d/m/Y');
        $dateDebut = date('d/m/Y H:i', strtotime($data['date_debut']));
        $dateFin = date('d/m/Y H:i', strtotime($data['date_fin']));
        $montant = number_format($data['montant'], 2, ',', ' ');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de réservation #{$data['id']}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .receipt-header h1 {
            color: #3498db;
            margin: 0;
        }
        .details {
            margin-bottom: 20px;
        }
        .details h3 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-item strong {
            display: block;
            margin-bottom: 5px;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #2ecc71;
            color: white;
        }
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code img {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>RÉCAPITULATIF DE RÉSERVATION</h1>
            <p>Réservation #{$data['id']}</p>
            <p>Date d'émission: {$dateRecu}</p>
        </div>
        
        <div class="details">
            <h3>DÉTAILS DE LA RÉSERVATION</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Place de parking</strong>
                    N°{$data['place_numero']} ({$data['place_type']})
                </div>
                <div class="detail-item">
                    <strong>Statut</strong>
                    <span class="badge badge-success">{$data['status']}</span>
                </div>
                <div class="detail-item">
                    <strong>Date de début</strong>
                    {$dateDebut}
                </div>
                <div class="detail-item">
                    <strong>Date de fin</strong>
                    {$dateFin}
                </div>
                <div class="detail-item">
                    <strong>Montant total</strong>
                    {$montant} €
                </div>
                <div class="detail-item">
                    <strong>Méthode de paiement</strong>
                    Carte bancaire
                </div>
            </div>
        </div>
        
        <div class="details">
            <h3>INFORMATIONS PRATIQUES</h3>
            <p>Pour accéder au parking, présentez ce reçu à la borne d'entrée. Vous pouvez imprimer ce document ou le présenter sur votre appareil mobile.</p>
            <p>En cas de problème, veuillez contacter notre service client au 01 23 45 67 89.</p>
            
            <div class="qr-code">
                <p><strong>Code d'accès</strong></p>
                <!-- À remplacer par un code QR généré dynamiquement si besoin -->
                <div style="width:150px; height:150px; background-color:#eee; margin:0 auto; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                    QR CODE
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Merci d'avoir choisi PARKME IN pour votre stationnement.</p>
            <p>Pour toute question, contactez-nous à support@parkmein.com</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Crée le contenu HTML pour un reçu de remboursement
     */
    private function generateRefundHTML($data) {
        $dateRemboursement = date('d/m/Y');
        $montant = number_format($data['montant'], 2, ',', ' ');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu de remboursement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .refund-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .refund-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .refund-header h1 {
            color: #e74c3c;
            margin: 0;
        }
        .details {
            margin-bottom: 20px;
        }
        .details h3 {
            color: #e74c3c;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="refund-container">
        <div class="refund-header">
            <h1>REÇU DE REMBOURSEMENT</h1>
            <p>Date: {$dateRemboursement}</p>
        </div>
        
        <div class="details">
            <h3>CLIENT</h3>
            <p><strong>{$data['nom']} {$data['prenom']}</strong><br>
            Email: {$data['email']}</p>
        </div>
        
        <div class="details">
            <h3>DÉTAILS DU REMBOURSEMENT</h3>
            <p><strong>Montant remboursé:</strong> {$montant} €</p>
            <p><strong>Raison:</strong> {$data['raison']}</p>
            <p><strong>Date de la demande:</strong> {$data['date_demande']}</p>
            <p><strong>Statut:</strong> {$data['status']}</p>
        </div>
        
        <div class="footer">
            <p>Ce reçu confirme que le remboursement a été traité avec succès. Pour toute question relative à ce remboursement, veuillez contacter notre service client.</p>
            <p>PARKME IN - Service Client: 01 23 45 67 89 - Email: remboursements@parkmein.com</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
