<?php
/**
 * No Local Business Schema Diagnostic
 *
 * Detects when local business schema is missing,
 * preventing local search features.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Local Business Schema
 *
 * Checks whether local business structured data
 * is implemented for local search visibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Local_Business_Schema extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-local-business-schema';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Local Business Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether local business schema exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for LocalBusiness schema
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		$has_local_schema = preg_match( '/"@type"\s*:\s*"(?:LocalBusiness|Restaurant|Store|AutoDealer|HealthAndBeautyBusiness)"/i', $body );

		if ( ! $has_local_schema ) {
			// Only flag if contact info exists (indicates local business)
			$has_contact_info = preg_match( '/(?:address|phone|location)/i', $body );
			
			if ( $has_contact_info ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __(
						'Local business schema isn\'t implemented, which limits local search visibility. LocalBusiness schema tells Google: your business name, address, phone, hours, services, reviews. This enables: Knowledge Panel in search, rich local results, "Open Now" indicators, click-to-call buttons. For local businesses, this is critical for appearing in "near me" searches and Google Maps. Schema plugins can add this automatically.',
						'wpshadow'
					),
					'severity'      => 'high',
					'threat_level'  => 65,
					'auto_fixable'  => false,
					'business_impact' => array(
						'metric'         => 'Local Search Visibility',
						'potential_gain' => 'Appear in local search and maps',
						'roi_explanation' => 'Local business schema enables rich local search features and "near me" search visibility.',
					),
					'kb_link'       => 'https://wpshadow.com/kb/local-business-schema',
				);
			}
		}

		return null;
	}
}
