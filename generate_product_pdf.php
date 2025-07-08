<?php
include "config.php";
require('tcpdf/tcpdf.php');

// Start output buffering to avoid unwanted output
ob_start();

// Fetch data from database based on ProductID
$productID = intval($_GET['ProductID']); // Ensure integer type for security
$Psq = "SELECT * FROM products WHERE ProductID = {$productID}";
$Presult = mysqli_query($conn, $Psq);
$Prow = mysqli_fetch_assoc($Presult);

// Create a new PDF document
$pdf = new TCPDF();
$pdf->AddPage();

// Set document title and font
$pdf->SetTitle('Product Details');
$pdf->SetFont('helvetica', '', 12);

// Styling and layout
$html = '
    <style>
        h1 {
            font-size: 22px;
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        .summary {
            margin-top: 20px;
            font-size: 14px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .summary p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
    </style>

    <h1>Product Details</h1>
    <h2>Product Name: ' . htmlspecialchars($Prow['ProductName']) . '</h2>
    <div class="summary">
        <p><strong>Product ID:</strong> ' . htmlspecialchars($Prow['ProductID']) . '</p>
        <p><strong>Size:</strong> ' . htmlspecialchars($Prow['ProductSize']) . '</p>
        <p><strong>Color:</strong> ' . htmlspecialchars($Prow['ProductColor']) . '</p>
        <p><strong>Weight:</strong> ' . htmlspecialchars($Prow['ProductWeight']) . '</p>
    </div>
';

// Output the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Get the height of the content so we can position the image correctly
$lastY = $pdf->getY();
$pageHeight = $pdf->getPageHeight();

// Calculate available space
$remainingSpace = $pageHeight - $lastY - 20; // Adjust 20 for margin

$imagePath = __DIR__ . '/uploads/' . $Prow['ProductImage'];
if (file_exists($imagePath) && $remainingSpace > 0) {
    // Set position for the image at the bottom of the same page
    $pdf->SetY($lastY + 10); // Adjust 10 for padding above the image
    $pdf->Image($imagePath, 10, $pdf->GetY(), 50, 0, '', '', '', true, 300, '', false, false, 1, false, false, false);
} else {
    // Handle image not found scenario or if there's no space
    $pdf->SetY($lastY + 10); // Position for message
    $pdf->Cell(0, 10, 'Image not available or no space for image. Check file path and permissions.', 0, 1, 'C');
}

// Clean output buffer and prevent additional output
ob_end_clean();

// Output the PDF
$pdf->Output('product_details.pdf', 'I');
