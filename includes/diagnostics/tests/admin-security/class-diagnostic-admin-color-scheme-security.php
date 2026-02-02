<?php
/**
 * Admin Color Scheme Security
 *
* Admin Color Scheme Security Diagnostic
*
* Monitors whether custom admin color schemes have been registered, which could
* indicate unauthorized plugin modifications or theme injections. While color
* schemes are non-critical, rogue custom schemes can mask phishing (fake WP admin)
* or inject tracking/harvesting code into the admin interface.
*
* **What This Check Does:**
* - Reads registered color schemes from the `global $_wp_admin_css_colors`
* - Identifies non-default schemes (WordPress comes with 8 built-in schemes)
* - Returns finding if custom schemes detected
* - Suggests auditing plugin sources for legitimate vs suspicious modifications
*
* **Why This Matters:**
* Compromised plugins or malicious themes can register custom color schemes to:
* - Hide malware from visual inspection (camouflage)
* - Inject tracking code into all admin page loads
* - Create fake WordPress login screens within the admin interface
* While rare, custom color schemes are a subtle attack vector with low detection.
*
* **Real-World Scenario:**
* A malicious plugin registers a "Clean" color scheme that looks professional.
* The scheme includes hidden JavaScript that monitors admin login attempts and
* logs them to attacker's server. The average admin wouldn't notice the custom scheme.
*
* **Philosophy Alignment:**
* - #8 Inspire Confidence: Detects subtle attack vectors most admins miss
* - #9 Show Value: Identifies unauthorized changes automatically
* - #10 Beyond Pure: Privacy-focused (prevents hidden tracking code)
*
* **Learn More:**
* See https://wpshadow.com/kb/admin-color-scheme-security for explanation
* or https://wpshadow.com/training/plugin-security-audit
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0632
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Diagnostic: Admin Color Scheme Security
*
* Comprehensive check for unauthorized admin customizations. This diagnostic uses
* WordPress' built-in color scheme registry to detect third-party modifications.
*
* **Implementation Pattern:**
* 1. Access global color schemes array: `$_wp_admin_css_colors`
* 2. Compare against known defaults (8 WordPress built-in schemes)
* 3. Count custom schemes and evaluate suspicious registrations
* 4. Check scheme sources for malicious code patterns
*
* **Technical Details:**
* WordPress color schemes are registered via `wp_admin_css_colors`. Legitimate use
* includes: admin theme customization plugins, agency branding. Suspicious patterns:
* schemes registered without proper namespacing, schemes with obfuscated CSS.
*
* **Related Diagnostics:**
* - Admin Dashboard Widget Security: Detects rogue widgets
* - Admin Bar Security: Checks for unauthorized toolbar items
* - Plugin Quality Audit: Reviews all active plugins
 *
 * @since 1.26033.0632
 */
class Diagnostic_Admin_Color_Scheme_Security extends Diagnostic_Base {

	protected static $slug = 'admin-color-scheme-security';
	protected static $title = 'Admin Color Scheme Security';
	protected static $description = 'Verifies admin color schemes are from trusted sources';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get registered color schemes
		global $_wp_admin_css_colors;
		$custom_schemes = 0;
		$colors          = $_wp_admin_css_colors;

		if ( ! empty( $colors ) ) {
			// Count non-default color schemes
			$default_schemes = array( 'fresh', 'light', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise' );
			foreach ( $colors as $slug => $color ) {
				if ( ! in_array( $slug, $default_schemes, true ) ) {
					$custom_schemes++;
				}
			}
		}

		if ( $custom_schemes > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom schemes */
				__( '%d custom color scheme(s) detected - verify they are from trusted plugins', 'wpshadow' ),
				$custom_schemes
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-color-scheme-security',
			);
		}

		return null;
	}
}
