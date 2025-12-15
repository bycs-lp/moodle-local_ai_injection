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
 * Plugin info class for AI injection subplugins.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_injection\plugininfo;

/**
 * Plugin info class for aiinjection subplugins.
 */
class aiinjection extends \core\plugininfo\base {
    /**
     * Should there be a way to uninstall the plugin via the administration UI.
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * Return the default display name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', $this->component);
    }

    /**
     * Return URL used for management of plugins of this type.
     *
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new \moodle_url('/admin/settings.php', ['section' => 'local_ai_injection']);
    }

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     *
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_enabled_plugins() {
        $plugins = \core_component::get_plugin_list('aiinjection');
        $enabled = [];

        foreach ($plugins as $plugin => $dir) {
            if (get_config('aiinjection_' . $plugin, 'enabled')) {
                $enabled[$plugin] = $plugin;
            }
        }

        return $enabled;
    }

    /**
     * Enable or disable this plugin.
     *
     * @param bool $newstate
     */
    public function set_enabled($newstate = true) {
        set_config('enabled', $newstate ? 1 : 0, $this->component);
    }

    /**
     * Return true if this plugin is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        return get_config($this->component, 'enabled') ? true : false;
    }

    /**
     * Get the settings section name.
     *
     * @return null|string the settings section name.
     */
    public function get_settings_section_name() {
        return $this->component;
    }

    /**
     * Load the global settings for this plugin.
     *
     * @param \part_of_admin_tree $adminroot
     * @param string $parentnodename
     * @param bool $hassiteconfig whether the current user has moodle/site:config capability
     */
    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig) {
            return;
        }

        $section = $this->get_settings_section_name();

        $settings = null;
        $settingsfile = $this->full_path('settings.php');
        if (file_exists($settingsfile)) {
            $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config', $this->is_enabled() === false);
            include($settingsfile); // This may update $settings.
            if ($settings) {
                $ADMIN->add($parentnodename, $settings);
            }
        }
    }
}
