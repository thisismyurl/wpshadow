<?php
/**
 * PHP Extensions Required Diagnostic
 *
 * Verifies that critical PHP extensions are installed and enabled
 * to ensure WordPress and plugins function correctly.
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
 * Diagnostic_PHP_Extensions_Required Class
 *
 * Verifies critical PHP extensions are installed.
 *
 * @since 1.2601.2148
 */
class Diagnostic_PHP_Extensions_Required extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-extensions-required';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Extensions Required';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies critical PHP extensions are installed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if missing extensions found, null otherwise.
	 */
	public static function check() {
		$missing_extensions = self::check_php_extensions();

		if ( empty( $missing_extensions['critical'] ) ) {
			return null; // All critical extensions present
		}

		$severity = count( $missing_extensions['critical'] ) > 2 ? 'critical' : 'high';
		$threat   = count( $missing_extensions['critical'] ) > 2 ? 85 : 70;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of missing critical extensions */
				__( '%d critical PHP extensions are missing. WordPress and plugins will fail to function correctly.', 'wpshadow' ),
				count( $missing_extensions['critical'] )
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-extensions',
			'family'       => self::$family,
			'meta'         => array(
				'missing_critical'   => count( $missing_extensions['critical'] ),
				'missing_recommended' => count( $missing_extensions['recommended'] ),
				'impact'             => __( 'Database queries, file operations, image processing will fail' ),
				'fix_difficulty'     => __( 'Contact hosting provider to enable extensions' ),
			),
			'details'      => array(
				'critical_extensions'       => array(
					'mysqli or pdo_mysql' => array(
						'Purpose: Database connectivity',
						'Without: All WordPress queries fail, site completely broken',
						'Usually: Installed by default',
					),
					'json' => array(
						'Purpose: JSON encoding/decoding (used everywhere)',
						'Without: API calls, plugins fail',
						'Usually: Installed by default',
					),
					'curl' => array(
						'Purpose: HTTP requests to external APIs',
						'Without: WordPress.org updates fail, API integrations broken',
						'Usually: Installed by default',
					),
					'gd or imagick' => array(
						'Purpose: Image processing (thumbnails, cropping)',
						'Without: Featured images broken, image uploads fail',
						'Usually: Must be enabled manually',
					),
				),
				'recommended_extensions'     => array(
					'openssl' => array(
						'Purpose: HTTPS, secure communications',
						'Without: Payment processing fails, security risks',
						'Check: Hosting control panel for enabling',
					),
					'xml / libxml' => array(
						'Purpose: XML parsing (WooCommerce feeds, etc)',
						'Without: Feed processing fails',
						'Usually: Installed by default',
					),
					'mbstring' => array(
						'Purpose: Multi-byte string handling (UTF-8)',
						'Without: Special characters display incorrectly',
						'Usually: Installed by default',
					),
					'opcache' => array(
						'Purpose: PHP bytecode caching (5-10x speed boost)',
						'Without: PHP scripts slower',
						'Check: Hosting control panel for enabling',
					),
				),
				'enabling_extensions'       => array(
					'Shared Hosting (cPanel/Plesk)' => array(
						'1. Log in to hosting control panel',
						'2. Find PHP Configuration or MultiPHP Manager',
						'3. Select PHP version',
						'4. Check extension boxes (gd, curl, openssl)',
						'5. Apply/save changes',
						'6. Wait 5 minutes for changes to take effect',
					),
					'Dedicated/VPS (Linux)' => array(
						'1. SSH into server',
						'2. Check installed: php -m',
						'3. Install if missing: apt-get install php-gd',
						'4. Enable in php.ini: uncomment ;extension=gd.so',
						'5. Restart Apache: systemctl restart apache2',
					),
					'Windows Hosting' => array(
						'1. Log in to control panel',
						'2. Find Windows PHP Manager',
						'3. Select extensions to enable',
						'4. Restart IIS service',
					),
				),
				'php_info_verification'      => array(
					__( 'Create test file: phpinfo.php' ),
					__( 'Add code: <?php phpinfo(); ?>' ),
					__( 'Visit http://yoursite.com/phpinfo.php' ),
					__( 'Search for extensions listed (use Ctrl+F)' ),
					__( 'Delete phpinfo.php after checking' ),
				),
				'common_issues'              => array(
					'GD Library Missing' => array(
						'Symptom: "Call to undefined function imagecreatefromjpeg()"',
						'Cause: GD extension not enabled',
						'Fix: Enable php-gd in hosting control panel',
					),
					'cURL Missing' => array(
						'Symptom: WordPress updates fail, "Failed opening URL"',
						'Cause: cURL extension disabled',
						'Fix: Enable curl in hosting control panel',
					),
				),
			),
		);
	}

	/**
	 * Check for PHP extensions.
	 *
	 * @since  1.2601.2148
	 * @return array Missing extensions.
	 */
	private static function check_php_extensions() {
		$critical = array(
			'mysqli',
			'json',
			'curl',
			'gd',
		);

		$recommended = array(
			'openssl',
			'xml',
			'mbstring',
			'opcache',
		);

		$missing_critical   = array();
		$missing_recommended = array();

		foreach ( $critical as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_critical[] = $ext;
			}
		}

		foreach ( $recommended as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_recommended[] = $ext;
			}
		}

		return array(
			'critical'   => $missing_critical,
			'recommended' => $missing_recommended,
		);
	}
}
