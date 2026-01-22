<?php
declare(strict_types=1);
/**
 * Password Reset Token Reuse Diagnostic
 *
 * Philosophy: Authentication security - prevent token replay attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if password reset tokens are properly invalidated.
 */
class Diagnostic_Password_Reset_Token_Reuse extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if core password reset process is hooked
		$has_invalidation = has_action( 'password_reset', 'wp_password_change_notification' );
		
		// Check for custom password reset handlers that might not clear tokens
		global $wp_filter;
		$reset_handlers = array();
		
		if ( isset( $wp_filter['retrieve_password_message'] ) ) {
			foreach ( $wp_filter['retrieve_password_message']->callbacks as $priority => $callbacks ) {
				$reset_handlers = array_merge( $reset_handlers, array_keys( $callbacks ) );
			}
		}
		
		// If custom handlers exist, warn about potential token reuse
		if ( count( $reset_handlers ) > 1 ) {
			return array(
				'id'          => 'password-reset-token-reuse',
				'title'       => 'Custom Password Reset May Allow Token Reuse',
				'description' => 'Custom password reset handlers detected. Ensure reset tokens are deleted after use to prevent replay attacks. Tokens should be single-use only.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-password-reset/',
				'training_link' => 'https://wpshadow.com/training/password-reset-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
}
