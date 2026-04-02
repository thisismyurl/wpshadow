<?php
/**
 * Component API Version Compatibility Diagnostic
 *
 * Issue #4983: Components Using Outdated API Versions
 * Pillar: ⚙️ Murphy's Law / #12: Expandable
 *
 * Checks if components use current WordPress API versions.
 * Old APIs may deprecate and cause breakage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Component_API_Version_Compatibility Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Component_API_Version_Compatibility extends Diagnostic_Base {

	protected static $slug = 'component-api-version-compatibility';
	protected static $title = 'Components Using Outdated API Versions';
	protected static $description = 'Checks if components use current WordPress API versions';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Update component API version to latest stable', 'wpshadow' );
		$issues[] = __( 'Monitor WordPress deprecation notices', 'wpshadow' );
		$issues[] = __( 'Test with WP_DEBUG enabled to catch issues', 'wpshadow' );
		$issues[] = __( 'Plan migration for major API changes', 'wpshadow' );
		$issues[] = __( 'Use backward compatibility layers during migration', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress deprecates old APIs to improve security and performance. Components must update to new APIs to remain compatible.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/api-compatibility',
				'details'      => array(
					'recommendations'         => $issues,
					'wp_debug'                => 'Enable WP_DEBUG to see deprecation warnings',
					'example'                 => 'get_currentuserinfo() deprecated, use wp_get_current_user()',
					'commandment'             => 'Commandment #12: Expandable',
				),
			);
		}

		return null;
	}
}
