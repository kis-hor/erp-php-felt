<?php
include "config.php";
require('tcpdf/tcpdf.php');

// Fetch data from database based on order_id
$ArtisanID = $_GET['ArtisanID'];
$Asql = "SELECT * FROM artisans INNER JOIN departments Using(DepartmentID) WHERE ArtisanID = {$ArtisanID}";
$Aresult = mysqli_query($conn, $Asql);
$Arow = mysqli_fetch_assoc($Aresult);


// Create a new PDF document
$pdf = new TCPDF();
$pdf->AddPage();

// Set document title and font
$pdf->SetTitle('Order Summary');
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
        .wages {
            font-size: 16px;
            color: #d9534f;
            font-weight: bold;
        }
    </style>

    <h1>Order Summary</h1>
    <h2>Artisan Name : ' . $Arow['ArtisanName'] . ' Deparment Name : ' . $Arow['DepartmentName'] . '</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Approved Quantity</th>
            <th>Production Due Date</th>
            <th>Wages Per Piece</th>
            <th>Total Wages</th>
        </tr>';
$sql = "SELECT * FROM orders INNER JOIN quality_control Using(OrderID) WHERE orders.is_delete = 0 AND ArtisanID = {$Arow['ArtisanID']}";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $g_total = 0;
    while ($row = mysqli_fetch_assoc($result)) {

        $html .= '<tr>
            <td>' . $row['OrderID'] . '</td>
            <td>' . $row['Product'] . '</td>
            <td>' . $row['Quantity'] . '</td>
            <td>' . $row['ApprovedQuantity'] . '</td>
            <td>' . $row['ProductionDueDate'] . '</td>
            <td>' . $row['WagesPerPiece'] . '</td>
            <td>' . number_format($row['WagesPerPiece'] * $row['ApprovedQuantity']) . '</td>
        </tr>';
        $g_total += ($row['WagesPerPiece']  * $row['ApprovedQuantity']);
    }

} else {
    echo 'No Record Found.';
}
$html .= '</table>

   

    <p class="wages">Grand Total : ' . number_format($g_total) . '</p>
';

// Output the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$pdf->Output('order_summary.pdf', 'I');
