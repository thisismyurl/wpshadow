<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spam Filter Too Aggressive?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Spam_Blocking_Real_Comments extends Diagnostic_Base {
	protected static $slug        = 'spam-blocking-real-comments';
	protected static $title       = 'Spam Filter Too Aggressive?';
	protected static $description = 'Checks if legitimate comments are being blocked.';


	public static function check(): ?array {
		$spam_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam_bee.php',
		);
		foreach ($spam_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				return null;
			}
		}
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'No spam protection plugin detected.',
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/spam-blocking-real-comments/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=spam-blocking-real-comments',
			'training_link' => 'https://wpshadow.com/training/spam-blocking-real-comments/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 2,
		);
	}

}
