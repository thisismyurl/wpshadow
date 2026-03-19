<?php
/**
 * Missing Form Field Labels Diagnostic
 *
 * Detects when form fields lack proper labels, causing accessibility
 * issues and potential security concerns with form validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Missing Form Field Labels
 *
 * Checks whether all form inputs have associated labels
 * for accessibility and form clarity.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Form_Field_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-form-field-labels';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Form Field Labels';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether form fields have proper labels';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

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
		global $wpdb;

		// Get all published posts with forms
		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$unlabeled_fields = 0;

		foreach ( $posts as $post ) {
			// Count input fields without labels
			preg_match_all( '/<input\s+(?!.*type\s*=\s*["\']?(hidden|submit|button)["\']?)[^>]*>/i', $post->post_content, $inputs );
			
			if ( ! empty( $inputs[0] ) ) {
				foreach ( $inputs[0] as $input ) {
					// Check if input has id and corresponding label
					if ( preg_match( '/id\s*=\s*["\']?([^"\'\s>]+)["\']?/i', $input, $id_match ) ) {
						$input_id = $id_match[1];
						// Check if label for this ID exists
						if ( ! preg_match( '/<label[^>]*for\s*=\s*["\']?' . preg_quote( $input_id ) . '["\']?[^>]*>/i', $post->post_content ) ) {
							$unlabeled_fields++;
						}
					} else {
						// No ID at all
						$unlabeled_fields++;
					}
				}
			}
		}

		if ( $unlabeled_fields > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Some form fields are missing labels. This is like a form with empty boxes—users don\'t know what to enter. Proper labels are essential for accessibility (people using screen readers need them) and user experience (everyone uses them). Labels should be associated with inputs using the "for" attribute. This also helps with form security by making input purpose clear.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'unlabeled_fields' => $unlabeled_fields,
				'business_impact' => array(
					'metric'         => 'Form Completion Rate',
					'potential_gain' => 'Improved form UX',
					'roi_explanation' => 'Clear labels reduce form abandonment and increase accessibility for all users, including those using assistive technology.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/form-field-labels',
			);
		}

		return null;
	}
}
