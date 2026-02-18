<?php
/**
 * XSS Protection Header Missing Diagnostic
 *
 * Checks XSS protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_XSS_Protection_Header_Missing Class
 *
 * Performs diagnostic check for Xss Protection Header Missing.
 *
 * @since 1.6033.2033
 */
class Diagnostic_XSS_Protection_Header_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-protection-header-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Protection Header Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks XSS protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'add_xss_protection_header' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'XSS Protection header missing. Add X-XSS-Protection header (legacy support) and Content-Security-Policy for modern browsers.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/xss-protection-header-missing',
				'context'       => array(
					'why'            => __( 'X-XSS-Protection = legacy IE/Chrome protection (now replaced by CSP). Still recommended for backward compatibility. Real scenario: Attacker finds XSS, injects malicious script. Browser with X-XSS-Protection=1 blocks it. Browser without header allows it. OWASP recommends both CSP + X-XSS-Protection. Chrome removed support but IE/Edge still use it. Cost of XSS breach: $4.29M average. One header prevents that.', 'wpshadow' ),
					'recommendation' => __( '1. Add header to .htaccess: "Header set X-XSS-Protection \'1; mode=block\'". 2. Or in functions.php: header("X-XSS-Protection: 1; mode=block"). 3. mode=block = block page on XSS detection (vs. sanitize). 4. Combine with Content-Security-Policy for defense-in-depth. 5. Test header presence: curl -I yoursite.com | grep X-XSS. 6. Monitor browser compatibility: most modern browsers use CSP instead. 7. Set in wp_headers filter: hook into response headers. 8. Verify on production: curl to live site, check header presence. 9. Use security header scanner: securityheaders.com checks automatically. 10. Plan CSP migration: CSP is modern replacement for X-XSS-Protection.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'xss-protection', 'header-configuration' );
			return $finding;
		}

		return null;
	}
}
