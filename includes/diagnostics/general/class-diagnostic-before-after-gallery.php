<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Before/After Gallery Present?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Before_After_Gallery extends Diagnostic_Base {
	protected static $slug        = 'before-after-gallery';
	protected static $title       = 'Before/After Gallery Present?';
	protected static $description = 'Looks for work portfolio or transformations.';


	public static function check(): ?array {
		return null; // Content strategy decision
	}

}
