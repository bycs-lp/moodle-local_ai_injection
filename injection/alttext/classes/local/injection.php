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
use local_ai_injection\local\ai_manager_wrapper;
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
     * The prompt template for AI alt text generation.
     * {LANGUAGE} placeholder will be replaced with the user's language name in English.
     */
    public const PROMPT_TEMPLATE = 'Generate a precise alt text for the image in the following language: {LANGUAGE}. ' .
        'Describe briefly and factually what is seen in the image. ' .
        'The alt text should be understandable for visually impaired people. ' .
        'Do not use any special characters, especially no quotes.';

    /** @var ai_manager_wrapper The AI manager wrapper instance. */
    private ai_manager_wrapper $aimanagerwrapper;

    /**
     * Constructor.
     *
     * Uses dependency injection to get the AI manager wrapper.
     */
    public function __construct() {
        $this->aimanagerwrapper = \core\di::get(ai_manager_wrapper::class);
    }

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

        $aiconfig = $this->aimanagerwrapper->get_ai_config(
            $USER,
            $PAGE->context->id,
            null,
            [self::PURPOSE]
        );

        return [
            'aiconfig' => $aiconfig,
            'contextid' => $PAGE->context->id,
            'prompt' => $this->get_prompt(),
        ];
    }

    /**
     * Get the prompt for AI alt text generation.
     *
     * The prompt is always in English with the target language name inserted.
     * This ensures consistent AI behavior regardless of the user's UI language.
     *
     * @return string The complete prompt with language inserted
     */
    private function get_prompt(): string {
        $languagename = $this->get_language_name_in_english();
        return str_replace('{LANGUAGE}', $languagename, self::PROMPT_TEMPLATE);
    }

    /**
     * Get the current user's language name in English.
     *
     * Uses PHP's Locale class to get the display name of the current language in English.
     *
     * @return string Language name in English (e.g., 'German', 'French')
     */
    private function get_language_name_in_english(): string {
        $currentlang = current_language();
        // Get the two-letter language code.
        $langcode = substr($currentlang, 0, 2);
        // Use PHP Intl extension to get language name in English.
        $languagename = \Locale::getDisplayLanguage($langcode, 'en');
        return $languagename ?: 'English';
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
        if (!has_capability('aiinjection/alttext:use', $PAGE->context)) {
            return false;
        }

        // Get AI configuration from local_ai_manager.
        $aiconfig = $this->aimanagerwrapper->get_ai_config(
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
