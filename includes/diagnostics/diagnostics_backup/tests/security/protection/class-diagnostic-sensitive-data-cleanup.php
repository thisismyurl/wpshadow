<?php
/**
 * Sensitive Data Cleanup Diagnostic
 *
 * Detects leftover sensitive data (API keys, passwords,
 * credentials) that should be deleted after use.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Sensitive_Data_Cleanup Class
 *
 * Scans for sensitive data in code/options.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Sensitive_Data_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sensitive-data-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Data Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects exposed API keys and credentials';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'protection';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if sensitive data found, null otherwise.
	 */
	public static function check() {
		$sensitive_data = self::scan_for_sensitive_data();

		if ( empty( $sensitive_data['found'] ) ) {
			return null; // No sensitive data found
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of sensitive items */
				__( 'Found %d potential sensitive credentials in code/options. Each exposed = attacker can access service (steal funds, spam, delete data). Delete immediately.', 'wpshadow' ),
				count( $sensitive_data['found'] )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/sensitive-data-cleanup',
			'family'       => self::$family,
			'meta'         => array(
				'potential_credentials' => count( $sensitive_data['found'] ),
			),
			'details'      => array(
				'types_of_sensitive_data'         => array(
					'API Keys' => array(
						'Examples: Stripe, PayPal, Mailchimp',
						'Risk: Attacker charges transactions',
						'Action: Rotate immediately',
					),
					'Passwords' => array(
						'Database password in code',
						'Hosting control panel password',
						'Risk: Full account takeover',
						'Action: Change immediately',
					),
					'Tokens' => array(
						'OAuth tokens, JWT tokens',
						'Risk: Access services as you',
						'Action: Revoke + issue new',
					),
					'Private Keys' => array(
						'SSH keys, encryption keys',
						'Risk: Server/data access',
						'Action: Revoke immediately',
					),
				),
				'common_places_data_exposed'       => array(
					'wp-config.php' => array(
						'Example: Database password hardcoded',
						'Fix: Use environment variables',
						'Tool: define( \'DB_PASSWORD\', getenv( \'DB_PASSWORD\' ) );',
					),
					'Plugin Configuration' => array(
						'Plugin stored API key in option',
						'Example: Stripe secret key in option',
						'Visible: In wp-admin → Database',
					),
					'Theme Functions' => array(
						'Theme author left API key in code',
						'Visible: In GitHub, git history',
					),
					'Debug Output' => array(
						'var_dump() of config array',
						'Left in error handlers',
						'Visible in error logs',
					),
					'Database Dumps' => array(
						'Backup file in /wp-content/ or root',
						'Visible: Via direct file access',
						'Downloadable: If listed in directory',
					),
				),
				'cleanup_process'                 => array(
					'Step 1: Identify Exposed Data' => array(
						'Tool: Grep search (below)',
						'Search: WordPress_options table',
						'Search: wp-config.php',
						'Search: Plugin files',
					),
					'Step 2: Remove Data' => array(
						'Option: Delete from wp-admin',
						'Code: Remove from functions.php',
						'Config: Move to environment',
					),
					'Step 3: Rotate Credentials' => array(
						'Stripe: Revoke old key, issue new',
						'PayPal: Update API credentials',
						'Mailchimp: Generate new API key',
					),
					'Step 4: Verify' => array(
						'Test: Feature still works',
						'Confirm: New credentials active',
					),
				),
				'secure_credential_storage'       => array(
					'Environment Variables' => array(
						'wp-config.php: define( \'API_KEY\', getenv( \'STRIPE_API_KEY\' ) );',
						'Server: Set in Apache/Nginx config',
						'.env file: Use php-dotenv package',
						'Hosting: Many have UI for env vars',
					),
					'WordPress Options' => array(
						'Use: WordPress option storage',
						'Example: update_option( \'stripe_secret\', $key );',
						'Protect: Option name should not be obvious',
						'Hide: Use \'wpml_\' prefix convention',
					),
					'Third-Party Services' => array(
						'Store: AWS Secrets Manager, Vault',
						'Benefit: Professional key rotation',
					),
				),
				'credential_search_patterns'       => array(
					'API Key Patterns' => array(
						'Stripe: sk_live_, sk_test_',
						'Mailchimp: API key has UUID format',
						'Google: AIzaSy...',
					),
					'Database Patterns' => array(
						'contains: password, token, secret, key',
						'Ends with: _key, _secret, _token',
					),
					'Search Tools' => array(
						'grep -r "password =" includes/',
						'grep -r "api_key =" includes/',
						'grep -r "\\$\\{env" wp-config.php',
					),
				),
				'prevention_best_practices'        => array(
					__( '1. Never commit credentials to git' ),
					__( '2. Use .gitignore to exclude .env files' ),
					__( '3. Store in environment variables' ),
					__( '4. Rotate keys monthly' ),
					__( '5. Monitor git history for leaks' ),
					__( '6. Educate team: No hardcoding' ),
				),
			),
		);
	}

	/**
	 * Scan for sensitive data.
	 *
	 * @since  1.2601.2148
	 * @return array Sensitive data scan results.
	 */
	private static function scan_for_sensitive_data() {
		$found = array();

		// Check wp-config for obvious issues
		$wp_config = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config ) ) {
			$config_content = file_get_contents( $wp_config );

			// Simple pattern matching
			if ( preg_match( '/password.*=.*[\'"]([\w\-\.]+)[\'"]/i', $config_content ) ) {
				$found[] = 'wp-config.php may contain hardcoded password';
			}
		}

		// Check wp_options for suspicious options
		global $wpdb;
		$suspicious_options = $wpdb->get_results(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%_key%' OR option_name LIKE '%_token%' OR option_name LIKE '%secret%' LIMIT 20"
		);

		if ( ! empty( $suspicious_options ) ) {
			foreach ( $suspicious_options as $option ) {
				if ( false !== strpos( $option->option_name, '_key' ) ) {
					$found[] = 'Suspicious option: ' . $option->option_name;
				}
			}
		}

		return array(
			'found' => array_slice( $found, 0, 5 ), // Return first 5
		);
	}
}
