<?php
session_start();
if (!isset($_SESSION['Username']) || !in_array($_SESSION['Role'], ['Admin', 'Salesperson', 'BusinessOperations', 'Manager'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Production Dashboard';
?>
<?php include "assets/includes/header.php"; ?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Production Dashboard</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title pb-3"><b>Production Summary</b></h5>
                            <div class="table-responsive table-card">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>PO Number</th>
                                            <th>Customer Name</th>
                                            <th>Salesperson</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT so.PONumber, so.CustomerName, u.Username AS Salesperson, pa.Status, pa.Progress, pa.AssignmentID
                                                FROM production_assignments pa
                                                JOIN inventory_checks ic ON pa.InventoryCheckID = ic.InventoryCheckID
                                                JOIN sales_orders so ON ic.SalesOrderID = so.SalesOrderID
                                                JOIN users u ON so.CreatedBy = u.UserID
                                                WHERE pa.is_delete = 0";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . $row['PONumber'] . '</td>';
                                            echo '<td>' . $row['CustomerName'] . '</td>';
                                            echo '<td>' . $row['Salesperson'] . '</td>';
                                            echo '<td>' . $row['Status'] . '</td>';
                                            echo '<td>
                                                    <div class="progress" style="width: 100px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: ' . $row['Progress'] . '%;" aria-valuenow="' . $row['Progress'] . '" aria-valuemin="0" aria-valuemax="100">' . $row['Progress'] . '%</div>
                                                    </div>
                                                  </td>';
                                            echo '<td><a href="#details-' . $row['AssignmentID'] . '" class="btn btn-primary btn-sm">View Details</a></td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart for Production Progress -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title pb-3"><b>Production Progress Overview</b></h5>
                            <canvas id="productionChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Get chart data from PHP
    <?php
    $sql = "SELECT ProductName, Progress FROM production_assignments WHERE is_delete = 0 LIMIT 5";
    $result = mysqli_query($conn, $sql);
    $labels = [];
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['ProductName'];
        $data[] = $row['Progress'];
    }
    ?>

    const ctx = document.getElementById('productionChart').getContext('2d');
    const productionChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Production Progress (%)',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    '#28a745',
                    '#007bff',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8'
                ],
                borderColor: [
                    '#218838',
                    '#0056b3',
                    '#e0a800',
                    '#c82333',
                    '#117a8b'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Progress (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Products'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Production Progress Overview'
                }
            }
        }
    });
</script>

<?php include "assets/includes/footer.php"; ?>