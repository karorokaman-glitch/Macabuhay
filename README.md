
<?php
mysqli_report(MYSQLI_REPORT_OFF);

$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "classhub";

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$dbError = null;
if ($mysqli->connect_errno) {
    $dbError = "Database connection failed: " . $mysqli->connect_error . ". Create the database and table by importing books.sql in phpMyAdmin.";
} else {
    $mysqli->set_charset("utf8mb4");
}

$q = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$category = isset($_GET["category"]) ? trim($_GET["category"]) : "";
$status = isset($_GET["status"]) ? trim($_GET["status"]) : "";

$books = [];
$categories = [];
$statuses = [];

if (!$dbError) {
    $catResult = $mysqli->query("SELECT DISTINCT category FROM books ORDER BY category");
    if ($catResult) {
        while ($row = $catResult->fetch_assoc()) {
            $categories[] = $row["category"];
        }
        $catResult->free();
    }

    $statusResult = $mysqli->query("SELECT DISTINCT status FROM books ORDER BY status");
    if ($statusResult) {
        while ($row = $statusResult->fetch_assoc()) {
            $statuses[] = $row["status"];
        }
        $statusResult->free();
    }

    $sql = "SELECT id, title, author, category, price, status, image FROM books WHERE 1=1";
    $types = "";
    $params = [];

    if ($q !== "") {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR category LIKE ?)";
        $like = "%" . $q . "%";
        $types .= "sss";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ($category !== "") {
        $sql .= " AND category = ?";
        $types .= "s";
        $params[] = $category;
    }
    if ($status !== "") {
        $sql .= " AND status = ?";
        $types .= "s";
        $params[] = $status;
    }

    $sql .= " ORDER BY id ASC";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $result->free();
        } else {
            $dbError = "Query failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $dbError = "Query failed: " . $mysqli->error . ". Make sure the 'books' table exists (run books.sql).";
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function displayBooks($books) {
    if (empty($books)) {
        echo "<tr><td colspan=\"7\" class=\"empty\">No books found.</td></tr>";
        return;
    }
    foreach ($books as $book) {
        $statusClass = strtolower($book["status"]) === "available" ? "available" : (strtolower($book["status"]) === "borrowed" ? "borrowed" : "");
        $image = isset($book["image"]) ? trim((string)$book["image"]) : "";
        echo "<tr>";
        echo "<td>" . e($book["id"]) . "</td>";
        echo "<td class=\"cover\">";
        if ($image !== "") {
            echo "<img src=\"" . e($image) . "\" alt=\"Cover\">";
        } else {
            echo "<span class=\"no-image\">No image</span>";
        }
        echo "</td>";
        echo "<td>" . e(ucwords(strtolower($book["title"]))) . "</td>";
        echo "<td>" . e(ucwords(strtolower($book["author"]))) . "</td>";
        echo "<td>" . e(ucwords(strtolower($book["category"]))) . "</td>";
        echo "<td>₱" . number_format((float)$book["price"], 2) . "</td>";
        echo "<td class=\"" . e($statusClass) . "\">" . e($book["status"]) . "</td>";
        echo "</tr>";
    }
}

function countBooks($books) {
    return count($books);
}
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

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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

    <?php if ($dbError): ?>
        <div class="error"><?php echo e($dbError); ?></div>
    <?php endif; ?>

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
                    <option value="<?php echo e($cat); ?>" <?php echo $category === $cat ? "selected" : ""; ?>><?php echo e(ucwords(strtolower($cat))); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Status
            <select name="status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo e($st); ?>" <?php echo $status === $st ? "selected" : ""; ?>><?php echo e($st); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit">Search</button>
        <a class="reset" href="pogi.php">Reset</a>
    </form>

    <div class="summary">
        Total Books: <?php echo countBooks($books); ?>
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
            <?php displayBooks($books); ?>
        </tbody>
    </table>

    <div class="footer">
        Book Information System | PHP &amp; MySQL
    </div>

</div>

</body>
</html>
<?php if (!$dbError) { $mysqli->close(); } ?>
