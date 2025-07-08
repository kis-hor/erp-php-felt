<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select2 Example</title>

    <!-- Include Select2 CSS --><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Include jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Include Select2 JS -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <form>
        <label for="ArtisanID">Select Artisan:</label>
        <select class="form-select" name="ArtisanID" id="ArtisanID" required>
            <option disabled selected>Select Artisan Name</option>
            <?php
            // Include your database connection file
            include 'config.php';

            $sql_artisan = "SELECT * FROM artisans WHERE is_delete = 0";
            $result_artisan = mysqli_query($conn, $sql_artisan);
            if (mysqli_num_rows($result_artisan) > 0) {
                while ($row_artisan = mysqli_fetch_assoc($result_artisan)) {
                    echo '<option value="' . $row_artisan['ArtisanID'] . '">' . $row_artisan['ArtisanName'] . '</option>';
                }
            } else {
                echo '<option value="">No records found</option>';
            }
            ?>
        </select>
    </form>

    <script>
        $(document).ready(function() {
            // Initialize Select2 on the ArtisanID select element
            $('#ArtisanID').select2({
                placeholder: 'Select Artisan Name',
                allowClear: true
            });
        });
    </script>
</body>
</html>
