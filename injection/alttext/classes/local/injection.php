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

namespace aiinjection_alttext\local;

use local_ai_injection\local\base_injection;
use local_ai_manager\ai_manager_utils;

/**
 * AI Alt Text injection class.
 *
 * @package    aiinjection_alttext
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class injection extends base_injection {
    /** @var string The purpose this injection uses from local_ai_manager. */
    private const PURPOSE = 'itt';

    /**
     * Get the subplugin name.
     *
     * @return string
     */
    public function get_subplugin_name(): string {
        return 'aiinjection_alttext';
    }

    /**
     * Get the JS module name for this subplugin.
     *
     * @return string
     */
    public function get_js_module_name(): string {
        return 'aiinjection_alttext/alttext_injection';
    }

    /**
     * Get the configuration parameters for the JavaScript module.
     *
     * @return array
     */
    public function get_js_config(): array {
        global $PAGE, $USER;

        $aiconfig = ai_manager_utils::get_ai_config(
            $USER,
            $PAGE->context->id,
            null,
            [self::PURPOSE]
        );

        return [
            $aiconfig,
        ];
    }

    /**
     * Check if this injection should be active on the current page.
     *
     * Returns true if the general availability is not 'hidden'.
     * For 'available' and 'disabled' states, the JavaScript is injected.
     *
     * @return bool
     */
    public function should_inject(): bool {
        global $PAGE, $USER;

        // Require capability to use the Alt Text feature on the current page context.
        if (!has_capability('local/ai_injection:alttextuse', $PAGE->context)) {
            return false;
        }

        // Get AI configuration from local_ai_manager.
        $aiconfig = ai_manager_utils::get_ai_config(
            $USER,
            $PAGE->context->id,
            null,
            [self::PURPOSE]
        );

        // If general availability is 'hidden', do not inject at all.
        if ($aiconfig['availability']['available'] === ai_manager_utils::AVAILABILITY_HIDDEN) {
            return false;
        }

        // If purpose availability is 'hidden', do not inject.
        if (
            !empty($aiconfig['purposes']) &&
                $aiconfig['purposes'][0]['available'] === ai_manager_utils::AVAILABILITY_HIDDEN
        ) {
            return false;
        }

        // For 'available' and 'disabled' states, we inject the JavaScript.
        // The frontend will handle the 'disabled' state appropriately.
        return true;
    }
}
