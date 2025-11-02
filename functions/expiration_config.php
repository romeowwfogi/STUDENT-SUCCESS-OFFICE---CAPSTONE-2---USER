<?php
function executeExpirationConfig($conn, $type)
{
    $sql = "SELECT interval_value, interval_unit FROM expiration_config WHERE type = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [
            "success" => false,
            "message" => "Prepare failed: " . mysqli_error($conn),
            "data" => []
        ];
    }

    mysqli_stmt_bind_param($stmt, "s", $type);

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

    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$data) {
        return [
            "success" => false,
            "message" => "No expiration config found for type '$type'.",
            "data" => []
        ];
    }

    return [
        "success" => true,
        "message" => "Expiration config retrieved successfully.",
        "data" => [
            "interval_value" => (int)$data['interval_value'],
            "interval_unit"  => $data['interval_unit']
        ]
    ];
}

// // --- Usage example ---
// $type = 'password_reset';
// $response = executeExpirationConfig($conn, $type);

// if ($response['success']) {
//     $intervalValue = $response['data']['interval_value'];
//     $intervalUnit  = $response['data']['interval_unit'];

//     echo "✅ Config: $intervalValue $intervalUnit\n";

//     // Example usage:
//     $sql = "
//         UPDATE tokenization
//         SET 
//             expires_at = DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit)
//         WHERE name = ?
//     ";

//     $stmt = mysqli_prepare($conn, $sql);
//     mysqli_stmt_bind_param($stmt, "s", $type);
//     mysqli_stmt_execute($stmt);
//     mysqli_stmt_close($stmt);
// } else {
//     echo "❌ " . $response['message'];
// }