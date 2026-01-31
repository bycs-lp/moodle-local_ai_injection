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

namespace local_ai_injection;

/**
 * Plugin manager for AI injection subplugins.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_manager {
    /**
     * Get all available AI injection subplugins.
     * Now using core_component with subplugins.json (Moodle 3.8+).
     *
     * @return array Array of subplugin names and paths
     */
    public static function get_available_plugins() {
        return \core_component::get_plugin_list('aiinjection');
    }

    /**
     * Get enabled AI injection subplugins.
     *
     * @return array Array of enabled subplugin names and info
     */
    public static function get_enabled_plugins() {
        $plugins = self::get_available_plugins();
        $enabled = [];

        foreach ($plugins as $name => $path) {
            if (get_config('aiinjection_' . $name, 'enabled')) {
                $enabled[$name] = $path;
            }
        }

        return $enabled;
    }

    /**
     * Check if a specific subplugin is enabled.
     *
     * @param string $name The subplugin name
     * @return bool
     */
    public static function is_plugin_enabled($name) {
        return (bool)get_config('aiinjection_' . $name, 'enabled');
    }

    /**
     * Get plugin info for a specific subplugin.
     *
     * @param string $name The subplugin name
     * @return object|null Plugin info or null if not found
     */
    public static function get_plugin_info($name) {
        $plugins = self::get_available_plugins();

        if (isset($plugins[$name])) {
            $versionfile = $plugins[$name] . '/version.php';
            if (file_exists($versionfile)) {
                $plugin = new \stdClass();
                include($versionfile);
                return $plugin;
            }
        }

        return null;
    }
}
