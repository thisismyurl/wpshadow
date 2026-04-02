<?php
/**
 * Organization Schema Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Organization_Schema_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Organization_Schema extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'organization-schema';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Organization Schema';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether Organization or Person structured data schema is being output to help search engines understand and display the site\'s identity.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check organization schema fields for name, logo, and URL completeness.
	 *
	 * TODO Fix Plan:
	 * - Complete organization schema to strengthen brand understanding.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		$has_yoast    = in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true )
		             || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins, true );
		$has_rankmath = in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true )
		             || in_array( 'seo-by-rank-math-pro/rank-math-pro.php', $active_plugins, true );

		$missing_fields = array();

		if ( $has_yoast ) {
			$wpseo = get_option( 'wpseo', array() );
			$type  = isset( $wpseo['company_or_person'] ) ? $wpseo['company_or_person'] : '';

			if ( 'company' === $type || '' === $type ) {
				if ( empty( $wpseo['company_name'] ) ) {
					$missing_fields[] = 'organization name';
				}
				if ( empty( $wpseo['company_logo'] ) ) {
					$missing_fields[] = 'organization logo';
				}
			}
		} elseif ( $has_rankmath ) {
			$general = get_option( 'rank_math_settings_general', array() );
			if ( empty( $general['knowledgegraph_name'] ) ) {
				$missing_fields[] = 'organization name';
			}
			if ( empty( $general['knowledgegraph_logo'] ) ) {
				$missing_fields[] = 'organization logo';
			}
		} else {
			// No SEO plugin — can't determine schema output.
			return null;
		}

		if ( empty( $missing_fields ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of missing fields */
				__( 'Organization schema is incomplete. The following fields are missing: %s. Complete these in your SEO plugin\'s knowledge graph / organization settings so search engines can accurately associate your brand with this site.', 'wpshadow' ),
				implode( ', ', $missing_fields )
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/organization-schema',
			'details'      => array(
				'missing_fields' => $missing_fields,
			),
		);
	}
}
