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

use advanced_testcase;
use core\hook\output\before_footer_html_generation;

/**
 * Unit tests for hook_callbacks class.
 *
 * @package    local_ai_injection
 * @copyright  ISB Bayern, 2025
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_ai_injection\local\hook_callbacks
 * @group      local_ai_injection
 */
final class hook_callbacks_test extends advanced_testcase {
    /**
     * Test hook callback executes without errors.
     */
    public function test_before_footer_html_generation_executes(): void {
        $this->resetAfterTest(true);

        // Create a mock renderer.
        $renderer = $this->getMockBuilder(\core\output\renderer_base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hook = new before_footer_html_generation($renderer);

        // Should complete without error, even with no or failing subplugins.
        hook_callbacks::before_footer_html_generation($hook);

        $this->assertTrue(true);
    }
}
