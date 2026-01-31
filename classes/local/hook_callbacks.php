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

namespace local_ai_injection\local;

use core\hook\output\before_footer_html_generation;

/**
 * Hook callbacks for local_ai_injection.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Callback to initialize all AI injection subplugins before footer.
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $CFG;

        // Use plugin manager to get enabled subplugins.
        $pluginmanager = new \local_ai_injection\plugin_manager();
        $subplugins = $pluginmanager::get_enabled_plugins();

        foreach ($subplugins as $name => $path) {
            // Look for injection class in subplugin.
            $classname = 'aiinjection_' . $name . '\local\injection';

            if (class_exists($classname)) {
                try {
                    $injection = \core\di::get($classname);
                    $injection->inject_javascript();
                } catch (\Exception $e) {
                    // Log error but don't break the page.
                    debugging('Error initializing AI injection subplugin ' . $name . ': ' . $e->getMessage());
                }
            }
        }
    }
}
