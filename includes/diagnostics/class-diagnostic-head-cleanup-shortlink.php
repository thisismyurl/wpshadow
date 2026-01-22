<?php
/**
 * Head Cleanup - WordPress Shortlink Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress shortlink is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-rsd
 */
class Diagnostic_Head_Cleanup_Shortlink extends Diagnostic_Base {

	protected static $slug = 'head-cleanup-shortlink';
	protected static $title = 'WordPress Shortlink';
	protected static $description = 'Checks if WordPress shortlink functionality is enabled and can be removed.';
	protected static $family = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_shortlink_enabled() ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The WordPress shortlink feature is rarely used in modern sites. Removing it reduces page headers and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 10,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if shortlink is enabled
	 *
	 * @return bool
	 */
	private static function is_shortlink_enabled(): bool {
		return has_action( 'wp_head', 'wp_shortlink_wp_head' ) !== false;
	}
}
