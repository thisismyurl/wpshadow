<?php
/**
 * Theme Accessibility and Inclusivity
 *
 * Validates theme accessibility and inclusive design.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Accessibility Class
 *
 * Checks theme accessibility and inclusive design.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme accessibility and inclusive design';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'theme-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Pattern 1: Theme missing alt text for images
		// Note: This would require parsing actual pages - simplified check
		$theme_file = get_template_directory() . '/style.css';
		$functions  = get_template_directory() . '/functions.php';

		$missing_alt_check = false;

		if ( file_exists( $functions ) ) {
			$content = file_get_contents( $functions );

			if ( preg_match( '/wp_get_attachment|get_the_post_thumbnail/', $content ) ) {
				// Check if alt text is being set
				if ( ! preg_match( '/alt=|_thumbnail_alt/', $content ) ) {
					$missing_alt_check = true;
				}
			}
		}

		if ( $missing_alt_check ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme may not properly handle alt text for images', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-accessibility',
				'details'      => array(
					'issue'                       => 'missing_alt_text',
					'message'                     => __( 'Theme not properly displaying image alt text', 'wpshadow' ),
					'alt_text_purpose'            => array(
						'Screen readers' => 'Describe images for blind users',
						'Broken images'  => 'Text shown if image fails to load',
						'SEO'            => 'Google indexes alt text',
						'Social sharing' => 'Description when shared',
					),
					'wcag_requirement'            => __( 'WCAG 2.1 Level A requires alt text for all images', 'wpshadow' ),
					'displaying_thumbnails'       => "// WRONG - No alt text
echo get_the_post_thumbnail();

// RIGHT - With alt text
echo get_the_post_thumbnail(null, 'full', array(
	'alt' => get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true)
));

// BEST - Descriptive alt text
\$alt_text = get_the_title() . ' - featured image';
echo wp_get_attachment_image(get_post_thumbnail_id(), 'full', false, array(
	'alt' => \$alt_text
));",
					'getting_alt_text'            => "\$attachment_id = 123;
\$alt = get_post_meta(\$attachment_id, '_wp_attachment_image_alt', true);
echo '<img src=\"' . wp_get_attachment_url(\$attachment_id) . '\" alt=\"' . esc_attr(\$alt) . '\">';",
					'meaningful_alt_descriptions' => array(
						'For decorative images' => 'alt=\"\"',
						'For photos'            => 'Describe the subject',
						'For charts'            => 'Describe data',
						'For logos'             => 'Company name or brief description',
					),
					'alt_text_examples'           => '// Good alt text
alt="WordPress logo - open source CMS"
alt="Chart showing Q4 revenue trends"
alt="Team members at company retreat"

// Bad alt text
alt="image"
alt="pic"
alt="photo123"',
					'filters_for_alt'             => "// Auto-set alt text from post title
add_filter('get_image_tag', function(\$html, \$id, \$alt, \$title) {
	if (empty(\$alt)) {
		\$post = get_post(\$id);
		\$alt = \$post->post_title;
	}
	return str_replace('alt=\"\"', 'alt=\"' . esc_attr(\$alt) . '\"', \$html);
}, 10, 4);",
					'testing_accessibility'       => array(
						'1. Use Lighthouse audit',
						'2. Test with screen reader',
						'3. Check alt text on all images',
						'4. Verify decorative images have empty alt',
					),
					'tools'                       => array(
						'Lighthouse'   => 'Chrome DevTools accessibility audit',
						'WAVE'         => 'Web accessibility evaluation tool',
						'Axe DevTools' => 'Accessibility testing',
						'NVDA'         => 'Free screen reader',
					),
					'recommendation'              => __( 'Ensure theme properly displays and handles image alt text', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Theme missing focus indicators
		if ( file_exists( $theme_file ) ) {
			$content = file_get_contents( $theme_file );

			if ( ! preg_match( '/:focus|outline|box-shadow.*focus/', $content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme missing focus indicators for keyboard navigation', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-accessibility',
					'details'      => array(
						'issue'                      => 'missing_focus_indicators',
						'message'                    => __( 'Theme lacks visible focus indicators for keyboard users', 'wpshadow' ),
						'keyboard_users'             => array(
							'Power users'    => 'Prefer keyboard navigation',
							'Disabled users' => 'Cannot use mouse',
							'Voice control'  => 'Navigates by keyboard',
							'Developers'     => 'Testing accessibility',
						),
						'focus_indicator_importance' => __( 'WCAG 2.1 Level AA requires visible focus indicators', 'wpshadow' ),
						'adding_focus_styles'        => '/* Provide visible focus indicator */
button:focus,
a:focus,
input:focus {
	outline: 2px solid #0073aa;
	outline-offset: 2px;
}

/* Or use box-shadow for modern browsers */
:focus-visible {
	outline: 3px solid #0073aa;
	outline-offset: 2px;
}',
						'contrast_requirements'      => __( 'Focus indicator must have 3:1 contrast ratio', 'wpshadow' ),
						'focus_visible'              => '/* Modern browsers - only show focus on keyboard, not mouse */
:focus-visible {
	outline: 2px solid #0073aa;
}

/* Fallback for older browsers */
:focus {
	outline: 2px solid #0073aa;
}',
						'testing_keyboard'           => array(
							'1. Tab through page',
							'2. Verify you can see focus outline',
							'3. Test on different elements',
							'4. Verify outline is visible',
						),
						'color_contrast'             => __( 'Ensure focus color contrasts with background', 'wpshadow' ),
						'recommendation'             => __( 'Add visible focus indicators to theme for keyboard accessibility', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Theme not semantic HTML
		return null;
	}
}
