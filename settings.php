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
    // Create category for AI injection settings.
    $aiinjectioncategory = new admin_category(
        'local_ai_injection_settings',
        new lang_string('pluginname', 'local_ai_injection')
    );
    $ADMIN->add('localplugins', $aiinjectioncategory);

    // Subplugin management page using core plugin_management_table.
    $aiinjectionsettingpage = new admin_settingpage(
        'aiinjectionpluginsmanagement',
        get_string('managesubplugins', 'local_ai_injection'),
        'moodle/site:config'
    );
    $aiinjectionsettingpage->add(
        new \core_admin\admin\admin_setting_plugin_manager(
            'aiinjection',
            \local_ai_injection\table\aiinjection_admin_table::class,
            'aiinjection_management',
            get_string('subplugintype_aiinjection_plural', 'local_ai_injection')
        )
    );
    $ADMIN->add('local_ai_injection_settings', $aiinjectionsettingpage);

    // Add category for subplugin settings.
    $ADMIN->add(
        'local_ai_injection_settings',
        new admin_category(
            'aiinjectionplugins',
            new lang_string('aiinjectionplugins', 'local_ai_injection')
        )
    );

    // Load settings from all subplugins.
    $plugins = \core_plugin_manager::instance()->get_plugins_of_type('aiinjection');
    foreach ($plugins as $plugin) {
        $plugin->load_settings($ADMIN, 'aiinjectionplugins', $hassiteconfig);
    }
}
