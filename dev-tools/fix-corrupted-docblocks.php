<?php
/**
 * Fix Corrupted Docblocks
 *
 * Removes literal \n characters from docblocks in performance diagnostic files.
 * These files have corrupted docblocks where \n literals replaced actual newlines.
 *
 * @since 1.6033.2063
 */

declare(strict_types=1);

// Get the performance diagnostics directory
$performance_dir = dirname(__DIR__) . '/includes/diagnostics/tests/performance';

if (!is_dir($performance_dir)) {
    echo "Error: Directory {$performance_dir} not found\n";
    exit(1);
}

// Find all diagnostic files
$files = glob($performance_dir . '/class-diagnostic-*.php');

if (empty($files)) {
    echo "No diagnostic files found in {$performance_dir}\n";
    exit(1);
}

$fixed_count = 0;
$error_count = 0;

foreach ($files as $file) {
    $basename = basename($file);
    
    try {
        $content = file_get_contents($file);
        $original = $content;
        
        // Replace literal \n with nothing, but ONLY in comment lines (within docblocks)
        // Strategy: Only replace on lines that start with optional whitespace and *
        $lines = explode("\n", $content);
        $fixed_lines = [];
        
        foreach ($lines as $line) {
            // If line is a comment line (after initial /** and before closing */)
            if (preg_match('/^\s*\*/', $line)) {
                // Remove literal \n from comment lines
                $line = str_replace('\\n', '', $line);
            }
            $fixed_lines[] = $line;
        }
        
        $fixed_content = implode("\n", $fixed_lines);
        
        // Only write if changed
        if ($fixed_content !== $original) {
            if (file_put_contents($file, $fixed_content) !== false) {
                $fixed_count++;
                echo "✓ Fixed: {$basename}\n";
            } else {
                $error_count++;
                echo "✗ Error writing: {$basename}\n";
            }
        } else {
            echo "- No changes: {$basename}\n";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "✗ Error processing {$basename}: {$e->getMessage()}\n";
    }
}

echo "\n=== Summary ===\n";
echo "Total files processed: " . count($files) . "\n";
echo "Fixed: {$fixed_count}\n";
echo "Errors: {$error_count}\n";

if ($error_count > 0) {
    exit(1);
}

exit(0);
