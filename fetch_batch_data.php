<?php
include 'dbAL.php';

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed."]));
}

if (isset($_POST['batch_id'])) {
    $batch_id = intval($_POST['batch_id']);
    $sql = "SELECT * FROM harvested_batch WHERE batch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $batch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $batch = $result->fetch_assoc();

    if ($batch) {
        echo json_encode(["status" => "success", "data" => $batch]);
    } else {
        echo json_encode(["status" => "error", "message" => "Batch not found."]);
    }
}
?>