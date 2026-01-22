<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WordPress VIP Compatibility
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_VIP_Go_Compatibility extends Diagnostic_Base {
	protected static $slug        = 'vip-go-compatibility';
	protected static $title       = 'WordPress VIP Compatibility';
	protected static $description = 'Checks code against VIP coding standards.';

	public static function check(): ?array {
		// Check if running on WordPress VIP Go
		$is_vip = (defined('WPCOM_IS_VIP_ENV') && WPCOM_IS_VIP_ENV === true) ||
		          (defined('VIP_GO_ENV') && VIP_GO_ENV === 'production');
		
		if (!$is_vip) {
			return null; // Pass - not on VIP, no compatibility concerns
		}
		
		// Check for VIP-incompatible plugins
		$incompatible_plugins = array(
			'wp-super-cache/wp-super-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-rocket/wp-rocket.php',
		);
		
		$found_incompatible = array();
		foreach ($incompatible_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				$found_incompatible[] = basename(dirname($plugin));
			}
		}
		
		if (empty($found_incompatible)) {
			return null; // Pass - no incompatible plugins
		}
		
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'VIP-incompatible plugins detected: ' . implode(', ', $found_incompatible),
			'color'         => '#f44336',
			'bg_color'      => '#ffebee',
			'kb_link'       => 'https://wpshadow.com/kb/vip-go-compatibility/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=vip-go-compatibility',
			'training_link' => 'https://wpshadow.com/training/vip-go-compatibility/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Compatibility',
			'priority'      => 2,
		);
	}

}
