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
 * Ultra-optimized AI Alt Text injection for Tiny Media modals.
 *
 * @module     aiinjection_alttext/alttext_injection
 * @copyright  2025 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Log from 'core/log';
import {getString} from 'core/str';
import {alert as moodleAlert} from 'core/notification';
import {makeRequest} from 'local_ai_manager/make_request';
import Templates from 'core/templates';

/** @type {WeakSet} Track modals that have been initialized to prevent duplicate listeners */
const initializedModals = new WeakSet();

/** @type {Object|null} Store AI configuration passed from PHP */
let aiConfig = null;

/**
 * Get current user language from DOM.
 *
 * Extracts the language from the html lang attribute which is set by Moodle.
 * Falls back to 'English' if not found.
 *
 * @returns {string} User's current language name
 */
const getCurrentLanguage = () => {
    const htmlLang = document.documentElement.lang || 'en';
    // Map common language codes to full language names for the AI prompt.
    const languageMap = {
        'de': 'German',
        'en': 'English',
        'fr': 'French',
        'es': 'Spanish',
        'it': 'Italian',
        'pt': 'Portuguese',
        'nl': 'Dutch',
        'pl': 'Polish',
        'ru': 'Russian',
        'ja': 'Japanese',
        'zh': 'Chinese',
        'ko': 'Korean',
        'ar': 'Arabic',
        'tr': 'Turkish',
        'cs': 'Czech',
        'sv': 'Swedish',
        'da': 'Danish',
        'fi': 'Finnish',
        'no': 'Norwegian',
        'hu': 'Hungarian',
        'el': 'Greek',
        'he': 'Hebrew',
        'uk': 'Ukrainian',
    };
    // Extract base language code (e.g., 'de' from 'de-DE').
    const baseLang = htmlLang.split('-')[0].toLowerCase();
    return languageMap[baseLang] || 'English';
};

/**
 * Check if AI purpose is disabled.
 *
 * @returns {boolean} True if purpose is disabled
 */
const isPurposeDisabled = () => {
    if (!aiConfig?.purposes?.[0]) {
        return false;
    }
    return aiConfig.purposes[0].available === 'disabled';
};

/**
 * Get disabled reason message.
 *
 * @returns {string|null} Disabled reason or null
 */
const getDisabledReason = () => {
    if (!isPurposeDisabled()) {
        return null;
    }
    return aiConfig.purposes[0].disabledreason || null;
};

/**
 * Convert image to base64 using fetch and FileReader.
 *
 * Uses fetch to retrieve the image as a blob and FileReader to convert to base64.
 * This approach avoids canvas CORS issues and is more reliable for cross-origin images.
 *
 * @param {string} imageUrl Image URL to convert
 * @returns {Promise<string>} Promise resolving to base64 data URL
 */
const imageToBase64 = async(imageUrl) => {
    const response = await fetch(imageUrl);
    if (!response.ok) {
        throw new Error('Failed to fetch image');
    }
    const blob = await response.blob();
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('FileReader failed'));
        reader.readAsDataURL(blob);
    });
};

/**
 * Display error alert to user.
 *
 * @param {string} message Error message to display
 * @param {string|null} title Optional title for the alert
 */
const showErrorAlert = async(message, title = null) => {
    if (title === null) {
        title = await getString('generateerror', 'aiinjection_alttext');
    }
    await moodleAlert(title, message);
};

/**
 * Extract alt text from AI response
 *
 * @param {Object} data Response data from AI service
 * @returns {string|null} Extracted alt text or null
 */
