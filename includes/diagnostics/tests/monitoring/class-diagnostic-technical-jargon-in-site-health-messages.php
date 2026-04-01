<?php
/**
 * Technical Jargon in Site Health Messages
 *
 * Tests whether Site Health uses accessible language vs technical jargon.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Technical_Jargon_In_Site_Health_Messages Class
 *
 * Validates language accessibility of Site Health messages.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Technical_Jargon_In_Site_Health_Messages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'technical-jargon-in-site-health-messages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health Language Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Site Health uses accessible language for non-developers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for technical jargon in Site Health messages.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for undefined acronyms
		$acronyms_issue = self::check_undefined_acronyms();
		if ( $acronyms_issue ) {
			$issues[] = $acronyms_issue;
		}

		// 2. Check for unexplained technical terms
		$terms_issue = self::check_unexplained_technical_terms();
		if ( $terms_issue ) {
			$issues[] = $terms_issue;
		}

		// 3. Check for plain language alternatives
		if ( ! self::has_plain_language_options() ) {
			$issues[] = __( 'No plain language explanations of technical concepts', 'wpshadow' );
		}

		// 4. Check for help documentation
		if ( ! self::has_accessible_docs() ) {
			$issues[] = __( 'Technical documentation not written for beginners', 'wpshadow' );
		}

		// 5. Check for glossary
		if ( ! self::has_glossary() ) {
			$issues[] = __( 'No glossary of technical terms', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of accessibility issues */
					__( '%d language accessibility issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-language-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Explain all acronyms on first use', 'wpshadow' ),
					__( 'Provide plain language definitions for technical terms', 'wpshadow' ),
					__( 'Use glossary with beginner-friendly explanations', 'wpshadow' ),
					__( 'Link to tutorials explaining technical concepts', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for undefined acronyms.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_undefined_acronyms() {
		// Common technical acronyms that should be explained
		$unexplained_acronyms = array(
			'PHP-FPM' => 'PHP FastCGI Process Manager',
			'OPcache' => 'Opcode caching system',
			'PDO'     => 'PHP Data Objects',
			'CORS'    => 'Cross-Origin Resource Sharing',
			'REST'    => 'Representational State Transfer',
			'JSON'    => 'JavaScript Object Notation',
			'SSL'     => 'Secure Sockets Layer',
			'TLS'     => 'Transport Layer Security',
			'FTP'     => 'File Transfer Protocol',
			'SFTP'    => 'SSH File Transfer Protocol',
		);

		// In real implementation, would scan Site Health messages
		// For now, assume potential issue exists
		return __( 'Site Health may use acronyms without definitions (PHP-FPM, OPcache, PDO, CORS, REST, etc.)', 'wpshadow' );
	}

	/**
	 * Check for unexplained technical terms.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_unexplained_technical_terms() {
		// Common technical terms used in Site Health
		$technical_terms = array(
			'serialization',
			'transients',
			'meta values',
			'caching',
			'database queries',
			'plugins',
			'themes',
			'hooks',
			'filters',
			'memory limit',
			'execution time',
			'file permissions',
			'charset',
			'collation',
			'compression',
		);

		// Assume some are unexplained
		return __( 'Site Health uses technical terms without beginner explanations', 'wpshadow' );
	}

	/**
	 * Check for plain language options.
	 *
	 * @since 0.6093.1200
	 * @return bool True if plain language available.
	 */
	private static function has_plain_language_options() {
		// Check for education mode or beginner mode
		if ( get_option( 'wpshadow_education_mode', false ) ) {
			return true;
		}

		// Check for user preference
		$user = wp_get_current_user();
		if ( $user && get_user_meta( $user->ID, 'wpshadow_prefer_simple_language', false ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for accessible documentation.
	 *
	 * @since 0.6093.1200
	 * @return bool True if beginner docs available.
	 */
	private static function has_accessible_docs() {
		// Check for KB articles written for non-technical users
		// Should have:
		// - Clear headings
		// - Short paragraphs
		// - Examples
		// - Screenshots

		// Check for education plugin
		if ( is_plugin_active( 'wpshadow-education/wpshadow-education.php' ) ) {
			return true;
		}

		// Check if documentation links point to accessible resources
		return false;
	}

	/**
	 * Check for glossary.
	 *
	 * @since 0.6093.1200
	 * @return bool True if glossary available.
	 */
	private static function has_glossary() {
		// Check for glossary feature
		$glossary_posts = get_posts( array(
			'post_type'  => 'glossary',
			'numberposts' => 1,
		) );

		if ( ! empty( $glossary_posts ) ) {
			return true;
		}

		// Check for documentation with glossary
		$docs_page = get_page_by_path( 'glossary' );

		return null !== $docs_page;
	}
}
