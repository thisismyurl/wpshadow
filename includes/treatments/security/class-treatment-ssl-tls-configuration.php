<?php
/**
 * SSL/TLS Configuration Treatment
 *
 * Analyzes SSL certificate and HTTPS configuration.
 *
 * @since   1.6033.2145
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL/TLS Configuration Treatment
 *
 * Evaluates SSL certificate validity and security configuration.
 *
 * @since 1.6033.2145
 */
class Treatment_SSL_TLS_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-tls-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SSL/TLS Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes SSL certificate and HTTPS configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site uses HTTPS
		$is_ssl = is_ssl();
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );
		$uses_https = strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0;

		// Check for SSL forcing plugins
		$ssl_plugins = array(
			'really-simple-ssl/rlrsssl-really-simple-ssl.php' => 'Really Simple SSL',
			'wp-force-ssl/wp-force-ssl.php'                   => 'WP Force SSL',
		);

		$active_ssl_plugin = null;
		foreach ( $ssl_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_ssl_plugin = $name;
				break;
			}
		}

		// Check HSTS header
		$has_hsts = false;
		if ( function_exists( 'apache_response_headers' ) ) {
			$headers = apache_response_headers();
			$has_hsts = isset( $headers['Strict-Transport-Security'] );
		}

		// Generate findings if not using HTTPS
		if ( ! $uses_https ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site not configured for HTTPS. Modern browsers mark HTTP sites as "Not Secure", damaging user trust.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-tls-configuration',
				'meta'         => array(
					'is_ssl'            => $is_ssl,
					'uses_https'        => $uses_https,
					'site_url'          => $site_url,
					'home_url'          => $home_url,
				),
				'context'      => array(
					'why'            => __(
						'HTTPS encrypts communication between user and server, preventing man-in-the-middle (MITM) attacks. Without HTTPS: attacker on same WiFi intercepts traffic, sees passwords, session cookies, data transfers. All compromised. Google requires HTTPS for SEO ranking (HTTPS is ranking signal). Modern browsers display "Not Secure" on HTTP sites, causing ~75% user bounce rate. PCI-DSS, HIPAA, GDPR all require encryption in transit for regulated data. Verizon\'s 2024 DBIR reports unencrypted data transmission in 16% of breaches. Attackers specifically target HTTP sites knowing traffic is readable. SSL stripping attacks (downgrade HTTPS → HTTP) redirect users to unencrypted version. Free SSL from Let\'s Encrypt (supported by most hosts) eliminates cost excuse.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Obtain SSL certificate: Use Let\'s Encrypt (free, automated). Most hosting providers (GoDaddy, SiteGround, Bluehost, AWS) offer free or $7/year certificates.
2. Install SSL via cPanel/Plesk: If hosting provides control panel, click "Install SSL Certificate", select "Let\'s Encrypt", done. Automatic renewal included.
3. Use Really Simple SSL plugin: Install free "Really Simple SSL" plugin. Automatically fixes mixed content, redirects HTTP→HTTPS, adds HSTS.
4. Fix mixed content: Warnings appear in console if page loads http:// resources from https:// page. Search & replace old URLs: wp-cli: `wp search-replace \'http://domain.com\' \'https://domain.com\'`
5. Redirect HTTP to HTTPS: Add to .htaccess: `RewriteEngine On` + `RewriteCond %{HTTPS} off` + `RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]`
6. Update WordPress URLs: Dashboard > Settings > General URL, update both "WordPress Address" and "Site Address" to https://
7. Test SSL: Visit https://www.ssllabs.com/ssltest, enter domain, verify A or A+ rating. Fix any weak protocols (TLS 1.0, TLS 1.1).
8. Enable HSTS header: Add to .htaccess: `Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"` (tells browser: always use HTTPS for 1 year).
9. Add HSTS preload: Submit domain to https://hstspreload.org (includes site in browser HSTS preload list, prevents first visit MITM).
10. Monitor SSL certificate expiration: Let\'s Encrypt auto-renews, but set calendar reminder to verify renewal (happens 30 days before expiration).',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'https-enforcement',
				'ssl_tls_configuration'
			);

			return $finding;
		}

		// Check if HSTS is configured
		if ( $uses_https && ! $has_hsts ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HSTS (HTTP Strict Transport Security) not configured. Enable HSTS to prevent SSL stripping attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-tls-configuration',
				'meta'         => array(
					'has_hsts'       => $has_hsts,
				),
				'context'      => array(
					'why'            => __(
						'HSTS header tells browsers "this site ONLY uses HTTPS, never HTTP". Without HSTS: attacker can intercept first HTTP request to site, redirect user to malicious HTTPS look-alike site. With HSTS: browser refuses any HTTP connection, even if redirected. HSTS also prevents SSL downgrade attacks. OWASP Top 10 recommends HSTS for all production sites. Alexa top 100K sites: 58% implement HSTS. HSTS preload list prevents MITM even on first visit before HSTS header received.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Add HSTS header via .htaccess: Add line: `Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"`
2. Or use security plugin: "WP Force SSL" or "Really Simple SSL" have HSTS toggle in settings.
3. Or use WordPress filter: `add_filter(\'wp_headers\', function( $headers ) { $headers[\'Strict-Transport-Security\'] = \'max-age=31536000; includeSubDomains; preload\'; return $headers; })`
4. Set preload flag: Include "preload" in HSTS header to be included in browser HSTS preload lists.
5. Gradually increase max-age: Start with 1 month (2592000 seconds), test for issues, increase to 1 year (31536000).
6. Include subdomains: Add "includeSubDomains" to apply HSTS to all subdomains too.
7. Verify header: Use curl to check: `curl -I https://domain.com | grep -i "Strict-Transport"`
8. Test HSTS: Submit domain to https://hstspreload.org, browser preloads list updated monthly.
9. Monitor removal: If you remove HSTS and move site, users still can\'t access for 1 year. Plan ahead.
10. Communicate with users: No visible change, but security improved. Document in security policy.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'https-enforcement',
				'hsts_configuration'
			);

			return $finding;
		}

		return null;
	}
}
