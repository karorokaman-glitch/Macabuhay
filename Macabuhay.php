<?php
require_once __DIR__ . "/data.php";
require_once __DIR__ . "/functions.php";

$q = isset($_GET["q"]) ? trim((string)$_GET["q"]) : "";
$category = isset($_GET["category"]) ? trim((string)$_GET["category"]) : "";
$status = isset($_GET["status"]) ? trim((string)$_GET["status"]) : "";

$categories = getUniqueValues($books, "category");
$statuses = getUniqueValues($books, "status");
$filteredBooks = filterBooks($books, $q, $category, $status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Information System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 30px;
            color: #222;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h1 {
            color: #263b63;
            margin-bottom: 5px;
        }

        .description {
            color: #777;
            margin-bottom: 25px;
        }

        .summary {
            background: #e8edf5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: flex-end;
        }

        .filters label {
            display: flex;
            flex-direction: column;
            font-size: 13px;
            color: #444;
            gap: 4px;
        }

        .filters input[type="text"],
        .filters select {
            padding: 9px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            min-width: 180px;
        }

        .filters button,
        .filters .reset {
            padding: 9px 14px;
            border-radius: 6px;
            border: none;
            background: #263b63;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .filters .reset {
            background: #6b7280;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #263b63;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        tr:hover {
            background: #f1f4f8;
        }

        .cover img {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: block;
        }

        .no-image {
            display: inline-block;
            width: 50px;
            height: 70px;
            line-height: 70px;
            text-align: center;
            font-size: 11px;
            color: #888;
            background: #f3f4f6;
            border: 1px dashed #ccc;
            border-radius: 4px;
        }

        .available {
            color: green;
            font-weight: bold;
        }

        .borrowed {
            color: #d97706;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            color: #666;
            padding: 20px;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            color: #888;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            body {
                padding: 15px;
            }

            .container {
                padding: 15px;
                overflow-x: auto;
            }

            table {
                min-width: 750px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Book Information System</h1>
    <p class="description">Book Information Management and Catalog</p>

    <form class="filters" method="get" action="">
        <label>
            Search
            <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Title, author, or category">
        </label>
        <label>
            Category
            <select name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo e($cat); ?>" <?php echo strcasecmp($category, $cat) === 0 ? "selected" : ""; ?>><?php echo e(formatText($cat)); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo e($st); ?>" <?php echo strcasecmp($status, $st) === 0 ? "selected" : ""; ?>><?php echo e(trim((string)$st)); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Search</button>
        <a class="reset" href="pogi.php">Reset</a>
    </form>

    <div class="summary">
        Showing <?php echo countBooks($filteredBooks); ?> of <?php echo countBooks($books); ?> books
    </div>

    <table>
        <thead>
            <tr>
                <th>Book ID</th>
                <th>Cover</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            <?php displayBooks($filteredBooks); ?>
        </tbody>
    </table>

    <div class="footer">
        Book Information System | PHP Activity
    </div>

</div>

</body>
</html>
