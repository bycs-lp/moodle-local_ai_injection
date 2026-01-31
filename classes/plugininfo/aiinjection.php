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

use core\plugininfo\base;
use core_plugin_manager;

/**
 * Plugin info class for aiinjection subplugins.
 */
class aiinjection extends base {
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

    #[\Override]
    public static function get_enabled_plugins() {
        global $DB;

        $plugins = core_plugin_manager::instance()->get_installed_plugins('aiinjection');
        if (!$plugins) {
            return [];
        }
        $installed = [];
        foreach ($plugins as $plugin => $version) {
            $installed[] = 'aiinjection_' . $plugin;
        }

        [$insql, $params] = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
        $disabled = $DB->get_records_select(
            'config_plugins',
            "plugin $insql AND name = 'enabled' AND value = '0'",
            $params,
            'plugin ASC'
        );
        foreach ($disabled as $conf) {
            unset($plugins[explode('_', $conf->plugin, 2)[1]]);
        }

        $enabled = [];
        foreach ($plugins as $plugin => $version) {
            $enabled[$plugin] = $plugin;
        }

        return $enabled;
    }

    #[\Override]
    public static function enable_plugin(string $pluginname, int $enabled): bool {
        $haschanged = false;

        $plugin = 'aiinjection_' . $pluginname;
        $oldvalue = get_config($plugin, 'enabled');

        // Only set value if there is no config setting or if the value is different from the previous one.
        if ($oldvalue === false || (intval($oldvalue) !== $enabled)) {
            set_config('enabled', $enabled, $plugin);
            $haschanged = true;

            add_to_config_log('enabled', $oldvalue, $enabled, $plugin);
            core_plugin_manager::reset_caches();
        }

        return $haschanged;
    }

    /**
     * Enable or disable this plugin instance.
     *
     * @param bool $newstate True to enable, false to disable
     */
    public function set_enabled($newstate = true): void {
        self::enable_plugin($this->name, $newstate ? 1 : 0);
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

    #[\Override]
    public function uninstall(\progress_trace $progress): bool {
        global $DB;

        // Remove all configuration settings for this subplugin.
        $DB->delete_records('config_plugins', ['plugin' => $this->component]);

        $progress->output("Removed configuration for {$this->component}");

        return true;
    }
}
