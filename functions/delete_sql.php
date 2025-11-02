<?php
function executeDelete($conn, $sql, $types = "", $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Prepare failed: " . mysqli_error($conn),
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
            "affected_rows" => 0
        ];
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($affected > 0) {
        return [
            "success" => true,
            "message" => "Delete successful. Rows deleted: $affected",
            "affected_rows" => $affected
        ];
    } else {
        return [
            "success" => true,
            "message" => "Query executed, but no rows were deleted.",
            "affected_rows" => 0
        ];
    }
}