<?php
declare(strict_types=1);
/**
 * Head Cleanup - RSD Link Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if RSD (Really Simple Discovery) link is enabled.
 *
 * Family: head-cleanup
 * Related: head-cleanup-emoji, head-cleanup-oembed, head-cleanup-shortlink
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Head_Cleanup_RSD extends Diagnostic_Base {

	protected static $slug         = 'head-cleanup-rsd';
	protected static $title        = 'RSD (Really Simple Discovery) Link';
	protected static $description  = 'Checks if WordPress RSD link is enabled and can be removed.';
	protected static $family       = 'head-cleanup';
	protected static $family_label = 'Head Cleanup Tasks';

	public static function check(): ?array {
		if ( ! self::is_rsd_enabled() ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The RSD link is legacy from the XML-RPC era and is unnecessary for modern WordPress sites. Removing it improves security and reduces page noise.', 'wpshadow' ),
			'category'     => 'security',
			'severity'     => 'low',
			'threat_level' => 18,
			'auto_fixable' => true,
			'family'       => self::$family,
			'family_label' => self::$family_label,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if RSD link is enabled
	 *
	 * @return bool
	 */
	private static function is_rsd_enabled(): bool {
		return has_action( 'wp_head', 'rsd_link' ) !== false;
	}
}
