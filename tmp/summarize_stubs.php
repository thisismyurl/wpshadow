<?php
declare(strict_types=1);

$dir = '/workspaces/wpshadow/includes/diagnostics/new';
$files = glob($dir . '/*.php');
$summary = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $decoded = str_replace('\\"', '"', $content);
        preg_match('/namespace\\s+WPShadow\\\\DiagnosticsFuture\\\\([^;]+);/', $decoded, $ns);
        preg_match('/"module"\s*=>\s*"([^"]+)"/', $decoded, $module);

    $nsKey = $ns[1] ?? 'Unknown';
    $moduleKey = $module[1] ?? 'Unknown';

    $summary[$nsKey]['count'] = ($summary[$nsKey]['count'] ?? 0) + 1;
    $summary[$nsKey]['modules'][$moduleKey] = ($summary[$nsKey]['modules'][$moduleKey] ?? 0) + 1;
}

ksort($summary);
foreach ($summary as $ns => $data) {
    echo $ns . ': ' . ($data['count'] ?? 0) . PHP_EOL;
    if (!empty($data['modules'])) {
        ksort($data['modules']);
        foreach ($data['modules'] as $module => $count) {
            echo '  - ' . $module . ': ' . $count . PHP_EOL;
        }
    }
}
