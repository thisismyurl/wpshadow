<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Over-Broad Catch Blocks
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-broad-catch
 * Training: https://wpshadow.com/training/code-standards-broad-catch
 */
class Diagnostic_Code_CODE_STANDARDS_BROAD_CATCH {
    public static function check() {
        return [
            'id' => 'code-standards-broad-catch',
            'title' => __('Over-Broad Catch Blocks', 'wpshadow'),
            'description' => __('Detects catch(Exception) instead of specific exception types.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-broad-catch',
            'training_link' => 'https://wpshadow.com/training/code-standards-broad-catch',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }
}

