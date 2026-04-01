<?php
/**
 * Findings Cache Helper Functions
 *
 * Provides utilities for clearing cached diagnostic findings.
 *
 * @package    WPShadow
 * @subpackage Utils\Helpers
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear cached diagnostic findings.
 *
 * Clears both transient- and option-based caches to ensure
 * fresh results after treatments or major configuration changes.
 *
 * @since 0.6093.1200
 * @return void
 */
function wpshadow_clear_findings_cache(): void {
	delete_transient( 'wpshadow_findings_cache' );
	delete_option( 'wpshadow_findings_cache' );
}
