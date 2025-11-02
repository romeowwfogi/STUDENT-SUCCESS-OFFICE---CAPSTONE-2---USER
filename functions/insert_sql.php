<?php
function executeInsert($conn, $sql, $types = "", $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Prepare failed: " . mysqli_error($conn),
            "insert_id" => null,
            "affected_rows" => 0
        ];
    }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return [
            "success" => false,
            "message" => "Execution failed: " . $error,
            "insert_id" => null,
            "affected_rows" => 0
        ];
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    $insert_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        return [
            "success" => true,
            "message" => "Insert successful. ID: $insert_id",
            "insert_id" => $insert_id,
            "affected_rows" => $affected
        ];
    } else {
        return [
            "success" => true,
            "message" => "Query executed, but no rows were inserted.",
            "insert_id" => null,
            "affected_rows" => 0
        ];
    }
}
