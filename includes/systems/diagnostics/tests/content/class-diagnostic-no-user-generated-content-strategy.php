<?php
/**
 * No User-Generated Content Strategy Diagnostic
 *
 * Detects when user-generated content is not leveraged,
 * missing cost-effective content and social proof.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No User-Generated Content Strategy
 *
 * Checks whether user-generated content is being
 * collected and displayed for trust and scale.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_User_Generated_Content_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-user-generated-content-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User-Generated Content Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether user-generated content is being collected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for UGC plugins
		$has_ugc_plugin = is_plugin_active( 'hashtaggable/hashtaggable.php' ) ||
			is_plugin_active( 'crowdsignal/crowdsignal.php' ) ||
			is_plugin_active( 'wp-photo-album-plus/wp-photo-album-plus.php' );

		// Check for comment or review systems
		$has_comments = get_comments( array( 'number' => 1 ) );

		if ( ! $has_ugc_plugin && empty( $has_comments ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not collecting or displaying user-generated content, which is free content AND social proof. User-generated content (reviews, comments, testimonials, photos) is 5x more trusted than branded content. It\'s also cost-effective—users create content for free. Encourage and display: reviews, comments, testimonials, customer photos, success stories. This builds community, increases engagement, and provides fresh content for SEO.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Trust & Content Scale',
					'potential_gain' => '5x more trusted than branded content',
					'roi_explanation' => 'User-generated content provides free, trusted content while building community and providing social proof for conversions.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/user-generated-content-strategy',
			);
		}

		return null;
	}
}
