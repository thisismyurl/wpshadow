<?php
/**
 * No FAQ Schema Markup Diagnostic
 *
 * Detects when FAQ schema is missing,
 * preventing rich FAQ results in search.
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
 * Diagnostic: No FAQ Schema Markup
 *
 * Checks whether FAQ schema is implemented
 * for rich FAQ search results.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_FAQ_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-faq-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether FAQ schema is implemented';

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
		// Check homepage for FAQ content
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for FAQ content
		$has_faq = preg_match( '/FAQ|frequently\s+asked\s+questions/i', $body );
		
		// Check for FAQ schema
		$has_faq_schema = strpos( $body, 'FAQPage' ) !== false;

		if ( $has_faq && ! $has_faq_schema ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'FAQ schema isn\'t implemented, which means your FAQ content won\'t get rich results. FAQ schema displays Q&A in special accordion format in search results, taking up more space and getting more clicks. Schema markup tells Google which text is the question and which is the answer. This is powerful for industries with common questions: support, SaaS, ecommerce, services.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Search Result Prominence',
					'potential_gain' => 'Rich FAQ results with higher CTR',
					'roi_explanation' => 'FAQ schema displays accordions in search results, increasing visibility and click-through rates.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/faq-schema-markup',
			);
		}

		return null;
	}
}
