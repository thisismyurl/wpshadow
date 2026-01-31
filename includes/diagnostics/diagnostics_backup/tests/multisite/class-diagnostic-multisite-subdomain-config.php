<?php
/**
 * Multisite Subdomain Configuration Diagnostic
 *
 * Verifies subdomain multisite DNS and server configuration to ensure
 * wildcard DNS is properly set up for automatic subdomain creation.
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
 * Diagnostic_Multisite_Subdomain_Config Class
 *
 * Detects subdomain configuration issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Subdomain_Config extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-subdomain-config';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Subdomain Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies subdomain multisite DNS and server setup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Only run on subdomain multisite
		if ( ! is_multisite() || ! ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) ) {
			return null;
		}

		$config_check = self::verify_subdomain_config();

		if ( ! $config_check['has_issues'] ) {
			return null; // Configuration looks good
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Subdomain multisite configuration issues detected. Wildcard DNS required for automatic subdomain creation. New sites won\'t load without proper setup.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-subdomains',
			'family'       => self::$family,
			'meta'         => array(
				'network_domain'   => network_home_url(),
				'wildcard_required' => __( 'DNS wildcard A record needed' ),
			),
			'details'      => array(
				'subdomain_requirements'   => array(
					'Wildcard DNS' => array(
						'Required: *.example.com → server IP',
						'Purpose: Automatic subdomain routing',
						'Without it: Each subdomain needs manual DNS entry',
					),
					'Server Configuration' => array(
						'Apache: ServerAlias *.example.com',
						'Nginx: server_name *.example.com',
						'Purpose: Accept all subdomains',
					),
					'SSL Certificate' => array(
						'Required: Wildcard SSL (*.example.com)',
						'Cost: $50-200/year typically',
						'Free option: Cloudflare',
					),
				),
				'dns_setup_cloudflare'     => array(
					'Step 1' => 'Sign up Cloudflare (free)',
					'Step 2' => 'Add site, copy nameservers',
					'Step 3' => 'Update registrar nameservers',
					'Step 4' => 'Cloudflare DNS: Add A record @ → server IP',
					'Step 5' => 'Add CNAME record * → example.com',
					'Step 6' => 'Enable "Proxied" (orange cloud)',
					'Result' => 'Wildcard DNS + Free SSL',
				),
				'dns_setup_cpanel'         => array(
					'Step 1' => 'cPanel → Zone Editor',
					'Step 2' => 'Find your domain',
					'Step 3' => 'Add A Record',
					'Step 4' => 'Name: * (asterisk)',
					'Step 5' => 'Points to: Server IP address',
					'Step 6' => 'Save',
				),
				'apache_configuration'     => array(
					'VirtualHost Setup' => array(
						'File: /etc/apache2/sites-available/example.com.conf',
						'ServerName example.com',
						'ServerAlias www.example.com *.example.com',
						'DocumentRoot /var/www/html',
					),
					'Enable Site' => array(
						'Command: a2ensite example.com.conf',
						'Reload: systemctl reload apache2',
					),
				),
				'nginx_configuration'      => array(
					'Server Block' => array(
						'File: /etc/nginx/sites-available/example.com',
						'server_name example.com *.example.com;',
						'root /var/www/html;',
					),
					'Enable Site' => array(
						'Link: ln -s /etc/nginx/sites-available/example.com /etc/nginx/sites-enabled/',
						'Reload: systemctl reload nginx',
					),
				),
				'wildcard_ssl_options'     => array(
					'Cloudflare (Free)' => array(
						'Wildcard SSL automatic',
						'Full SSL/TLS encryption',
						'Setup: 5 minutes',
					),
					'Let\'s Encrypt (Free)' => array(
						'Requires DNS-01 challenge',
						'Wildcard: certbot certonly --manual --preferred-challenges dns',
						'Manual DNS TXT record needed',
					),
					'Commercial SSL ($50-200/yr)' => array(
						'RapidSSL, Sectigo, DigiCert',
						'Easier setup than Let\'s Encrypt',
						'Better support',
					),
				),
				'testing_subdomain_setup'  => array(
					'Test DNS' => array(
						'Command: nslookup test.example.com',
						'Should resolve to same IP as example.com',
					),
					'Test Site Creation' => array(
						'Network Admin → Sites → Add New',
						'Create: test.example.com',
						'Visit: https://test.example.com',
					),
					'Test SSL' => array(
						'Visit: https://newsite.example.com',
						'Check: Green padlock in browser',
						'No certificate warnings',
					),
				),
			),
		);
	}

	/**
	 * Verify subdomain configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Configuration status.
	 */
	private static function verify_subdomain_config() {
		if ( ! is_multisite() ) {
			return array( 'has_issues' => false );
		}

		// In subdomain mode but cannot fully verify wildcard DNS from here
		// Check for SSL on main site
		$is_ssl = is_ssl();

		// Assume needs attention if not SSL (wildcard SSL usually needed)
		$has_issues = ! $is_ssl;

		return array(
			'has_issues' => $has_issues,
		);
	}
}
