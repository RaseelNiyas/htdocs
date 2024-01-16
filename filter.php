<?php
// Assume your MySQL connection is already established
$pdo = new PDO("mysql:host=localhost;dbname=excel_import_db", "root", "");

// Assume the table name is passed as a query parameter
$tableName = $_GET['table'] ?? '';

if (empty($tableName)) {
    die('Error: Table name is missing.');
}

// Fetch column names from the table
$stmt = $pdo->prepare("DESCRIBE $tableName");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Display filter form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<h1>Filter Data</h1>";
    echo "<form action=\"filter.php\" method=\"post\">";
    foreach ($columns as $column) {
        echo "<label for=\"$column\">$column:</label>";
        echo "<input type=\"text\" name=\"filters[$column]\">";
        echo "<br>";
    }
    echo "<button type=\"submit\">Apply Filters</button>";
    echo "</form>";
}

// Process filter form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = $_POST['filters'] ?? [];

    // Construct the WHERE clause based on filters
    $whereClause = '';
    foreach ($filters as $column => $value) {
        if (!empty($value)) {
            $whereClause .= "$column LIKE :$column AND ";
        }
    }
    $whereClause = rtrim($whereClause, ' AND ');

    // Fetch filtered data from the table
    $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE $whereClause");
    $stmt->execute(array_map(function ($value) {
        return "%$value%";
    }, $filters));
    $filteredData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display filtered data
    echo "<h1>Filtered Data</h1>";
    echo "<pre>";
    print_r($filteredData);
    echo "</pre>";
}
?>