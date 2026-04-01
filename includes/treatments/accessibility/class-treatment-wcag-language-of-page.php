<?php
/**
 * Treatment: Add WCAG 3.1.1 Language Attribute
 *
 * Adds or fixes the HTML lang attribute so screen readers
 * can pronounce content correctly.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_WCAG_Language_Of_Page Class
 *
 * Adds proper lang attribute to <html> element (WCAG 3.1.1 Level A).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_Language_Of_Page extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'wcag-language-of-page';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds proper HTML lang attribute using WordPress language_attributes().
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		$theme_header = get_template_directory() . '/header.php';

		// Check if header.php exists.
		if ( ! file_exists( $theme_header ) ) {
			// Use filter to add lang attribute instead.
			return self::apply_via_filter();
		}

		// Read header content.
		$content = file_get_contents( $theme_header );
		if ( $content === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to read theme header.php file', 'wpshadow' ),
			);
		}

		// Backup original file.
		$backup_path = get_template_directory() . '/header.php.wpshadow-backup';
		file_put_contents( $backup_path, $content );

		$original_content = $content;
		$modified = false;

		// Pattern 1: <html> with no attributes - add language_attributes().
		if ( preg_match( '/<html\s*>/i', $content ) ) {
			$content = preg_replace(
				'/<html\s*>/i',
				'<html <?php language_attributes(); ?>>',
				$content
			);
			$modified = true;
		}

		// Pattern 2: <html lang="en"> - replace with language_attributes().
		if ( preg_match( '/<html\s+lang=["\'][^"\']*["\'][^>]*>/i', $content ) ) {
			$content = preg_replace(
				'/<html\s+lang=["\'][^"\']*["\']([^>]*)>/i',
				'<html <?php language_attributes(); ?>$1>',
				$content
			);
			$modified = true;
		}

		// Pattern 3: <html class="something"> - add language_attributes() before class.
		if ( ! $modified && preg_match( '/<html\s+class=/i', $content ) ) {
			$content = preg_replace(
				'/<html\s+(class=)/i',
				'<html <?php language_attributes(); ?> $1',
				$content
			);
			$modified = true;
		}

		if ( ! $modified ) {
			// No changes needed or couldn't find pattern - use filter approach.
			return self::apply_via_filter();
		}

		// Write modified content.
		$result = file_put_contents( $theme_header, $content );

		if ( $result === false ) {
			// Restore backup.
			file_put_contents( $theme_header, $original_content );

			return array(
				'success' => false,
				'message' => __( 'Failed to write modified header.php file', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Added proper HTML lang attribute for screen readers', 'wpshadow' ),
			'details' => array(
				'action'         => 'modified_header',
				'file'           => 'header.php',
				'backup'         => 'header.php.wpshadow-backup',
				'current_locale' => get_locale(),
				'lang_code'      => substr( get_locale(), 0, 2 ),
				'impact'         => __( 'Screen readers will now pronounce text correctly', 'wpshadow' ),
			),
		);
	}

	/**
	 * Apply treatment via filter (fallback method).
	 *
	 * @since 0.6093.1200
	 * @return array Result array.
	 */
	private static function apply_via_filter() {
		// Create mu-plugin to add language attributes via filter.
		$mu_plugin_code = self::get_lang_filter_mu_plugin();
		$mu_plugin_path = WPMU_PLUGIN_DIR . '/wpshadow-html-lang.php';

		// Create mu-plugins directory if it doesn't exist.
		if ( ! is_dir( WPMU_PLUGIN_DIR ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}

		// Write the mu-plugin file.
		$result = file_put_contents( $mu_plugin_path, $mu_plugin_code );

		if ( $result === false ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create language attribute mu-plugin', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Added HTML lang attribute via filter', 'wpshadow' ),
			'details' => array(
				'action'         => 'created_mu_plugin',
				'file'           => 'wpshadow-html-lang.php',
				'current_locale' => get_locale(),
				'lang_code'      => substr( get_locale(), 0, 2 ),
			),
		);
	}

	/**
	 * Get MU plugin code for language filter.
	 *
	 * @since 0.6093.1200
	 * @return string MU plugin code.
	 */
	private static function get_lang_filter_mu_plugin() {
		return <<<'PHP'
<?php
/**
 * WPShadow: HTML Lang Attribute
 *
 * Adds proper lang attribute to HTML element.
 * Created by WPShadow accessibility treatment.
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add language attributes to HTML tag via filter.
 */
add_filter( 'language_attributes', function( $output ) {
	$lang = get_bloginfo( 'language' );
	if ( empty( $output ) ) {
		return 'lang="' . esc_attr( $lang ) . '"';
	}
	return $output;
}, 100 );
PHP;
	}
}

		if ( ! $uses_wp_function && ! empty( $issues ) ) {
			$issues[] = __( 'Theme should use language_attributes() function for automatic locale handling', 'wpshadow' );
		}

		// Check WordPress site language configuration.
		$site_language = get_bloginfo( 'language' );
		if ( empty( $site_language ) ) {
			$issues[] = __( 'WordPress site language not configured in Settings > General', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site\'s HTML language attribute helps screen readers pronounce text correctly. About 2% of users rely on screen readers and need this set properly. Without it, a French screen reader might try to read English text with French pronunciation, making it unintelligible. This is like having someone read Spanish with an English accent—confusing for everyone.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-language-of-page?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
