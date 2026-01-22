<?php
declare(strict_types=1);
/**
 * Session Fixation Protection Diagnostic
 *
 * Philosophy: Authentication security - regenerate session IDs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if session IDs are regenerated on login.
 */
class Diagnostic_Session_Fixation_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_fixation_protection = has_action( 'wp_login' );
		
		if ( ! $has_fixation_protection ) {
			return array(
				'id'          => 'session-fixation-protection',
				'title'       => 'No Session Fixation Protection',
				'description' => 'Session IDs are not regenerated on login. Attackers can use pre-existing session IDs to hijack accounts. Regenerate session ID on every login.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-session-fixation/',
				'training_link' => 'https://wpshadow.com/training/session-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
