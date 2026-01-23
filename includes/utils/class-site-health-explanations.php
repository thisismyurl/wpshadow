<?php
/**
 * Site Health Explanations
 * 
 * Adds user-friendly explanations to WordPress Site Health issues
 * with links to WPShadow knowledge base documentation.
 *
 * @package WPShadow
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Site_Health_Explanations {

	/**
	 * Initialize the Site Health explanations system.
	 */
	public static function init() {
		// Filter the Site Health test results to add explanations and KB links
		add_filter( 'site_status_test_result', array( __CLASS__, 'add_explanations' ) );
	}

	/**
	 * Add user-friendly explanations to Site Health test results.
	 *
	 * @param array $result The Site Health test result.
	 * @return array Modified result with our explanations.
	 */
	public static function add_explanations( $result ) {
		if ( ! is_array( $result ) ) {
			return $result;
		}

		// Get the test name from the result
		$test = isset( $result['test'] ) ? $result['test'] : '';
		
		// Map of WordPress Site Health tests to our explanations
		$explanations = self::get_explanations();

		// If we have an explanation for this test, add it
		if ( isset( $explanations[ $test ] ) ) {
			$explanation = $explanations[ $test ];
			
			// Append our explanation to the description
			if ( isset( $result['description'] ) ) {
				$result['description'] .= '<div class="wpshadow-site-health-explanation">' . $explanation . '</div>';
			}
		}

		return $result;
	}

	/**
	 * Get map of WordPress Site Health tests to WPShadow explanations.
	 *
	 * @return array Map of test names to explanations.
	 */
	private static function get_explanations() {
		$kb_base = admin_url( 'admin.php?page=wpshadow-help&category=site-health' );

		return array(
			// REST API tests
			'rest_api_test' => sprintf(
				'<p><strong>Why this matters:</strong> The REST API is how modern WordPress applications communicate with your site. If it\'s blocked, some features won\'t work properly.</p>'
				. '<p><a href="%s#rest-api" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Loopback tests
			'loopback_requests' => sprintf(
				'<p><strong>Why this matters:</strong> Your server needs to "talk to itself" to power scheduled tasks, updates, and background processes. If this is blocked, your site can\'t perform important maintenance automatically.</p>'
				. '<p><a href="%s#loopback-requests" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// PHP Version test
			'php_version' => sprintf(
				'<p><strong>Why this matters:</strong> Older PHP versions are slower and have security vulnerabilities. Modern WordPress and plugins require at least PHP 7.4, but 8.0+ is recommended for better performance.</p>'
				. '<p><a href="%s#php-version" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// SSL/HTTPS test
			'ssl_support' => sprintf(
				'<p><strong>Why this matters:</strong> SSL encrypts data between your visitors and your site. It\'s required by modern browsers, improves SEO, and protects sensitive information like passwords and payment data.</p>'
				. '<p><a href="%s#ssl-https" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// WordPress updates test
			'wordpress_version' => sprintf(
				'<p><strong>Why this matters:</strong> WordPress updates contain security patches, bug fixes, and new features. Staying current protects your site from hackers and ensures compatibility with plugins and themes.</p>'
				. '<p><a href="%s#wordpress-updates" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Plugin updates test
			'plugin_version' => sprintf(
				'<p><strong>Why this matters:</strong> Outdated plugins are a common target for hackers. Keeping plugins updated ensures you get security patches, new features, and compatibility improvements.</p>'
				. '<p><a href="%s#plugin-updates" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Theme updates test
			'theme_version' => sprintf(
				'<p><strong>Why this matters:</strong> Theme updates provide security fixes and improve compatibility with the latest WordPress versions and browsers.</p>'
				. '<p><a href="%s#theme-updates" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Database test
			'database' => sprintf(
				'<p><strong>Why this matters:</strong> Your database stores all your content, settings, and user information. If there are issues, your site could slow down or even lose data.</p>'
				. '<p><a href="%s#database" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Backup test
			'backup_state' => sprintf(
				'<p><strong>Why this matters:</strong> Regular backups protect you from data loss due to hacking, server failures, or accidental deletion. They\'re your insurance policy.</p>'
				. '<p><a href="%s#backups" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// File permissions test
			'file_integrity' => sprintf(
				'<p><strong>Why this matters:</strong> Incorrect file permissions can allow hackers to modify your site or prevent WordPress from updating itself. This is a critical security concern.</p>'
				. '<p><a href="%s#file-permissions" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Plugin count test (custom)
			'plugin_count' => sprintf(
				'<p><strong>Why this matters:</strong> Every plugin adds code to your site. Too many plugins can slow down your site, increase security surface area, and cause conflicts between plugins.</p>'
				. '<p><a href="%s#plugin-count" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// ActivePlugins in debug tab
			'debug_plugins' => sprintf(
				'<p><strong>Why this matters:</strong> Knowing which plugins are active helps identify conflicts and performance issues. Disable unused plugins to improve speed and security.</p>'
				. '<p><a href="%s#active-plugins" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Multisite test
			'site_environment_type' => sprintf(
				'<p><strong>Why this matters:</strong> Running your site in "production" mode (not "development" or "staging") ensures security settings are properly enforced and errors aren\'t exposed to visitors.</p>'
				. '<p><a href="%s#environment-type" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Debug mode test
			'debug_mode_enabled' => sprintf(
				'<p><strong>Why this matters:</strong> Debug mode should only be enabled temporarily for troubleshooting. Leaving it on in production exposes sensitive information to potential attackers.</p>'
				. '<p><a href="%s#debug-mode" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Object cache test
			'object_cache_status' => sprintf(
				'<p><strong>Why this matters:</strong> Object caching stores frequently-used data in memory, dramatically improving site speed. WordPress can work without it, but your site will be slower.</p>'
				. '<p><a href="%s#object-cache" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Memory limit test
			'memory_limit_status' => sprintf(
				'<p><strong>Why this matters:</strong> PHP memory determines how much data WordPress can process at once. Low memory causes timeouts, broken features, and plugin conflicts. 256MB is recommended.</p>'
				. '<p><a href="%s#memory-limit" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Two Factor Authentication test
			'two_factor_authentication' => sprintf(
				'<p><strong>Why this matters:</strong> Two-factor authentication (2FA) adds an extra layer of security to admin accounts. Even if a password is compromised, the account stays protected.</p>'
				. '<p><a href="%s#2fa" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Scheduled events test
			'scheduled_events' => sprintf(
				'<p><strong>Why this matters:</strong> WordPress scheduled tasks power automatic updates, backups, email notifications, and other background processes. If this is broken, these critical tasks won\'t run.</p>'
				. '<p><a href="%s#scheduled-events" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),

			// Commenting test
			'comments_enabled' => sprintf(
				'<p><strong>Why this matters:</strong> Comments allow visitors to engage with your content, but they can also attract spam. Make sure you have filters in place if comments are enabled.</p>'
				. '<p><a href="%s#comments" target="_blank" rel="noopener noreferrer">Learn more in our knowledge base →</a></p>',
				esc_url( $kb_base )
			),
		);
	}
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\\Site_Health_Explanations', 'init' ) );
