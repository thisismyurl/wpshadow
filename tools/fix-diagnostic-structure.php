<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

function fix_namespace_duplicates(string $content): string {
    // Collapse multiple consecutive namespace declarations into one
    $content = preg_replace('/(namespace\s+WPShadow\\\\Diagnostics;)(?:\s*\R\s*namespace\s+WPShadow\\\\Diagnostics;)+/m', '$1', $content);
    // Remove stray duplicate namespaces anywhere after the first occurrence
    $firstPos = strpos($content, 'namespace WPShadow\\Diagnostics;');
    if ($firstPos !== false) {
        $before = substr($content, 0, $firstPos + strlen('namespace WPShadow\\Diagnostics;'));
        $after  = substr($content, $firstPos + strlen('namespace WPShadow\\Diagnostics;'));
        $after  = preg_replace('/\s*namespace\s+WPShadow\\\\Diagnostics;\s*/m', "\n\n", $after);
        $content = $before . $after;
    }
    // Ensure class starts on its own line after namespace
    $content = preg_replace('/(namespace\s+WPShadow\\\\Diagnostics;)(\s*)class\s+/m', "$1\n\nclass ", $content);
    return $content;
}

function fix_malformed_extends(string $content): string {
    // Fix cases like: extends Diagnostic_Base -file-optimization {
    $content = preg_replace('/extends\s+Diagnostic_Base\s+-[^\{]+\{/', 'extends Diagnostic_Base {', $content);
    return $content;
}

function process_file(string $path): ?string {
    $orig = file_get_contents($path);
    if ($orig === false) return null;
    $updated = $orig;
    $updated = fix_namespace_duplicates($updated);
    $updated = fix_malformed_extends($updated);
    if ($updated !== $orig) return $updated;
    return null;
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diagDir));
$changed = 0;
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (!preg_match('/class-diagnostic-.*\.php$/', $path)) continue;
    $nc = process_file($path);
    if ($nc !== null) {
        file_put_contents($path, $nc);
        $changed++;
        fwrite(STDOUT, "Fixed: {$path}\n");
    }
}
fwrite(STDOUT, "Done. Files fixed: {$changed}\n");
