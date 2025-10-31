<?php
/**
 * Cabinet360 - Generate PDF Receipt
 * Simple HTML to PDF conversion without external libraries
 */

require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    die('Non autorisé');
}

$payment_id = $_GET['id'] ?? 0;

try {
    $stmt = $conn->prepare("SELECT p.*, c.full_name as client_name, c.cin, c.address, cs.case_number 
                            FROM payments p 
                            JOIN clients c ON p.client_id = c.id 
                            LEFT JOIN cases cs ON p.case_id = cs.id 
                            WHERE p.id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        die('Paiement non trouvé');
    }
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

// Set headers for PDF download
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement #<?php echo $payment['id']; ?></title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #D4AF37;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32pt;
            font-weight: bold;
            color: #D4AF37;
            margin-bottom: 10px;
        }
        .company-info {
            font-size: 10pt;
            color: #666;
        }
        .receipt-title {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            margin: 30px 0;
            color: #D4AF37;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        .info-value {
            width: 60%;
            text-align: right;
        }
        .amount-box {
            background: #f5f5f5;
            border: 2px solid #D4AF37;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        .amount-label {
            font-size: 14pt;
            color: #666;
        }
        .amount-value {
            font-size: 24pt;
            font-weight: bold;
            color: #D4AF37;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            border-top: 2px solid #D4AF37;
            padding-top: 20px;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 10px;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14pt; cursor: pointer; background: #D4AF37; color: #000; border: none; border-radius: 5px;">
            🖨️ Imprimer / Sauvegarder en PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 14pt; cursor: pointer; background: #666; color: #fff; border: none; border-radius: 5px; margin-left: 10px;">
            ✖ Fermer
        </button>
    </div>

    <div class="header">
        <div class="logo">⚖️ Cabinet360</div>
        <div class="company-info">
            Cabinet d'Avocat<br>
            Système de Gestion Juridique<br>
            Maroc
        </div>
    </div>

    <div class="receipt-title">
        REÇU DE PAIEMENT
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">N° de Reçu:</span>
            <span class="info-value">#<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date d'émission:</span>
            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($payment['created_at'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Date de paiement:</span>
            <span class="info-value"><?php echo date('d/m/Y', strtotime($payment['date'])); ?></span>
        </div>
    </div>

    <div class="info-section">
        <h3 style="color: #D4AF37; border-bottom: 1px solid #D4AF37; padding-bottom: 5px;">Informations Client</h3>
        <div class="info-row">
            <span class="info-label">Nom complet:</span>
            <span class="info-value"><?php echo htmlspecialchars($payment['client_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">CIN:</span>
            <span class="info-value"><?php echo htmlspecialchars($payment['cin']); ?></span>
        </div>
        <?php if ($payment['case_number']): ?>
        <div class="info-row">
            <span class="info-label">N° Dossier:</span>
            <span class="info-value"><?php echo htmlspecialchars($payment['case_number']); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="amount-box">
        <div class="amount-label">Montant Payé</div>
        <div class="amount-value"><?php echo number_format($payment['amount'], 2, ',', ' '); ?> MAD</div>
    </div>

    <div class="info-section">
        <h3 style="color: #D4AF37; border-bottom: 1px solid #D4AF37; padding-bottom: 5px;">Détails du Paiement</h3>
        <div class="info-row">
            <span class="info-label">Méthode de paiement:</span>
            <span class="info-value"><?php echo ucfirst($payment['method']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut:</span>
            <span class="info-value"><?php echo ucfirst($payment['status']); ?></span>
        </div>
        <?php if ($payment['notes']): ?>
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value"><?php echo htmlspecialchars($payment['notes']); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                Signature du Client
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Cachet et Signature du Cabinet
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Ce reçu a été généré automatiquement par Cabinet360</p>
        <p>Merci de votre confiance</p>
        <p style="margin-top: 20px; font-size: 9pt;">
            © <?php echo date('Y'); ?> Cabinet360 - Tous droits réservés
        </p>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>

