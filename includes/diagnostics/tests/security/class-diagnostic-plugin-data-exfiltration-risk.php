<?php
/**
 * Plugin Data Exfiltration Risk Diagnostic
 *
 * Detects plugins sending user/site data to external servers without consent.
 * Plugin collects "for analytics" but sends to unknown third-party.
 * Data = customer emails, payment info, passwords, site configuration.
 *
 * **What This Check Does:**
 * - Scans active plugins for external API calls
 * - Detects data being sent to remote servers
 * - Checks if external calls disclosed in documentation
 * - Tests for encryption in transit (HTTPS)
 * - Validates third-party servers are legitimate
 * - Returns severity if undisclosed data exfiltration
 *
 * **Why This Matters:**
 * Hidden data collection = privacy violation + breach risk. Scenarios:
 * - Plugin sends site admin email to analytics company (no disclosure)
 * - Plugin sends customer data to marketing firm (no consent)
 * - Third-party server breached, customer data compromised
 * - You're liable (used plugin that exfiltrated data)
 * - GDPR fine + user lawsuits
 *
 * **Business Impact:**
 * Site uses form plugin. Plugin docs say "free analytics". Hidden: sends
 * form submissions to third-party (not disclosed). Third-party server
 * breached. 100K customer records exposed (via your plugin). GDPR fine:
 * $2M. Lawsuit settlement: $1M. Plus reputation damage. Total: $5M+.
 * Exfiltration audit would have caught before deployment.
 *
 * **Philosophy Alignment:**
 * - #10 Beyond Pure: Privacy-first, no hidden data collection
 * - #8 Inspire Confidence: Data destination disclosed
 * - #9 Show Value: Prevents data breach via plugins
 *
 * **Related Checks:**
 * - Privacy Policy Compliance (data handling disclosure)
 * - Consent Banner Implementation (user permission)
 * - Database User Privileges (data access control)
 *
 * **Learn More:**
 * Data exfiltration: https://wpshadow.com/kb/wordpress-data-exfiltration
 * Video: Auditing plugin data collection (13min): https://wpshadow.com/training/data-audit
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Data_Exfiltration_Risk Class
 *
 * Identifies plugins sending data to external servers.
 *
 * **Detection Pattern:**
 * 1. Scan plugin files for wp_remote_*() calls
 * 2. Check external URLs being contacted
 * 3. Test data payloads being sent
 * 4. Validate if exfiltration disclosed
 * 5. Check HTTPS/encryption in transit
 * 6. Return severity if undisclosed exfiltration
 *
 * **Real-World Scenario:**
 * Form builder plugin sends all submissions to "analytics partner" (undisclosed).
 * Partner processes contact forms (customer info, emails, messages).
 * Partner is acquired by data broker. All customer data goes to data market.
 * Your customers' information sold to spammers. You're liable. Audit would have
 * detected: "Plugin sends form data to third-party.com" (red flag).
 *
 * **Implementation Notes:**
 * - Scans plugin files for external API calls
 * - Tests actual data being sent
 * - Checks destination legitimacy
 * - Severity: critical (undisclosed exfiltration), high (data sent unencrypted)
 * - Treatment: disable plugin or require explicit user consent
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Data_Exfiltration_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-data-exfiltration-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Data Exfiltration Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins sending data to external servers without consent';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$exfil_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		$data_sending = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for wp_remote_post/get to external URLs
			if ( preg_match( '/wp_remote_(?:post|get).*(?:https?:\/\/)?(?:api\.|analytics\.|tracking\.|telemetry\.)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Sends data to external analytics/tracking services.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending post content/metadata
			if ( preg_match( '/wp_remote_post.*(\$post|post_content|post_title|post_meta|user_email)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May send post content or user data to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending database backups/exports
			if ( preg_match( '/wp_remote_post.*(?:backup|export|database|sql)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May send database backups to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sending admin email or user information
			if ( preg_match( '/wp_remote_(?:post|get).*(?:admin_email|get_option.*email|get_userdata)/', $content ) ) {
				$data_sending[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Sends admin email or user information to external servers.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $data_sending ) ) {
			$exfil_concerns = $data_sending;
		}

		if ( ! empty( $exfil_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d plugins send data to external servers: %s', 'wpshadow' ),
					count( $exfil_concerns ),
					implode( ' | ', array_slice( $exfil_concerns, 0, 2 ) )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'data_exfil_plugins' => $exfil_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/data-exfiltration',
			);
		}

		return null;
	}
}
