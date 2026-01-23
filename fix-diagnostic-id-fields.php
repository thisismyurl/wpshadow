#!/usr/bin/env php
<?php
/**
 * Automated 'id' Field Injector
 * 
 * Adds missing 'id' field to diagnostic return arrays
 * Extracts slug from filename or uses static $slug property
 */

declare(strict_types=1);

class Diagnostic_ID_Injector {
    private int $fixed = 0;
    private int $skipped = 0;
    private array $errors = [];
    
    public function process_all_diagnostics(): void {
        $files = $this->get_diagnostic_files_without_id();
        
        echo "🔧 Processing " . count($files) . " diagnostics missing 'id' field\n";
        echo "═══════════════════════════════════════════════════════\n\n";
        
        foreach ($files as $file) {
            $this->process_file($file);
        }
        
        $this->print_summary();
    }
    
    private function get_diagnostic_files_without_id(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('/workspaces/wpshadow/includes/diagnostics'),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isFile() || strpos($file->getFilename(), 'class-diagnostic-') !== 0) {
                continue;
            }
            
            $content = file_get_contents($file->getPathname());
            
            // Skip if no check() method or already has 'id'
            if (strpos($content, 'public static function check') === false ||
                preg_match("/['\"]id['\"]\s*=>/", $content)) {
                continue;
            }
            
            $files[] = $file->getPathname();
        }
        
        return $files;
    }
    
    private function process_file(string $file_path): void {
        $content = file_get_contents($file_path);
        if (!$content) {
            $this->skipped++;
            return;
        }
        
        // Extract slug from class properties or filename
        $slug = $this->extract_slug($file_path, $content);
        
        if (!$slug) {
            $this->skipped++;
            echo "⏭️  Skipped: " . basename($file_path) . " (couldn't extract slug)\n";
            return;
        }
        
        // Add 'id' field to first return array in check() method
        $updated_content = $this->inject_id_field($content, $slug);
        
        if ($updated_content === $content) {
            $this->skipped++;
            echo "⏭️  Skipped: " . basename($file_path) . " (no return array found)\n";
            return;
        }
        
        // Write back
        if (file_put_contents($file_path, $updated_content) !== false) {
            $this->fixed++;
            echo "✅ Fixed: " . basename($file_path) . " → id: '$slug'\n";
        } else {
            $this->errors[] = basename($file_path) . " (write failed)";
            echo "❌ Error: " . basename($file_path) . " (write failed)\n";
        }
    }
    
    private function extract_slug(string $file_path, string $content): ?string {
        // Try to get from static $slug property first
        if (preg_match("/protected\s+static\s+\$slug\s*=\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            return $matches[1];
        }
        
        // Fall back to filename-based slug
        $filename = basename($file_path, '.php');
        $slug = str_replace('class-diagnostic-', '', $filename);
        
        if (!empty($slug)) {
            return $slug;
        }
        
        return null;
    }
    
    private function inject_id_field(string $content, string $slug): string {
        // Find and inject into return array statements in check() method
        // We need to find the first return array after "public static function check"
        
        if (!preg_match('/public\s+static\s+function\s+check.*?\{/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
            return $content;
        }
        
        $check_start = $matches[0][1] + strlen($matches[0][0]);
        $check_section = substr($content, $check_start);
        
        // Find the first "return array(" in this function
        if (!preg_match('/return\s+array\s*\(\s*(?!\s*\))/', $check_section, $match, PREG_OFFSET_CAPTURE)) {
            return $content;
        }
        
        $return_pos = $check_start + $match[0][1];
        $after_return_array = $return_pos + strlen($match[0][0]);
        
        // Check if next line already has 'id' (safety check)
        $next_200 = substr($content, $after_return_array, 200);
        if (preg_match("/['\"]id['\"]\s*=>/", $next_200)) {
            return $content; // Already has 'id'
        }
        
        // Insert 'id' field after "return array("
        // Detect indentation from next line
        if (preg_match("/\n(\s+)['\"]/", $next_200, $indent_match)) {
            $indent = $indent_match[1];
        } else {
            $indent = "\t\t\t";
        }
        
        $id_field = $indent . "'id' => '$slug',\n";
        
        $new_content = substr_replace(
            $content,
            $id_field,
            $after_return_array,
            0
        );
        
        return $new_content;
    }
    
    private function print_summary(): void {
        echo "\n";
        echo "╔═══════════════════════════════════╗\n";
        echo "║       INJECTION COMPLETE          ║\n";
        echo "╠═══════════════════════════════════╣\n";
        printf("║ ✅ Fixed:    %3d                  ║\n", $this->fixed);
        printf("║ ⏭️  Skipped:  %3d                  ║\n", $this->skipped);
        printf("║ ❌ Errors:   %3d                  ║\n", count($this->errors));
        echo "╚═══════════════════════════════════╝\n";
        
        if (!empty($this->errors)) {
            echo "\nErrors:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
    }
}

$injector = new Diagnostic_ID_Injector();
$injector->process_all_diagnostics();
