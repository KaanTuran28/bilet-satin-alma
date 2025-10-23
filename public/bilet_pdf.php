<?php
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../src/tfpdf/tfpdf.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Erişim reddedildi.");
}

$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['ticket_id'] ?? null;

if (!$ticket_id || !is_numeric($ticket_id)) {
    http_response_code(400);
    die("Geçersiz bilet ID'si.");
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id as ticket_id, t.seat_number, t.purchase_time, t.price_paid,
            tr.departure_location, tr.arrival_location, tr.departure_time,
            c.name as company_name,
            u.fullname as passenger_name
        FROM tickets t
        JOIN trips tr ON t.trip_id = tr.id
        JOIN companies c ON tr.company_id = c.id
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ? AND t.user_id = ?
    ");
    $stmt->execute([$ticket_id, $user_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        http_response_code(404);
        die("Bilet bulunamadı veya bu bilete erişim yetkiniz yok.");
    }
} catch (PDOException $e) {
    error_log("PDF Bilet Hatası: " . $e->getMessage());
    
    http_response_code(500);
    die("Bilet bilgileri getirilirken bir sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.");
}

$ticket['price'] = $ticket['price_paid'];


class TicketPDF extends tFPDF
{
    function Header()
    {
        $this->SetFont('DejaVu','B',16);
        $this->Cell(0, 10, 'SEYAHAT BİLETİ', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('DejaVu','I',8);
        $this->Cell(0, 10, 'İyi yolculuklar dileriz!', 0, 0, 'C');
    }

    function InfoRow($label, $value, $is_bold = false)
    {
        $this->SetFont('DejaVu','B',12);
        $this->Cell(50, 10, $label, 0);
        $font_style = $is_bold ? 'B' : '';
        $font_size = $is_bold ? 16 : 12;
        $this->SetFont('DejaVu', $font_style, $font_size);
        $this->Cell(0, 10, $value, 0, 1);
    }
}

$pdf = new TicketPDF();

$pdf->AddFont('DejaVu','','DejaVuSans.ttf',true);
$pdf->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
$pdf->AddFont('DejaVu','I','DejaVuSans-Oblique.ttf',true);

$pdf->AddPage();

$pdf->InfoRow('Firma:', $ticket['company_name']);
$pdf->InfoRow('Yolcu Adı Soyadı:', $ticket['passenger_name']);
$pdf->Ln(5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);
$pdf->InfoRow('Güzergah:', $ticket['departure_location'] . ' -> ' . $ticket['arrival_location']);
$pdf->InfoRow('Kalkış Zamanı:', date('d.m.Y H:i', strtotime($ticket['departure_time'])));
$pdf->InfoRow('Koltuk No:', $ticket['seat_number'], true);
$pdf->Ln(5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);
$pdf->InfoRow('Ödenen Tutar:', number_format($ticket['price'], 2, ',', '.') . ' TL');
$pdf->InfoRow('Satın Alma Tarihi:', date('d.m.Y H:i', strtotime($ticket['purchase_time'])));

$pdf->Output('I', 'bilet_'. $ticket['ticket_id'] . '.pdf');
?>