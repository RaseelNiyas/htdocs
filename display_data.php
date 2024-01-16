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

    // Fetch all table names from the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get the selected table from the query parameter
    $selectedTable = $_GET['table'] ?? '';

    // Fetch all rows from the selected table
    if ($selectedTable && in_array($selectedTable, $tables)) {
        // Fetch all columns from the selected table
        $stmt = $pdo->query("DESCRIBE $selectedTable");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get filter values from the form
        $filters = [];
        foreach ($columns as $column) {
            $filters[$column] = $_GET[$column] ?? '';
        }

        // Build the WHERE clause based on filter values
        $whereClause = '';
        foreach ($filters as $column => $value) {
            if ($value !== '') {
                $escapedValue = $pdo->quote($value); // Prevent SQL injection
                $whereClause .= " AND $column = $escapedValue";
            }
        }

        // Fetch filtered rows
        $stmt = $pdo->query("SELECT * FROM $selectedTable WHERE 1 $whereClause");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Display the data
        echo "<h1>Data from Table: $selectedTable</h1>";
        echo "<p>Total rows : " . count($rows) . "</p>";
        echo "<form action='display_data.php' method='get'>";
        echo "<input type='hidden' name='table' value='$selectedTable'>";
        foreach ($columns as $column) {
            echo "<label for='$column'>$column:</label>";
            echo "<input type='text' name='$column' value='{$filters[$column]}'>";
        }
        echo "<button type='submit'>Apply Filters</button>";
        echo "<button type='button' onclick='clearFilters()'>Clear Filters</button>";
        echo "</form>";
        echo "<table border='1'>";
        echo "<tr>";
        foreach ($rows[0] as $column => $value) {
            echo "<th>$column</th>";
        }
        echo "</tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Please select a valid table to display data.</p>";
    }

    // Dropdown menu for selecting tables
    echo "<form action='display_data.php' method='get'>";
    echo "<label for='table'>Select Table:</label>";
    echo "<select name='table' id='table'>";
    foreach ($tables as $table) {
        echo "<option value='$table' ";
        echo ($selectedTable == $table) ? 'selected' : '';
        echo ">$table</option>";
    }
    echo "</select>";
    echo "<button type='submit'>Display Data</button>";
    echo "</form>";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<script>
    function clearFilters() {
        // Get all input elements in the form
        var inputs = document.querySelectorAll("form input[type=text]");

        // Loop through each input element and set its value to an empty string
        inputs.forEach(function(input) {
            input.value = '';
        });

        // Submit the form to clear the filters
        document.querySelector("form").submit();
    }
</script>
