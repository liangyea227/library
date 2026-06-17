<?php
session_start();
error_reporting(0);
require_once 'includes/config.php';
header('Content-Type: application/json');

// ── Lookup by Student ID (exact) ──────────────────────────────
if(!empty($_POST['studentid'])){
    $sid = strtoupper(trim($_POST['studentid']));
    $sql = "SELECT StudentId, FullName, EmailId, MobileNumber, Status
            FROM tblstudents WHERE StudentId = :sid LIMIT 1";
    $q   = $dbh->prepare($sql);
    $q->bindParam(':sid', $sid, PDO::PARAM_STR);
    $q->execute();

    if($q->rowCount() === 0){
        echo json_encode(['found' => false]);
        exit;
    }
    $row = $q->fetch(PDO::FETCH_OBJ);

    // Count active (unreturned) borrows for limit check
    $bq = $dbh->prepare("SELECT COUNT(*) FROM tblissuedbookdetails
                          WHERE StudentID = :sid
                          AND (RetrunStatus = 0 OR RetrunStatus IS NULL OR RetrunStatus = '')");
    $bq->bindParam(':sid', $row->StudentId, PDO::PARAM_STR);
    $bq->execute();
    $activeBorrows = (int)$bq->fetchColumn();

    echo json_encode([
        'found'         => true,
        'active'        => (int)$row->Status === 1,
        'studentid'     => $row->StudentId,
        'name'          => $row->FullName,
        'email'         => $row->EmailId,
        'mobile'        => $row->MobileNumber,
        'activeBorrows' => $activeBorrows,
        'atLimit'       => $activeBorrows >= 3,
    ]);
    exit;
}

// ── Search by Name — returns array of up to 10 matches ────────
if(!empty($_POST['studentname'])){
    $name = '%' . trim($_POST['studentname']) . '%';
    $sql  = "SELECT StudentId, FullName, EmailId, MobileNumber, Status
             FROM tblstudents WHERE FullName LIKE :name
             ORDER BY FullName ASC LIMIT 10";
    $q    = $dbh->prepare($sql);
    $q->bindParam(':name', $name, PDO::PARAM_STR);
    $q->execute();

    $rows     = $q->fetchAll(PDO::FETCH_OBJ);
    $students = [];
    foreach($rows as $row){
        $students[] = [
            'studentid' => $row->StudentId,
            'name'      => $row->FullName,
            'email'     => $row->EmailId,
            'mobile'    => $row->MobileNumber,
            'active'    => (int)$row->Status === 1,
        ];
    }
    echo json_encode($students);
    exit;
}

echo json_encode(['found' => false]);