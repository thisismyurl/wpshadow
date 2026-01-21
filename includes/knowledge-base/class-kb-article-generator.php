<?php
/**
 * KB Article Generator
 *
 * Generates KB articles from diagnostic and treatment metadata.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Article Generator
 */
class KB_Article_Generator {
	/**
	 * Generate article from diagnostic data.
	 *
	 * @param string $diagnostic_id Diagnostic ID.
	 * @param array  $diagnostic    Diagnostic data.
	 * @return array Article data.
	 */
	public static function generate_diagnostic_article( $diagnostic_id, $diagnostic ) {
		$title       = isset( $diagnostic['title'] ) ? $diagnostic['title'] : 'Diagnostic Check';
		$description = isset( $diagnostic['description'] ) ? $diagnostic['description'] : '';
		$status      = isset( $diagnostic['status'] ) ? $diagnostic['status'] : 'good';
		$category    = isset( $diagnostic['category'] ) ? $diagnostic['category'] : 'General';

		// Build content sections
		$content = '';

		// What it is section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'What is this check?', 'wpshadow' ) . '</h2>';
		$content .= '<p>' . wp_kses_post( $description ) . '</p>';
		$content .= '</section>';

		// Why it matters section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'Why it matters', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= self::get_why_matters_text( $diagnostic_id, $category );
		$content .= '</p>';
		$content .= '</section>';

		// How to fix section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'How to fix it yourself', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= self::get_fix_instructions( $diagnostic_id );
		$content .= '</p>';
		$content .= '</section>';

		// Automatic fix section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'Let WPShadow fix it', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= __( 'WPShadow can automatically apply the recommended fix for this issue. ', 'wpshadow' );
		$content .= __( 'Click the "Fix Now" button on the dashboard when you see this issue.', 'wpshadow' );
		$content .= '</p>';
		$content .= '</section>';

		// Learn more section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'Learn more about this topic', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= self::get_learn_more_text( $diagnostic_id );
		$content .= '</p>';
		$content .= '</section>';

		return array(
			'id'          => 'diagnostic-' . $diagnostic_id,
			'type'        => 'diagnostic',
			'title'       => $title,
			'description' => $description,
			'category'    => $category,
			'status'      => $status,
			'content'     => $content,
			'difficulty'  => 'Beginner',
			'estimated_fix_time' => __( '2-5 minutes', 'wpshadow' ),
		);
	}

	/**
	 * Generate article from treatment data.
	 *
	 * @param string $treatment_id Treatment ID.
	 * @param array  $treatment    Treatment data.
	 * @return array Article data.
	 */
	public static function generate_treatment_article( $treatment_id, $treatment ) {
		$title       = isset( $treatment['title'] ) ? $treatment['title'] : 'Fix';
		$description = isset( $treatment['description'] ) ? $treatment['description'] : '';
		$category    = isset( $treatment['category'] ) ? $treatment['category'] : 'General';

		// Build content sections
		$content = '';

		// What will happen section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'What will be changed', 'wpshadow' ) . '</h2>';
		$content .= '<p>' . wp_kses_post( $description ) . '</p>';
		$content .= '</section>';

		// Why this helps section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'Why this helps', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= self::get_treatment_impact_text( $treatment_id );
		$content .= '</p>';
		$content .= '</section>';

		// Safety & backup section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'Safety & Backup', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= __( 'WPShadow always creates a backup before making any changes. ', 'wpshadow' );
		$content .= __( 'You can undo this fix at any time from the dashboard.', 'wpshadow' );
		$content .= '</p>';
		$content .= '</section>';

		// Manual application section
		$content .= '<section class="wpshadow-kb-section">';
		$content .= '<h2>' . __( 'How to do this manually', 'wpshadow' ) . '</h2>';
		$content .= '<p>';
		$content .= self::get_manual_instructions( $treatment_id );
		$content .= '</p>';
		$content .= '</section>';

		return array(
			'id'          => 'treatment-' . $treatment_id,
			'type'        => 'treatment',
			'title'       => $title,
			'description' => $description,
			'category'    => $category,
			'content'     => $content,
			'difficulty'  => 'Advanced',
			'estimated_fix_time' => __( 'Automatic', 'wpshadow' ),
		);
	}

	/**
	 * Get "why it matters" text for diagnostic.
	 *
	 * @param string $diagnostic_id Diagnostic ID.
	 * @param string $category      Category.
	 * @return string Explanation text.
	 */
	private static function get_why_matters_text( $diagnostic_id, $category ) {
		$reasons = array(
			'security'      => __( 'This helps keep your WordPress site secure from attackers and protects your users\' data.', 'wpshadow' ),
			'performance'   => __( 'Fixing this will make your site faster, which improves user experience and SEO rankings.', 'wpshadow' ),
			'accessibility' => __( 'This ensures your site is usable by everyone, including people with disabilities.', 'wpshadow' ),
			'seo'           => __( 'Search engines like Google rank sites higher when this is properly configured.', 'wpshadow' ),
			'maintenance'   => __( 'Keeping this up to date prevents problems before they happen.', 'wpshadow' ),
		);

		return isset( $reasons[ $category ] ) ? $reasons[ $category ] : $reasons['maintenance'];
	}

	/**
	 * Get fix instructions for diagnostic.
	 *
	 * @param string $diagnostic_id Diagnostic ID.
	 * @return string Instructions.
	 */
	private static function get_fix_instructions( $diagnostic_id ) {
		$instructions = array(
			'ssl'                   => __( '1. Log into your hosting control panel (cPanel, Plesk, etc.)\n2. Look for "SSL Certificates"\n3. Click "Install" and select your domain\n4. Follow the prompts to complete installation', 'wpshadow' ),
			'debug-mode'            => __( '1. Open wp-config.php via FTP or File Manager\n2. Find "WP_DEBUG" and change to false\n3. Find "WP_DEBUG_DISPLAY" and change to false\n4. Save the file', 'wpshadow' ),
			'memory-limit'          => __( '1. Open wp-config.php via FTP or File Manager\n2. Add this line: define( \'WP_MEMORY_LIMIT\', \'256M\' );\n3. Save the file', 'wpshadow' ),
			'permalinks'            => __( '1. Go to Settings > Permalinks in WordPress admin\n2. Choose a permalink structure (usually Post name)\n3. Click Save Changes', 'wpshadow' ),
			'tagline'               => __( '1. Go to Settings > General in WordPress admin\n2. Update the Tagline field\n3. Click Save Changes', 'wpshadow' ),
		);

		return isset( $instructions[ $diagnostic_id ] ) ? $instructions[ $diagnostic_id ] : __( 'Please refer to the WPShadow dashboard for specific fix instructions for this issue.', 'wpshadow' );
	}

	/**
	 * Get treatment impact text.
	 *
	 * @param string $treatment_id Treatment ID.
	 * @return string Impact text.
	 */
	private static function get_treatment_impact_text( $treatment_id ) {
		return __( 'This change will improve your site according to WordPress best practices and industry standards.', 'wpshadow' );
	}

	/**
	 * Get manual instructions for treatment.
	 *
	 * @param string $treatment_id Treatment ID.
	 * @return string Instructions.
	 */
	private static function get_manual_instructions( $treatment_id ) {
		return __( 'If you prefer to make this change manually, please contact your hosting provider for detailed instructions.', 'wpshadow' );
	}
}
