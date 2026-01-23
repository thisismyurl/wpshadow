#!/usr/bin/env php
<?php
/**
 * Targeted ID Field Injector v2
 * Specifically targets diagnostics that have return array() but no 'id' field
 */

declare(strict_types=1);

$files_to_fix = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('/workspaces/wpshadow/includes/diagnostics'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strpos($file->getFilename(), 'class-diagnostic-') !== 0) {
        continue;
    }
    
    $path = $file->getPathname();
    $content = file_get_contents($path);
    
    // Skip if already has 'id'
    if (preg_match("/['\"]id['\"]\s*=>/", $content)) {
        continue;
    }
    
    // Only process files that have return array()
    if (preg_match("/return\s*array\s*\(/", $content)) {
        $files_to_fix[] = $path;
    }
}

echo "🔧 Found " . count($files_to_fix) . " diagnostics to fix\n";
echo "═════════════════════════════════════════════\n\n";

$fixed = 0;
$errors = [];

foreach ($files_to_fix as $file_path) {
    $content = file_get_contents($file_path);
    $filename = basename($file_path);
    
    // Extract slug from protected static $slug property
    $slug = null;
    if (preg_match("/protected\s+static\s+\$slug\s*=\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
        $slug = $matches[1];
    } else {
        // Fallback: use filename
        $slug = str_replace(['class-diagnostic-', '.php'], '', $filename);
    }
    
    // Find the first "return array(" and add 'id' after it
    // Handle multi-line return statements
    $pattern = '/(return\s+array\s*\(\s*)([^\)]*?)([\'\"]title[\'\"]\s*=>|[\'\"]description[\'\"]\s*=>)/s';
    
    if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        // Get indentation from the next field
        if (preg_match('/\n(\s+)[\'\"]/', $matches[2][0], $indent_match)) {
            $indent = $indent_match[1];
        } else {
            $indent = '\t\t\t';
        }
        
        // Build the replacement - insert 'id' before 'title' or 'description'
        $replacement = $matches[1][0] . "\n" . $indent . "'id' => '" . $slug . "',\n" . $indent . $matches[3][0];
        
        $new_content = substr_replace(
            $content,
            $replacement,
            $matches[1][1],
            $matches[1][0]  == $matches[1][0] ? strlen($matches[1][0]) + strlen($matches[2][0]) + strlen($matches[3][0]) : 0
        );
        
        // Actually, simpler approach - just replace the first occurrence
        $new_content = preg_replace(
            $pattern,
            '${1}' . "\n" . $indent . "'id' => '" . $slug . "',\n" . $indent . '${3}',
            $content,
            1
        );
        
        if ($new_content !== $content) {
            if (file_put_contents($file_path, $new_content) !== false) {
                $fixed++;
                echo "✅ $filename → id: '$slug'\n";
            } else {
                $errors[] = $filename;
                echo "❌ $filename (write failed)\n";
            }
        } else {
            echo "⏭️  $filename (no regex match)\n";
        }
    } else {
        echo "⏭️  $filename (pattern not found)\n";
    }
}

echo "\n";
echo "╔════════════════════════════════════╗\n";
printf("║ ✅ Fixed:     %3d                 ║\n", $fixed);
printf("║ ❌ Errors:    %3d                 ║\n", count($errors));
echo "╚════════════════════════════════════╝\n";
