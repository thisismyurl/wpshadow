<?php
/**
 * Head Cleanup - Emoji Scripts Diagnostic
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
 * Check if emoji detection scripts are enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-oembed, head-cleanup-rsd, head-cleanup-shortlink
 */
class Diagnostic_Head_Cleanup_Emoji extends Diagnostic_Base {

	protected static $slug = 'head-cleanup-emoji';
	protected static $title = 'Emoji Detection Scripts';
	protected static $description = 'Checks if WordPress emoji detection scripts are enabled and can be removed.';
	protected static $family = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_emoji_enabled() ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Emoji detection scripts load on every page but are rarely needed. Removing them reduces requests and improves performance.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if emoji scripts are enabled
	 *
	 * @return bool
	 */
	private static function is_emoji_enabled(): bool {
		return has_action( 'wp_head', 'print_emoji_detection_script' ) !== false || has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) !== false;
	}
}
