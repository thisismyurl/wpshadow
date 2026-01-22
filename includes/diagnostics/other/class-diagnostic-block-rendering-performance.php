<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Rendering Performance (WP-ADV-005)
 * 
 * Block Rendering Performance diagnostic
 * Philosophy: Educate (#5) - Which blocks are slow.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_DiagnosticBlockRenderingPerformance extends Diagnostic_Base {
    public static function check(): ?array {
        $slow_blocks = get_transient('wpshadow_slow_block_list');
        $slow_blocks = is_array($slow_blocks) ? $slow_blocks : array();

        if (!empty($slow_blocks)) {
            return array(
                'id' => 'block-rendering-performance',
                'title' => __('Slow block rendering detected', 'wpshadow'),
                'description' => __('Some blocks render slowly on the server or client. Consider caching rendered output or replacing heavy blocks.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'other',
                'kb_link' => 'https://wpshadow.com/kb/block-rendering-performance/',
                'training_link' => 'https://wpshadow.com/training/gutenberg-performance/',
                'auto_fixable' => false,
                'threat_level' => 50,
                'slow_blocks' => $slow_blocks,
            );
        }

        return null;
    }
}
