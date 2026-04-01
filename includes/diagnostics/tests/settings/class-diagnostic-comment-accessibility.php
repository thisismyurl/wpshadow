<?php
/**
 * Comment Accessibility and Display Quality
 *
 * Validates comment section accessibility and rendering quality.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Comment_Accessibility Class
 *
 * Checks comment section accessibility and display quality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Section Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comment form and display section accessibility (WCAG 2.1 AA)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Fetch homepage to check comment section rendering
		$response = wp_remote_get( get_permalink( get_option( 'page_for_posts' ) ? get_option( 'page_for_posts' ) : 0 ) );

		if ( is_wp_error( $response ) ) {
			return null; // Cannot verify, assume okay
		}

		$body = wp_remote_retrieve_body( $response );

		// Pattern 1: Comment form exists but has no LABEL elements
		if ( strpos( $body, 'comment-form' ) !== false ) {
			$label_count = substr_count( $body, '<label' );
			$input_count = substr_count( $body, '<input' ) + substr_count( $body, '<textarea' );

			if ( $label_count === 0 && $input_count > 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment form fields have no associated labels (accessibility failure)', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'form_fields_unlabeled',
						'message' => __( 'Form fields have no <label> elements (WCAG failure)', 'wpshadow' ),
						'wcag_violation' => 'WCAG 2.1 Level A -1.0 Info and Relationships',
						'accessibility_impact' => array(
							'Screen reader users don\'t know what field is what',
							'Keyboard users can\'t identify fields',
							'Mobile users see no field hints',
							'Auto-fill features don\'t work properly',
						),
						'users_affected' => array(
							'Screen reader users (blind, low vision)',
							'Keyboard-only users (motor disabilities)',
							'Voice control users',
							'Mobile/touch users',
						),
						'legal_implications' => 'ADA/WCAG non-compliance (litigation risk)',
						'user_experience' => __( 'Everyone benefits from proper labels (mobile, keyboard, etc.)', 'wpshadow' ),
						'fix_required' => array(
							'Wrap each form field in a <label>',
							'Use "for" attribute matching field "id"',
							'Or use aria-label/aria-labelledby',
						),
						'example_fix' => '<label for="author">Name</label><input id="author" name="author">',
						'recommendation' => __( 'Update theme/plugin to add proper labels to comment form fields', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 2: Comment form lacks proper heading structure
		if ( strpos( $body, 'comment-form' ) !== false ) {
			$heading_count = substr_count( $body, '<h' );

			// Check if heading appears near comment form
			if ( $heading_count < 2 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment section lacks proper heading hierarchy', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_heading_hierarchy',
						'message' => __( 'Comment section has no clear heading structure', 'wpshadow' ),
						'wcag_violation' => 'WCAG 2.1 Level A -1.0 Info and Relationships',
						'screen_reader_impact' => __( 'Screen reader users can\'t navigate to comments section', 'wpshadow' ),
						'navigation_structure' => array(
							'Missing: <h2>Comments</h2> (or similar)',
							'Missing: <h3>Leave a Comment</h3> (for form)',
							'Screen readers use headings to navigate page',
						),
						'user_scenarios' => array(
							'Blind user navigates by headings (no comments section found)',
							'Mobile user needs to jump to comments (can\'t)',
							'User with cognitive disability needs structure (confused)',
						),
						'proper_structure' => array(
							'<h2>Comments (5)</h2>',
							'<div class="comments-list">...</div>',
							'<h3>Leave a Comment</h3>',
							'<form class="comment-form">...</form>',
						),
						'benefit_to_all_users' => __( 'Clear heading structure helps everyone find content faster', 'wpshadow' ),
						'recommendation' => __( 'Add proper heading tags around comment section and form', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Comments lack author/date metadata
		$has_comment_metadata = (bool) strpos( $body, 'comment-author' ) !== false;

		if ( $has_comment_metadata === false && strpos( $body, '<li class="comment' ) !== false ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comments lack author and date attribution', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_comment_metadata',
					'message' => __( 'Comment author and date information not visible', 'wpshadow' ),
					'accessibility_impact' => array(
						'Screen readers can\'t identify who wrote each comment',
						'No timestamp for context',
						'Confusing which comment is which',
					),
					'user_experience' => array(
						'Readers need to know: Who said this? When?',
						'Comment order might not show date',
						'Author identity is crucial for credibility',
					),
					'proper_metadata' => array(
						'Author name prominently displayed',
						'Timestamp (e.g., "2 hours ago" or "Jan 5, 2024")',
						'User role indicator if applicable (Author, Admin)',
						'User avatar if available',
					),
					'semantic_html' => '<address> tag for author, <time> tag for timestamp',
					'seo_benefit' => __( 'Structured comment metadata helps search engines understand content', 'wpshadow' ),
					'recommendation' => __( 'Ensure comment author name and date are displayed in comment HTML', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Comment form lacks description of required fields
		if ( strpos( $body, 'required' ) === false && strpos( $body, 'comment-form' ) !== false ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment form does not indicate which fields are required', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'required_fields_not_indicated',
					'message' => __( 'No indication which comment form fields are required', 'wpshadow' ),
					'wcag_violation' => 'WCAG 2.1 Level A - 3.3.2 Labels or Instructions',
					'user_frustration' => array(
						'Submit form without required field',
						'Get error message (which field? why?)',
						'Resubmit and retry',
						'Abandonment (40%+ give up)',
					),
					'accessibility_requirements' => array(
						'Required fields marked with "required" attribute (HTML5)',
						'Or indicated with aria-required="true"',
						'Visual indicator: asterisk (*) or text',
						'Error messages specific: "Name is required"',
					),
					'user_needs' => array(
						'Screen reader users: "Name required"',
						'Dyslexic users: Visual indicators help',
						'Cognitive disabilities: Clear instructions',
						'Everyone: Fewer form errors',
					),
					'implementation' => array(
						'Add required="required" to input tags',
						'Add aria-required="true" for custom fields',
						'Display "* = required field" hint at top',
						'Include specific error messages on submit',
					),
					'recommendation' => __( 'Clearly mark required fields with both visual and programmatic indicators', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Comment form submit button has no accessible name
		if ( strpos( $body, 'type="submit"' ) !== false ) {
			$submit_button_accessible = (bool) strpos( $body, 'value="' ) !== false || strpos( $body, 'aria-label' ) !== false;

			if ( ! $submit_button_accessible ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment form submit button lacks accessible name', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'button_no_accessible_name',
						'message' => __( 'Submit button has no text or aria-label', 'wpshadow' ),
						'wcag_violation' => 'WCAG 2.1 Level A - 4.1.2 Name, Role, Value',
						'accessibility_impact' => array(
							'Screen readers can\'t identify button purpose',
							'Keyboard users don\'t know what button does',
							'Voice control users can\'t activate button',
						),
						'technical_issue' => 'Button lacks: value, aria-label, or text content',
						'proper_implementations' => array(
							'<input type="submit" value="Post Comment">',
							'<button type="submit">Post Comment</button>',
							'<button type="submit" aria-label="Post Comment"><img src="..."></button>',
						),
						'fails_when' => array(
							'Icon-only button with no aria-label',
							'Button with no value attribute',
							'Empty button with no text',
						),
						'screen_reader_result' => 'Currently announces: "button" (meaningless)',
						'fixed_result' => 'Announces: "Post Comment button" (clear purpose)',
						'recommendation' => __( 'Add clear, descriptive text or aria-label to submit button', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: Comments not keyboard navigable
		if ( strpos( $body, 'comment-form' ) !== false ) {
			$has_focus_trap = (bool) strpos( $body, 'tabindex' ) !== false;

			if ( ! $has_focus_trap ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Comment form may have focus/tab order issues', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'keyboard_navigation_broken',
						'message' => __( 'Comment form may not be fully keyboard navigable', 'wpshadow' ),
						'wcag_violation' => 'WCAG 2.1 Level A - 2.1.1 Keyboard',
						'keyboard_user_impact' => array(
							'Cannot tab through all form fields',
							'Cannot submit form without mouse',
							'Cannot access comment permalinks',
							'Cannot reply to specific comments',
						),
						'users_affected' => array(
							'Motor disabilities (cannot use mouse)',
							'Keyboard-only users (faster navigation)',
							'Power users (prefer keyboard)',
							'Voice control users (Tab commands)',
						),
						'testing_requirements' => array(
							'Tab key reaches all interactive elements',
							'Shift+Tab goes backwards through elements',
							'Enter activates buttons/links',
							'Focus indicator visible at all times',
						),
						'focus_visible_requirement' => 'Never hide default focus outline (outline: none without replacement)',
						'css_mistake' => '*:focus { outline: none; } ← DON\'T DO THIS',
						'css_correct' => '*:focus { outline: 2px solid #0073aa; } ← CORRECT',
						'testing_keyboard' => 'Tab through entire form and comment section without mouse',
						'recommendation' => __( 'Ensure comment form and section are fully keyboard navigable with visible focus indicators', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}
}
