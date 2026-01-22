<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Jetpack Integration Health
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Jetpack_Integration extends Diagnostic_Base {
	protected static $slug        = 'jetpack-integration';
	protected static $title       = 'Jetpack Integration Health';
	protected static $description = 'Monitors Jetpack feature functionality.';

	public static function check(): ?array {
		// Check if Jetpack is active
		if (!is_plugin_active('jetpack/jetpack.php')) {
			return null; // Pass - Jetpack not installed, no concern
		}
		
		// Check Jetpack connection status
		if (class_exists('Jetpack') && method_exists('Jetpack', 'is_connection_ready')) {
			if (Jetpack::is_connection_ready()) {
				return null; // Pass - Jetpack connected
			}
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'Jetpack installed but not connected to WordPress.com.',
				'color'         => '#ff9800',
				'bg_color'      => '#fff3e0',
				'kb_link'       => 'https://wpshadow.com/kb/jetpack-integration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=jetpack-integration',
				'training_link' => 'https://wpshadow.com/training/jetpack-integration/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Integration',
				'priority'      => 2,
			);
		}
		
		return null;
	}

}
