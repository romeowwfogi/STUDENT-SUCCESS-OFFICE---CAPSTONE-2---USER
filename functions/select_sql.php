<?php
function executeSelect($conn, $sql, $types = "", $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Prepare failed: " . mysqli_error($conn),
            "data" => []
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
            "data" => []
        ];
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return [
            "success" => false,
            "message" => "No result returned: " . $error,
            "data" => []
        ];
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return [
        "success" => true,
        "message" => count($data) > 0 ? "Data retrieved successfully." : "No records found.",
        "data" => $data
    ];
}