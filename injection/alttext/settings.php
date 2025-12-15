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
 * Settings for aiinjection_alttext subplugin.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'aiinjection_alttext',
        get_string('pluginname', 'aiinjection_alttext')
    );

    // Enable/disable setting.
    $settings->add(new admin_setting_configcheckbox(
        'aiinjection_alttext/enabled',
        get_string('enabled', 'aiinjection_alttext'),
        get_string('plugin_desc', 'aiinjection_alttext'),
        1
    ));

    // API key setting.
    $settings->add(new admin_setting_configtext(
        'aiinjection_alttext/apikey',
        get_string('apikey', 'aiinjection_alttext'),
        get_string('apikey_desc', 'aiinjection_alttext'),
        '',
        PARAM_TEXT
    ));

    // CSS selector setting.
    $settings->add(new admin_setting_configtext(
        'aiinjection_alttext/selector',
        get_string('selector', 'aiinjection_alttext'),
        get_string('selector_desc', 'aiinjection_alttext'),
        'img:not([alt]), img[alt=""]',
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);
}
