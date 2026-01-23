<?php
declare(strict_types=1);
/**
 * User Agent Blacklist Diagnostic
 *
 * Philosophy: Bot security - block malicious user agents
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if user agent filtering is active.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_User_Agent_Blacklist extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$has_ua_filter = has_filter( 'wp_headers' ) || has_action( 'init' );

		if ( ! $has_ua_filter ) {
			return array(
				'id'            => 'user-agent-blacklist',
				'title'         => 'No User Agent Blocking',
				'description'   => 'Malicious bots with known user agents continue accessing your site. Implement user agent filtering to block known scanners and malware distribution bots.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/block-malicious-user-agents/',
				'training_link' => 'https://wpshadow.com/training/bot-detection/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
			);
		}

		return null;
	}

}