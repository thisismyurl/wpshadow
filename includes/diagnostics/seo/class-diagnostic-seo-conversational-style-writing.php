<?php
declare(strict_types=1);
/**
 * Conversational Style Writing Diagnostic
 *
 * Philosophy: Voice search prefers conversational tone
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Conversational_Style_Writing extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-conversational-style-writing',
            'title' => 'Conversational Writing Style',
            'description' => 'Write in conversational tone matching how people speak for voice search optimization.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/conversational-content/',
            'training_link' => 'https://wpshadow.com/training/voice-friendly-writing/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }

}