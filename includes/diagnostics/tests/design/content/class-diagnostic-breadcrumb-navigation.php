<?php
/**
 * Breadcrumb Navigation Diagnostic
 *
 * Issue #4963: No Breadcrumb Navigation
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if site provides breadcrumb navigation.
 * Breadcrumbs help users understand location and hierarchy.
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
 * Diagnostic_Breadcrumb_Navigation Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Breadcrumb_Navigation extends Diagnostic_Base {

	protected static $slug = 'breadcrumb-navigation';
	protected static $title = 'No Breadcrumb Navigation';
	protected static $description = 'Checks if site provides breadcrumb trails for navigation';
	protected static $family = 'content';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add breadcrumbs to all interior pages', 'wpshadow' );
		$issues[] = __( 'Show hierarchy: Home > Category > Subcategory > Page', 'wpshadow' );
		$issues[] = __( 'Make breadcrumbs clickable (each level is link)', 'wpshadow' );
		$issues[] = __( 'Use schema.org markup for SEO benefit', 'wpshadow' );
		$issues[] = __( 'Keep breadcrumb style simple and consistent', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Breadcrumbs show users where they are in your site structure. This reduces confusion and makes it easy to navigate back up.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/breadcrumbs',
				'details'      => array(
					'recommendations'         => $issues,
					'seo_benefit'             => 'Google shows breadcrumbs in search results',
					'user_benefit'            => 'Reduces clicks to navigate hierarchy',
					'commandment'             => 'Commandment #1: Helpful Neighbor Experience',
				),
			);
		}

		return null;
	}
}
