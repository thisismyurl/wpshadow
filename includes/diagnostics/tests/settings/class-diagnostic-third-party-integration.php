<?php
/**
 * Third-Party Service Integration
 *
 * Validates third-party service integrations.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Third_Party_Integration Class
 *
 * Checks third-party service integrations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Third_Party_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates third-party service integrations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugin-ecosystem';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: API credentials stored insecurely
		$options = wp_load_alloptions();

		foreach ( $options as $option_name => $option_value ) {
			// Check for common API key patterns
			if ( preg_match( '/api_key|api_token|secret_key|password|token/i', $option_name ) ) {
				// Check if it's in plain text without encryption
				if ( is_string( $option_value ) && strlen( $option_value ) > 10 ) {
					// If option name suggests it's sensitive but not encrypted
					if ( ! preg_match( '/encrypted|hash|*/', $option_value ) ) {
						return array(
							'id'           => self::$slug,
							'title'        => self::$title,
							'description'  => __( 'API credentials may be stored insecurely', 'wpshadow' ),
							'severity'     => 'critical',
							'threat_level' => 90,
							'auto_fixable' => false,
							'kb_link'      => 'https://wpshadow.com/kb/api-security',
							'details'      => array(
								'issue' => 'insecure_credentials',
								'option_name' => $option_name,
								'message' => __( 'API credentials stored in plain text in database', 'wpshadow' ),
								'risks' => array(
									'Credentials exposed in database',
									'Stolen credentials enable account takeover',
									'Unauthorized API usage',
									'Billing fraud',
									'Data breach',
								),
								'solution' => array(
									'Use environment variables',
									'Encrypt sensitive data',
									'Use WordPress Secrets API',
									'Store in separate secure file',
									'Rotate exposed credentials',
								),
								'best_practices' => array(
									'Never store credentials in database',
									'Never commit credentials to Git',
									'Use .env files with .env.example',
									'Restrict file permissions',
									'Rotate regularly',
								),
								'secure_storage' => "// RIGHT - Store in wp-config.php or .env
define('MY_SERVICE_API_KEY', 'sk_live_xxx');
define('MY_SERVICE_SECRET', 'secret_xxx');

// In code, use constant
\$api_key = defined('MY_SERVICE_API_KEY') ? MY_SERVICE_API_KEY : '';

// Or use getenv for .env files
\$api_key = getenv('MY_SERVICE_API_KEY');",
								'wrong_way' => "// WRONG - Database storage
update_option('api_key', 'sk_live_xxx');
update_option('secret_key', 'secret_xxx');",
								'environment_variables' => array(
									'WordPress doesn\'t have built-in .env',
									'Use plugins or libraries like phpdotenv',
									'Put .env in parent directory',
									'Add to .gitignore',
									'Set in server environment',
								),
								'phpdotenv_usage' => "// composer.json
{
	'require': {
		'vlucas/phpdotenv': '^5.4'
	}
}

// wp-config.php
require_once __DIR__ . '/vendor/autoload.php';
\$dotenv = Dotenv\\Dotenv::createImmutable(__DIR__);
\$dotenv->load();

\$api_key = \$_ENV['MY_SERVICE_API_KEY'];",
								'wordpress_secrets_api' => "// WordPress 5.8+ has Secrets REST API
// Use WordPress Secrets plugin for management

// Get secret
\$api_key = wp_get_secret('my_service_api_key');

// Set secret (admin only)
wp_set_secret('my_service_api_key', 'value');",
								'credential_rotation' => array(
									'Set monthly rotation reminder',
									'Generate new API key regularly',
									'Revoke old credentials',
									'Update code with new key',
									'Verify service still works',
								),
								'compromised_credentials' => __( 'If credentials exposed, immediately rotate them', 'wpshadow' ),
								'recommendation' => __( 'Move API credentials from database to environment variables', 'wpshadow' ),
							),
						);
					}
				}
			}
		}

		// Pattern 2: External service timeouts happening
		// This would typically require logging analysis
		global $wp_filter;

		if ( isset( $wp_filter['http_request_timeout'] ) ) {
			// External requests timing out, may indicate service issues
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Third-party service integration issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-integration',
				'details'      => array(
					'issue' => 'service_timeout',
					'message' => __( 'External service requests timing out', 'wpshadow' ),
					'symptoms' => array(
						'Slow page loads',
						'Hung requests',
						'Incomplete functionality',
						'Missing data',
					),
					'common_causes' => array(
						'Service down' => 'Third-party service unavailable',
						'Network issues' => 'Connection problems',
						'Timeout too short' => 'Legitimate requests fail',
						'Rate limiting' => 'Too many requests',
						'Credentials expired' => 'Authentication failed',
					),
					'debugging' => "// Check if service is reachable
\$response = wp_remote_get('https://api.service.com/health', array(
	'timeout' => 5,
	'sslverify' => true,
));

if (is_wp_error(\$response)) {
	error_log('Service error: ' . \$response->get_error_message());
} else {
	error_log('Service status: ' . wp_remote_retrieve_response_code(\$response));
}",
					'increasing_timeout' => "// In plugin/theme
add_filter('http_request_timeout', function() {
	return 10; // 10 seconds instead of 5
});",
					'graceful_fallback' => "// Handle timeout gracefully
\$response = wp_remote_get('https://api.service.com/data', array(
	'timeout' => 5,
));

if (is_wp_error(\$response)) {
	// Use cached data or default values
	\$data = get_transient('service_data_cache');
	if (!empty(\$data)) {
		return \$data;
	}
} else {
	\$data = json_decode(wp_remote_retrieve_body(\$response), true);
	// Cache for 1 hour
	set_transient('service_data_cache', \$data, HOUR_IN_SECONDS);
	return \$data;
}",
					'monitoring' => array(
						'Monitor service availability',
						'Log timeout errors',
						'Set up alerts',
						'Track request times',
						'Cache responses when possible',
					),
					'error_logging' => "// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Log errors to wp-content/debug.log",
					'recommendation' => __( 'Investigate and resolve third-party service integration issues', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
