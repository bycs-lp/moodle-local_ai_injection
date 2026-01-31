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

use local_ai_manager\ai_manager_utils;
use stdClass;

/**
 * Wrapper class for local_ai_manager API to enable dependency injection and mocking in tests.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_manager_wrapper {
    /**
     * Get AI configuration from local_ai_manager.
     *
     * @param stdClass $user The user object
     * @param int $contextid The context ID
     * @param string|null $component The component (optional)
     * @param array $purposes The purposes to check
     * @return array The AI configuration
     */
    public function get_ai_config(stdClass $user, int $contextid, ?string $component, array $purposes): array {
        return ai_manager_utils::get_ai_config($user, $contextid, $component, $purposes);
    }
}