const extractAltText = (data) => {
    if (data?.error) {
        return null;
    }
    const result = data?.result || data?.data?.result;
    if (!result) {
        return null;
    }

    // Strip HTML tags from result.
    let text = result.replace(/<[^>]*>/g, '').trim();
    // Attempt to decode escaped unicode characters.
    try {
        text = JSON.parse('"' + text.replace(/"/g, '\\"') + '"');
    } catch (e) {
        // Use original if JSON parsing fails.
    }
    return text || null;
};

/**
 * Generate alt text using AI service
 *
 * @param {string} imageUrl Image URL to process
 * @returns {Promise<string|null>} Generated alt text or null
 */
const generateAltText = async(imageUrl) => {
    // Get current user language for the prompt.
    const currentLanguage = getCurrentLanguage();

    const [imageBase64, prompt] = await Promise.all([
        imageToBase64(imageUrl),
        getString('aiprompt', 'aiinjection_alttext', currentLanguage)
    ]);

    const result = await makeRequest('itt', prompt, 'aiinjection_alttext', 0, {image: imageBase64});

    // Check for error response with code.
    if (result?.code && result.code !== 200) {
        const parsedResult = JSON.parse(result.result);
        if (parsedResult.debuginfo) {
            Log.error(parsedResult.debuginfo);
        }
        throw new Error(parsedResult.message || 'AI request failed');
    }

    return extractAltText(Array.isArray(result) ? result[0] : result);
};

/**
 * Button click handler
 *
 * @param {Event} event Click event
 */
const handleButtonClick = async(event) => {
    event.preventDefault();
    const button = event.target.closest('[data-action="generate-alttext"]');
    if (!button) {
        return;
    }
    const modal = button.closest(".modal");
    const textarea = modal.querySelector(".tiny_image_altentry");
    const image = modal.querySelector(".tiny_image_preview");

    if (!textarea || !image?.src || image.src === "data:,") {
        return;
    }

    // Show loading state via template.
    await injectButton(modal, {isloading: true});

    try {
        const altText = await generateAltText(image.src);
        if (altText) {
            textarea.value = altText;
            textarea.dispatchEvent(new Event("input", {bubbles: true}));
            textarea.dispatchEvent(new Event("change", {bubbles: true}));
        }
    } catch (error) {
        Log.error("Alt text generation failed:", error);
        const errorMessage = await getString('generateerrorwithmessage', 'aiinjection_alttext', error.message);
        await showErrorAlert(errorMessage);
    }

    // Reset to normal state via template.
    await injectButton(modal, {isloading: false});
};

/**
 * Inject AI button into modal.
 *
 * Uses Templates.appendNodeContents for proper rendering and JS execution.
 * Handles disabled state by showing a disabled button with tooltip.
 *
 * @param {HTMLElement} modal Modal element to inject button into
 * @param {Object} templateContext Context object for template rendering (optional)
 */
const injectButton = async(modal, templateContext = {}) => {
    // Use data attribute selector for the character count element.
    const countspan = modal.querySelector('[data-region="character-count"]') || modal.querySelector("#the-count");
    if (!countspan) {
        return;
    }

    // Remove existing button if present.
    const existingButton = modal.querySelector('[data-action="generate-alttext"]');
    if (existingButton) {
        existingButton.remove();
    }

    // Add disabled state to template context if purpose is disabled.
    if (isPurposeDisabled()) {
        templateContext.isdisabled = true;
        templateContext.disabledreason = getDisabledReason();
    }

    // Render template using Templates.appendNodeContents which handles JS execution.
    const {html, js} = await Templates.renderForPromise('aiinjection_alttext/ai_button_container', templateContext);
    countspan.insertAdjacentHTML("afterend", html);
    Templates.runTemplateJS(js);

    // Only add event listener if not disabled.
    const button = modal.querySelector('[data-action="generate-alttext"]');
    if (button && !isPurposeDisabled()) {
        button.addEventListener('click', handleButtonClick);
    }
};

/**
 * Initialize MutationObserver for modal detection.
 *
 * Watches for new modals being added to the DOM and injects the AI button.
 * Uses WeakSet to track initialized modals and prevent duplicate listeners.
 */
const initModalObserver = () => {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check if added node is a modal with tiny_image_altentry.
                    if (node.classList?.contains('modal') && node.querySelector('.tiny_image_altentry')) {
                        if (!initializedModals.has(node)) {
                            initializedModals.add(node);
                            injectButton(node);
                        }
                    }
                    // Check if added node contains a modal with tiny_image_altentry.
                    const altentry = node.querySelector?.('.modal .tiny_image_altentry');
                    if (altentry) {
                        const modalElement = altentry.closest('.modal');
                        if (modalElement && !initializedModals.has(modalElement)) {
                            initializedModals.add(modalElement);
                            injectButton(modalElement);
                        }
                    }
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
};

/**
 * Initialize AI alt text injection.
 *
 * @param {Object} config AI configuration object from PHP
 */
export const init = (config) => {
    // Store configuration for later use.
    aiConfig = config;

    // Use MutationObserver for detecting dynamically added modals.
    initModalObserver();

    // Check for existing modals on page load.
    const existingModals = document.querySelectorAll('.modal .tiny_image_altentry');
    existingModals.forEach(textarea => {
        const modal = textarea.closest('.modal');
        if (modal && !initializedModals.has(modal)) {
            initializedModals.add(modal);
            injectButton(modal);
        }
    });
};
