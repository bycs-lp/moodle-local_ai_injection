<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AI Injection plugin library - manages AI-powered subplugins for various purposes.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get all available AI injection subplugins.
 * Now using core_component with subplugins.json (Moodle 3.8+).
 *
 * @return array Array of subplugin names and paths
 */
function local_ai_injection_get_subplugins() {
    return \core_component::get_plugin_list('aiinjection');
}

/**
 * Get enabled AI injection subplugins.
 *
 * @return array Array of enabled subplugin names and paths
 */
function local_ai_injection_get_enabled_subplugins() {
    $subplugins = local_ai_injection_get_subplugins();
    $enabled = [];

    foreach ($subplugins as $name => $path) {
        if (get_config('aiinjection_' . $name, 'enabled')) {
            $enabled[$name] = $path;
        }
    }

    return $enabled;
}
