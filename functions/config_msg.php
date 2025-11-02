<?php
function getConfigValue($conn, $name, $default = "")
{
    $sql = "SELECT value FROM msg_config WHERE name = ? AND is_active = 1";
    $types = "s";
    $params = [$name];
    $result = executeSelect($conn, $sql, $types, $params);
    if ($result['success'] && count($result['data']) > 0) {
        return $result['data'][0]['value'];
    }
    return $default;
}