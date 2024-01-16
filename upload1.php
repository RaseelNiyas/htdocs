<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Assume your MySQL connection is already established
// $pdo = new PDO("mysql:host=localhost;dbname=your_database_name", "your_username", "your_password");
$pdo = new PDO("mysql:host=localhost;dbname=excel_import_db", "root", "");


// Assume your uploaded Excel file is named 'uploaded_file.xls' (change accordingly)
$inputFileName = 'C:/xampp/htdocs/excel_import_db/path/to/uploaded/uploaded_file.xls';


// Load the Excel file
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getActiveSheet();

// Get the header row to determine column names
$headerRow = $sheet->getRowIterator()->current()->getValues();

// Generate a unique table name based on the current timestamp
$tableName = 'excel_data_' . time();

// Create the table with dynamic columns
$columns = array_map(function ($column) {
    return "`$column` VARCHAR(255)";
}, $headerRow);

$createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (" . implode(', ', $columns) . ")";
$pdo->exec($createTableSql);

// Prepare the INSERT statement
$insertColumns = implode(', ', array_map(function ($column) {
    return "`$column`";
}, $headerRow));
$insertSql = "INSERT INTO $tableName ($insertColumns) VALUES (:" . implode(', :', $headerRow) . ")";

// Iterate through the rows and insert into the database
foreach ($sheet->getRowIterator() as $row) {
    $rowData = $row->getValues();
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute(array_combine($headerRow, $rowData));
}

echo "Data inserted into MySQL successfully!";
?>
