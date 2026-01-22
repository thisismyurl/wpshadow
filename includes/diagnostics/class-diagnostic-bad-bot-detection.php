<?php declare(strict_types=1);
/**
 * Bad Bot Detection Diagnostic
 *
 * Philosophy: Bot security - identify malicious crawlers
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for bad bot detection.
 */
class Diagnostic_Bad_Bot_Detection {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$bot_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $bot_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		return array(
			'id'          => 'bad-bot-detection',
			'title'       => 'No Bot Detection/Blocking',
			'description' => 'Scrapers, vulnerability scanners, and malware distribution bots are accessing your site undetected. Implement bot detection to filter automated attacks.',
			'severity'    => 'medium',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/detect-malicious-bots/',
			'training_link' => 'https://wpshadow.com/training/bot-blocking/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
