<?php
// Assume $selectedTable contains the selected table name

// Fetch all columns from the selected table
$stmt = $pdo->query("DESCRIBE $selectedTable");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Selection</title>
</head>
<body>
    <h1>Select Fields for Filtering</h1>
    <form action="display_data.php" method="get">
        <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">

        <!-- Display checkboxes for each field -->
        <?php foreach ($columns as $column) : ?>
            <label>
                <input type="checkbox" name="filters[]" value="<?php echo $column; ?>">
                <?php echo $column; ?>
            </label>
            <br>
        <?php endforeach; ?>

        <button type="submit">Apply Filters</button>
    </form>
</body>
</html>
