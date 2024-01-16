<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadedFile = $_FILES['file'];

    // Validate file type
    $allowedExtensions = ['xls', 'xlsx'];
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        die('Error: Invalid file format. Please upload an Excel file.');
    }

    // Load the Excel file
    $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();

    // Determine columns dynamically
    $headerRow = $sheet->getRowIterator()->current();
    $cellIterator = $headerRow->getCellIterator();

    $columns = [];
    foreach ($cellIterator as $cell) {
        $columns[] = $cell->getValue();
    }

    // Generate a unique table name based on timestamp
    $tableName = 'excel_data_' . time();

    // Assume your MySQL connection is already established
    $pdo = new PDO("mysql:host=localhost;dbname=excel_import_db", "root", "");

    // Create the table with dynamic columns
    $createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (" . implode(', ', array_map(function ($column) {
        return "`$column` VARCHAR(255)";
    }, $columns)) . ")";
    
    try {
        $pdo->exec($createTableSql);
        echo "Table created successfully!<br>";
    } catch (PDOException $e) {
        die("Error creating table: " . $e->getMessage());
    }

// Prepare the INSERT statement
$insertColumns = implode(', ', array_map(function ($column) {
    return "`$column`";
}, $columns));


// Check for empty or invalid column names
$isValidColumns = array_filter($columns, function ($column) {
    return is_string($column) && $column !== '';
});

if (empty($isValidColumns)) {
    die('Error: No valid column names found.');
}

$insertColumns = implode(', ', array_map(function ($column) {
    return "`$column`";
}, $isValidColumns));


// Iterate through the rows and insert into the database
foreach ($sheet->getRowIterator() as $row) {
    $rowData = [];
    $cellIterator = $row->getCellIterator();

    foreach ($cellIterator as $cell) {
        $rowData[] = $cell->getValue();
    }

    // Remove duplicate values (columns as values)
    $rowData = array_diff($rowData, $isValidColumns);

    // Filter out rows with empty values
    $filteredRowData = array_filter($rowData, function ($value) {
        return !empty($value);
    });

    // Skip to the next iteration if no valid data is found
    if (empty($filteredRowData)) {
        continue;
    }

    $insertPlaceholders = implode(', ', array_fill(0, count($filteredRowData), '?'));

    $insertSql = "INSERT INTO $tableName ($insertColumns) VALUES ($insertPlaceholders)";

    $stmt = $pdo->prepare($insertSql);

    // Execute the statement
    $success = $stmt->execute($filteredRowData);

    // Check for errors
    if (!$success) {
        echo "Execution failed:<br>";
        print_r($stmt->errorInfo());
    }
}












    echo "Data inserted into MySQL successfully! Table name: $tableName";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel Upload</title>
</head>
<body>
    <h1>Upload Excel File</h1>
    <form action="upload2.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xls, .xlsx" required>
        <button type="submit">Upload</button>

    <!-- Add the following HTML and JavaScript code -->
    <button onclick="redirectToDisplayData()">View Data</button>

    <script>
        function redirectToDisplayData() {
            window.location.href = 'display_data.php';
        }
    </script>
    </form>
</body>
</html>
