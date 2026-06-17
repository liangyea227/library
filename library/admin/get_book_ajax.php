<?php
session_start();
error_reporting(0);
require_once 'includes/config.php';
header('Content-Type: application/json');

$baseSelect = "
    SELECT
        b.id,
        b.BookName,
        b.ISBNNumber,
        b.bookImage,
        b.bookQty,
        a.AuthorName,
        COUNT(CASE WHEN (i.RetrunStatus = 0 OR i.RetrunStatus IS NULL OR i.RetrunStatus = '')
              THEN 1 END) AS issuedCount
    FROM tblbooks b
    LEFT JOIN tblauthors a ON a.id = b.AuthorId
    LEFT JOIN tblissuedbookdetails i ON i.BookId = b.id
";

// ── Search by title (partial) ──────────────────────────────────
if(!empty($_POST['q'])){
    $q    = '%' . trim($_POST['q']) . '%';
    $sql  = $baseSelect . "WHERE b.BookName LIKE :q GROUP BY b.id ORDER BY b.BookName ASC LIMIT 10";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':q', $q, PDO::PARAM_STR);
    $stmt->execute();
    echo json_encode(buildResult($stmt));
    exit;
}

// ── Search by exact ISBN ───────────────────────────────────────
if(!empty($_POST['isbn'])){
    $isbn = trim($_POST['isbn']);
    $sql  = $baseSelect . "WHERE b.ISBNNumber = :isbn GROUP BY b.id LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $stmt->execute();
    echo json_encode(buildResult($stmt));
    exit;
}

echo json_encode([]);

function buildResult($stmt){
    $rows  = $stmt->fetchAll(PDO::FETCH_OBJ);
    $books = [];
    foreach($rows as $row){
        $avail   = max(0, (int)$row->bookQty - (int)$row->issuedCount);
        $books[] = [
            'id'     => (int)$row->id,
            'title'  => $row->BookName,
            'isbn'   => $row->ISBNNumber  ?? '',
            'author' => $row->AuthorName  ?? 'Unknown',
            'image'  => $row->bookImage   ?? '',
            'qty'    => (int)$row->bookQty,
            'avail'  => $avail,
        ];
    }
    return $books;
}