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
 * Subplugin management page for AI injection.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('managelocalai');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('subplugins', 'local_ai_injection'));

$pluginmanager = new \local_ai_injection\plugin_manager();
$subplugins = $pluginmanager::get_available_plugins();

if (empty($subplugins)) {
    echo $OUTPUT->notification(get_string('no_subplugins', 'local_ai_injection'), 'info');
} else {
    $context = [
        'installedsubplugins' => get_string('installedsubplugins', 'local_ai_injection'),
        'subplugins' => [],
    ];

    foreach ($subplugins as $name => $path) {
        $component = 'aiinjection_' . $name;
        $enabled = (bool)get_config($component, 'enabled');
        $context['subplugins'][] = [
            'component' => $component,
            'status' => $enabled ? get_string('enabled', 'local_ai_injection') : get_string('disabled', 'local_ai_injection'),
            'statusclass' => $enabled ? 'text-success' : 'text-warning',
            'pathlabel' => get_string('path', 'local_ai_injection'),
            'path' => $path,
        ];
    }

    echo $OUTPUT->render_from_template('local_ai_injection/subplugins', $context);
}

echo $OUTPUT->footer();
