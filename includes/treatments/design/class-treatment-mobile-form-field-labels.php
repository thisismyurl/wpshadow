<?php
/**
 * Mobile Form Field Labels Treatment
 *
 * Validates that all form inputs have associated labels for accessibility
 * and mobile usability (tap-to-focus).
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1205
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Field Labels Treatment Class
 *
 * Ensures all form inputs have proper <label> elements associated via for/id.
 * Labels provide larger tap targets on mobile and are essential for screen readers.
 *
 * WCAG Reference: 3.3.2 Labels or Instructions (Level A)
 *
 * @since 1.602.1205
			$type = $tag_name === 'input' ? 'text' : $tag_name;

			if ( preg_match( '/id=["\']([^"\']+)["\']/', $attributes, $id_match ) ) {
				$id = $id_match[1];
			}
			if ( preg_match( '/name=["\']([^"\']+)["\']/', $attributes, $name_match ) ) {
				$name = $name_match[1];
			}
			if ( preg_match( '/type=["\']([^"\']+)["\']/', $attributes, $type_match ) ) {
				$type = $type_match[1];
			}

			// Skip if no id and no name (can't be labeled).
			if ( empty( $id ) && empty( $name ) ) {
				continue;
			}

			// Check if labeled.
			$has_label = false;
			if ( ! empty( $id ) ) {
				// Look for <label for="id">.
				$has_label = (bool) preg_match( '/<label[^>]*for=["\']' . preg_quote( $id, '/' ) . '["\'][^>]*>/i', $html );
			}

			// Check for aria-label or aria-labelledby.
			$has_aria = (bool) preg_match( '/aria-(?:label|labelledby)=["\']/', $attributes );

			// If no label and no ARIA, it's unlabeled.
			if ( ! $has_label && ! $has_aria ) {
				$unlabeled[] = array(
					'id'       => $id,
					'name'     => $name,
					'type'     => $type,
					'has_aria' => $has_aria,
				);
			}
		}

		return $unlabeled;
	}
}
