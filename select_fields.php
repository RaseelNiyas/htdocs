<?php
// Connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "excel_import_db";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the selected table from the query parameter
    $selectedTable = $_GET['table'] ?? '';

    // Fetch all columns from the selected table
    $stmt = $pdo->query("DESCRIBE $selectedTable");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Display the form to select fields
    echo "<h1>Select Fields for Filtering</h1>";
    echo "<form action='display_data.php' method='get'>";
    echo "<input type='hidden' name='table' value='$selectedTable'>";
    foreach ($columns as $column) {
        echo "<label for='$column'>$column</label>";
        echo "<input type='checkbox' name='selected_fields[]' value='$column'>";
    }
    echo "<button type='submit'>Apply Filters</button>";
    echo "</form>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
