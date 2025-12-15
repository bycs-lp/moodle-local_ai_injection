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
 * Settings for local_ai_injection plugin.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Settings page with per-subplugin toggles; restricted by capability.
    $settings = new admin_settingpage(
        'local_ai_injection',
        get_string('pluginname', 'local_ai_injection'),
        'local/ai_injection:manage'
    );

    $pluginmanager = new \local_ai_injection\plugin_manager();
    $subplugins = $pluginmanager::get_available_plugins();

    foreach ($subplugins as $name => $path) {
        $component = 'aiinjection_' . $name;
        $componentname = get_string('pluginname', $component);
        $componentdesc = get_string('plugin_desc', $component);
        $settingname = $component . '/enabled';
        $visiblename = get_string('enable_subplugin', 'local_ai_injection', $componentname);
        $description = $componentdesc;
        $settings->add(new admin_setting_configcheckbox($settingname, $visiblename, $description, 0));
    }

    $ADMIN->add('localplugins', $settings);
}
