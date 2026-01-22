<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Silent Exception Catches
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-silent-catch
 * Training: https://wpshadow.com/training/code-standards-silent-catch
 */
class Diagnostic_Code_CODE_STANDARDS_SILENT_CATCH {
    public static function check() {
        return [
            'id' => 'code-standards-silent-catch',
            'title' => __('Silent Exception Catches', 'wpshadow'),
            'description' => __('Flags catch blocks that suppress errors without logging.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-silent-catch',
            'training_link' => 'https://wpshadow.com/training/code-standards-silent-catch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

