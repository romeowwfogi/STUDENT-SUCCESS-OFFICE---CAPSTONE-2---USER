<?php
function loadEmailTemplate($templatePath, $data = [])
{
    $template = file_get_contents($templatePath);
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}