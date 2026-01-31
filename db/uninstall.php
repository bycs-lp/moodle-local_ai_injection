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
 * Uninstall script for local_ai_injection.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure.
 *
 * @return bool True on success
 */
function xmldb_local_ai_injection_uninstall() {
    global $DB;

    // Remove all configuration settings for the plugin.
    $DB->delete_records('config_plugins', ['plugin' => 'local_ai_injection']);

    // Remove configuration settings for all aiinjection subplugins.
    $plugins = \core_component::get_plugin_list('aiinjection');
    foreach ($plugins as $pluginname => $pluginpath) {
        $component = 'aiinjection_' . $pluginname;
        $DB->delete_records('config_plugins', ['plugin' => $component]);
    }

    return true;
}
