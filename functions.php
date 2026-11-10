<?php
// Helper functions for processing and displaying book records

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function formatText($value) {
    $value = trim((string)$value);
    return ucwords(strtolower($value));
}

function formatPrice($price) {
    return "₱" . number_format((float)$price, 2);
}

function statusClass($status) {
    $normalized = strtolower(trim((string)$status));
    if ($normalized === "available") {
        return "available";
    }
    if ($normalized === "borrowed") {
        return "borrowed";
    }
    return "";
}

function countBooks($books) {
    return count($books);
}

function getUniqueValues($books, $key) {
    $values = [];
    foreach ($books as $book) {
        if (!isset($book[$key])) {
            continue;
        }
        $value = trim((string)$book[$key]);
        if ($value === "") {
            continue;
        }
        $lower = strtolower($value);
        if (!isset($values[$lower])) {
            $values[$lower] = $value;
        }
    }
    $result = array_values($values);
    sort($result, SORT_NATURAL | SORT_FLAG_CASE);
    return $result;
}

function matchesSearch($book, $query) {
    if ($query === "") {
        return true;
    }
    $needle = strtolower(trim($query));
    $haystack = strtolower($book["title"] . " " . $book["author"] . " " . $book["category"]);
    return strpos($haystack, $needle) !== false;
}

function filterBooks($books, $query, $category, $status) {
    $filtered = [];
    foreach ($books as $book) {
        if (!matchesSearch($book, $query)) {
            continue;
        }
        if ($category !== "" && strcasecmp(trim((string)$book["category"]), trim($category)) !== 0) {
            continue;
        }
        if ($status !== "" && strcasecmp(trim((string)$book["status"]), trim($status)) !== 0) {
            continue;
        }
        $filtered[] = $book;
    }
    return $filtered;
}

function displayBooks($books) {
    if (empty($books)) {
        echo "<tr><td colspan=\"7\" class=\"empty\">No books found.</td></tr>";
        return;
    }
    foreach ($books as $book) {
        $image = isset($book["image"]) ? trim((string)$book["image"]) : "";
        echo "<tr>";
        echo "<td>" . e($book["id"]) . "</td>";
        echo "<td class=\"cover\">";
        if ($image !== "") {
            echo "<img src=\"" . e($image) . "\" alt=\"" . e(formatText($book["title"])) . " cover\">";
        } else {
            echo "<span class=\"no-image\">No image</span>";
        }
        echo "</td>";
        echo "<td>" . e(formatText($book["title"])) . "</td>";
        echo "<td>" . e(formatText($book["author"])) . "</td>";
        echo "<td>" . e(formatText($book["category"])) . "</td>";
        echo "<td>" . e(formatPrice($book["price"])) . "</td>";
        echo "<td class=\"" . e(statusClass($book["status"])) . "\">" . e(trim((string)$book["status"])) . "</td>";
        echo "</tr>";
    }
}
?>
