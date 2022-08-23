<?php

$conn = null;

try {
    $conn = new PDO(
        "pgsql:host=postgres-b;port=5432;dbname=db;",
        'user',
        'mypass'
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully" . PHP_EOL;

    $items = 1000000;

    for ($i = 0; $i < $items; $i++) {
        $sql = "INSERT INTO books (id, category_id, title) VALUES (?, ?, ?);";
        $stmt= $conn->prepare($sql);

        $title = sprintf('Book %s', $i);

        $stmt->execute([rand(1, $items * 10), rand(1, 2), $title]);
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage()  . PHP_EOL;
} finally {
    if ($conn) {
        $conn = null;
    }
}