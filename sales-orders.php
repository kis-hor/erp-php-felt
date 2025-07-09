<?php
session_start();
if (!isset($_SESSION['Username']) || !in_array($_SESSION['Role'], ['Admin', 'Salesperson'])) {
    header('Location: login');
    exit;
}
include "config.php";
$title = 'Sales Orders';

if (isset($_POST['Add_Sales_Order'])) {
    $poNumber = mysqli_real_escape_string($conn, $_POST['poNumber']);
    $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
    $orderDate = mysqli_real_escape_string($conn, $_POST['orderDate']);
    $createdBy = $_SESSION['UserID'];

    // Start transaction to ensure data consistency
    mysqli_begin_transaction($conn);

    try {
        // Insert into sales_orders
        $sql = "INSERT INTO sales_orders (PONumber, CustomerName, OrderDate, CreatedBy)
                VALUES ('$poNumber', '$customerName', '$orderDate', $createdBy)";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception('Failed to add sales order: ' . mysqli_error($conn));
        }
        $salesOrderID = mysqli_insert_id($conn);

        // Handle multiple products
        $productNames = $_POST['productName'];
        $productDescriptions = $_POST['productDescription'];
        $orderedQuantities = $_POST['orderedQuantity'];
        $productPhotos = $_FILES['productPhoto'];

        $targetDir = "Uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        for ($i = 0; $i < count($productNames); $i++) {
            $productName = mysqli_real_escape_string($conn, $productNames[$i]);
            $productDescription = mysqli_real_escape_string($conn, $productDescriptions[$i]);
            $orderedQuantity = mysqli_real_escape_string($conn, $orderedQuantities[$i]);
            $productPhoto = '';

            // Handle file upload for each product
            if (!empty($productPhotos['name'][$i])) {
                $productPhoto = $targetDir . basename($productPhotos['name'][$i]);
                move_uploaded_file($productPhotos['tmp_name'][$i], $productPhoto);
            }

            $sql = "INSERT INTO sales_order_products (SalesOrderID, ProductName, ProductDescription, ProductPhoto, OrderedQuantity)
                    VALUES ($salesOrderID, '$productName', '$productDescription', '$productPhoto', $orderedQuantity)";
            if (!mysqli_query($conn, $sql)) {
                throw new Exception('Failed to add product: ' . mysqli_error($conn));
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        $_SESSION['success'] = 'Sales Order with Products Added Successfully.';
        header("Location: sales-orders");
        exit;
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: sales-orders");
        exit;
    }
}
?>

<?php include "assets/includes/header.php"; ?>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Sales Orders</h4>
                    </div>
                </div>
            </div>
            <?php
            if (isset($_SESSION['error']) && !empty($_SESSION['error'])) {
                echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success']) && !empty($_SESSION['success'])) {
                echo '<div class="alert alert-primary" role="alert">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="add-order-form" method="post" enctype="multipart/form-data">
                                <h2>Add Customer Order</h2>
                                <div class="mb-3">
                                    <label for="poNumber" class="form-label">PO Number:</label>
                                    <input type="text" class="form-control" id="poNumber" name="poNumber" required>
                                </div>
                                <div class="mb-3">
                                    <label for="customerName" class="form-label">Customer Name:</label>
                                    <input type="text" class="form-control" id="customerName" name="customerName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="orderDate" class="form-label">Order Date:</label>
                                    <input type="date" class="form-control" id="orderDate" name="orderDate" required>
                                </div>
                                <h3>Products</h3>
                                <div id="product-rows">
                                    <div class="product-row mb-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="productName" class="form-label">Product Name:</label>
                                                <input type="text" class="form-control" name="productName[]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="productDescription" class="form-label">Product Description:</label>
                                                <textarea class="form-control" name="productDescription[]" required></textarea>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="productPhoto" class="form-label">Product Photo:</label>
                                                <input type="file" class="form-control" name="productPhoto[]" accept="image/*">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="orderedQuantity" class="form-label">Quantity:</label>
                                                <input type="number" class="form-control" name="orderedQuantity[]" min="1" required>
                                            </div>
                                            <div class="col-md-2 align-self-end">
                                                <button type="button" class="btn btn-danger remove-product-row" style="margin-top: 32px;">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary mb-3" id="add-product-row">Add Another Product</button>
                                <button type="submit" class="btn btn-primary" name="Add_Sales_Order">Add Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('add-product-row').addEventListener('click', function() {
        const productRows = document.getElementById('product-rows');
        const newRow = document.createElement('div');
        newRow.className = 'product-row mb-3';
        newRow.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <label for="productName" class="form-label">Product Name:</label>
                <input type="text" class="form-control" name="productName[]" required>
            </div>
            <div class="col-md-3">
                <label for="productDescription" class="form-label">Product Description:</label>
                <textarea class="form-control" name="productDescription[]" required></textarea>
            </div>
            <div class="col-md-2">
                <label for="productPhoto" class="form-label">Product Photo:</label>
                <input type="file" class="form-control" name="productPhoto[]" accept="image/*">
            </div>
            <div class="col-md-2">
                <label for="orderedQuantity" class="form-label">Quantity:</label>
                <input type="number" class="form-control" name="orderedQuantity[]" min="1" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="button" class="btn btn-danger remove-product-row" style="margin-top: 32px;">Remove</button>
            </div>
        </div>
    `;
        productRows.appendChild(newRow);
    });

    document.getElementById('product-rows').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product-row')) {
            const productRows = document.querySelectorAll('.product-row');
            if (productRows.length > 1) {
                e.target.closest('.product-row').remove();
            } else {
                alert('At least one product is required.');
            }
        }
    });
</script>

<?php include "assets/includes/footer.php"; ?>