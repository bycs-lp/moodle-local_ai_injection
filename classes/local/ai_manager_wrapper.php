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
     * @param string|null $tenant The tenant identifier, null to use the tenant of the current user (recommended). Passing a
     *  tenant sets it as current tenant of the local_ai_manager for the rest of the PHP process (outside of web services it is
     *  not being reset automatically, e.g. in cron), so the caller has to check the access to the tenant.
     * @param array $purposes The purposes to check
     * @return array The AI configuration
     */
    public function get_ai_config(stdClass $user, int $contextid, ?string $tenant, array $purposes): array {
        return ai_manager_utils::get_ai_config($user, $contextid, $tenant, $purposes);
    }
}
