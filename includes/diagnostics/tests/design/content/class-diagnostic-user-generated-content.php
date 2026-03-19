<?php
/**
 * User Generated Content Diagnostic
 *
 * Detects when sites aren't leveraging user-generated content opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Content;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Generated Content Diagnostic Class
 *
 * Checks if the site encourages and displays user-generated content.
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Generated_Content extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-generated-content';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User-Generated Content Not Leveraged';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when sites don\'t encourage or display user-generated content';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$has_ugc          = false;
		$ugc_features     = array();
		$ugc_opportunities = array();

		// Check for UGC plugins/features.
		$ugc_plugins = array(
			'buddypress/bp-loader.php'              => 'BuddyPress (User Profiles & Activity)',
			'bbpress/bbpress.php'                   => 'bbPress (Forums)',
			'woocommerce/woocommerce.php'           => 'WooCommerce (Product Reviews)',
			'wp-user-frontend/wpuf.php'             => 'WP User Frontend (User Submissions)',
			'user-submitted-posts/user-submitted-posts.php' => 'User Submitted Posts',
			'photo-gallery/photo-gallery.php'       => 'Photo Gallery (User Photos)',
		);

		foreach ( $ugc_plugins as $plugin => $description ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_ugc        = true;
				$ugc_features[] = $description;
			}
		}

		// Check if comments are enabled globally.
		$comments_enabled = get_default_comment_status();
		if ( 'open' === $comments_enabled ) {
			$has_ugc        = true;
			$ugc_features[] = __( 'Blog Comments', 'wpshadow' );
		} else {
			$ugc_opportunities[] = __( 'Enable blog comments for engagement', 'wpshadow' );
		}

		// Check for contact forms (potential for submissions).
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'ninja-forms/ninja-forms.php',
			'wpforms-lite/wpforms.php',
			'gravityforms/gravityforms.php',
		);

		$has_forms = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms = true;
				break;
			}
		}

		if ( ! $has_forms ) {
			$ugc_opportunities[] = __( 'Add contact forms for user input', 'wpshadow' );
		}

		// Check for social proof/testimonial features.
		if ( ! has_action( 'wp_footer', 'display_testimonials' ) && ! has_shortcode( get_the_content(), 'testimonial' ) ) {
			$ugc_opportunities[] = __( 'Collect and display customer testimonials', 'wpshadow' );
		}

		// If UGC features exist and opportunities are minimal, no issue.
		if ( $has_ugc && count( $ugc_opportunities ) <= 1 ) {
			return null;
		}

		// If no content-driven site, less critical.
		$post_count = wp_count_posts( 'post' );
		if ( ( $post_count->publish ?? 0 ) < 5 ) {
			return null; // Small site, not critical.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'You\'re creating all the content yourself instead of letting customers contribute. User-generated content builds community, trust, and creates free content', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/user-generated-content',
			'context'      => array(
				'current_ugc'       => $ugc_features,
				'opportunities'     => $ugc_opportunities,
				'post_count'        => $post_count->publish ?? 0,
				'impact'            => __( 'User-generated content (reviews, comments, testimonials, photos) is 92% more trusted than brand content. It also creates fresh content without you writing anything.', 'wpshadow' ),
				'recommendation'    => array(
					__( 'Enable and encourage blog comments', 'wpshadow' ),
					__( 'Add product/service review capability', 'wpshadow' ),
					__( 'Create hashtag campaigns for social media UGC', 'wpshadow' ),
					__( 'Feature customer photos and stories', 'wpshadow' ),
					__( 'Add Q&A sections on product pages', 'wpshadow' ),
					__( 'Create a community forum for discussion', 'wpshadow' ),
					__( 'Run contests that encourage submissions', 'wpshadow' ),
					__( 'Display customer testimonials prominently', 'wpshadow' ),
				),
				'seo_benefit'       => __( 'UGC generates 6.9x more engagement and improves SEO with fresh, keyword-rich content', 'wpshadow' ),
				'trust_factor'      => __( 'Customer reviews increase conversions by 270%', 'wpshadow' ),
			),
		);
	}
}
