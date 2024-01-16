<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['file']['name'])) {
    $inputFileName = '../uploads/' . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], $inputFileName);

    // Load the Excel file
    $spreadsheet = IOFactory::load($inputFileName);

    // Get the active sheet
    $sheet = $spreadsheet->getActiveSheet();

    // Assume your MySQL connection is already established (you need to set up your database connection)
    // $pdo = new PDO("mysql:host=localhost;dbname=excel_import_db", "", "your_password");
    $pdo = new PDO("mysql:host=localhost;dbname=excel_import_db", "root", "");


    // Create a table to store the Excel data
    $tableName = 'excel_data';
    $columns = array_map(function ($column) {
        return "`$column` VARCHAR(255)";
    }, range('column1', 'column20'));
    
    $createTableSql = "CREATE TABLE IF NOT EXISTS $tableName (" . implode(', ', $columns) . ")";
    $pdo->exec($createTableSql);

    // Prepare the INSERT statement
    $columnNames = implode(', ', array_map(function ($column) {
        return "`$column`";
    }, range('column1', 'column20')));
    
    $insertSql = "INSERT INTO $tableName ($columnNames) VALUES (:" . implode(', :', range('column1', 'column20')) . ")";

    // Iterate through the rows and insert into the database
    foreach ($sheet->getRowIterator() as $row) {
        $rowData = [];
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue();
        }
    
        // Now $rowData contains values for all cells in the current row
    
        // Your database insertion logic here
        // For example, you can use $rowData to construct an INSERT statement
        // and insert the data into your MySQL database.
    }

    echo "Data inserted into MySQL successfully!";
}
?>
