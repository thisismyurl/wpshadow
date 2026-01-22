<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Cumulative_Layout_Shift extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-cls', 'title' => __('Cumulative Layout Shift Monitoring', 'wpshadow'), 'description' => __('CLS > 0.1 = poor UX, ranking penalty. Indicates lazy loading, ads, fonts causing shift.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/layout-stability/', 'training_link' => 'https://wpshadow.com/training/visual-stability/', 'auto_fixable' => false, 'threat_level' => 6]; } }
