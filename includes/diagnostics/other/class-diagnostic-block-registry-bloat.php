<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Block Registry Bloat (WP-335)
 *
 * Detects unused/enqueued block scripts/styles inflating payload.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_BlockRegistryBloat extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$unused_blocks = (int) get_transient('wpshadow_unused_block_assets');
		$block_asset_bytes = (int) get_transient('wpshadow_block_asset_bytes');

		if ($unused_blocks > 0 && $block_asset_bytes > 0) {
			return array(
				'id' => 'block-registry-bloat',
				'title' => sprintf(__('Unused block assets detected (%d blocks)', 'wpshadow'), $unused_blocks),
				'description' => __('Block scripts/styles are enqueued but unused on this page. Deregister unused blocks or enable selective asset loading.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'other',
				'kb_link' => 'https://wpshadow.com/kb/block-registry-bloat/',
				'training_link' => 'https://wpshadow.com/training/block-asset-optimization/',
				'auto_fixable' => false,
				'threat_level' => 50,
				'asset_bytes' => $block_asset_bytes,
			);
		}

		return null;
	}
    }
