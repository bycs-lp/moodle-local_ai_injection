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
import {makeRequest} from 'local_ai_manager/make_request';
import Templates from 'core/templates';

/**
 * Convert image to base64
 * @param {string} imageUrl Image URL to convert
 * @returns {Promise<string>} Promise resolving to base64 data URL
 */
const imageToBase64 = (imageUrl) => new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.naturalWidth || img.width;
        canvas.height = img.naturalHeight || img.height;
        ctx.drawImage(img, 0, 0);
        resolve(canvas.toDataURL('image/jpeg', 0.8));
    };
    img.onerror = () => reject(new Error('Image load failed'));
    img.src = imageUrl;
});

/**
 * Extract alt text from AI response
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

    let text = result.replace(/<[^>]*>/g, '').trim();
    try {
        text = JSON.parse('"' + text.replace(/"/g, '\\"') + '"');
    } catch (e) {
        // Use original if JSON parsing fails
    }
    return text || null;
};

/**
 * Generate alt text using AI service
 * @param {string} imageUrl Image URL to process
 * @returns {Promise<string|null>} Generated alt text or null
 */
const generateAltText = async(imageUrl) => {
    try {
        const [imageBase64, prompt] = await Promise.all([
            imageToBase64(imageUrl),
            getString('aiprompt', 'aiinjection_alttext')
        ]);

        const result = await makeRequest('itt', prompt, 'aiinjection_alttext', 0, {image: imageBase64});
        return extractAltText(Array.isArray(result) ? result[0] : result);
    } catch (error) {
        Log.error('AI generation failed:', error);
        return null;
    }
};

/**
 * Button click handler
 * @param {Event} event Click event
 */
const handleButtonClick = async (event) => {
    event.preventDefault();
    const button = event.target;
    const modal = button.closest(".modal");
    const textarea = modal.querySelector(".tiny_image_altentry");
    const image = modal.querySelector(".tiny_image_preview");

    if (!textarea || !image?.src || image.src === "data:,") {
        return;
    }

    // Show loading state via template
    await injectButton(modal, {isloading: true});

    try {
        const altText = await generateAltText(image.src);
        if (altText) {
            textarea.value = altText;
            textarea.dispatchEvent(new Event("input", { bubbles: true }));
            textarea.dispatchEvent(new Event("change", { bubbles: true }));
        }
    } catch (error) {
        Log.error("Button click error:", error);
    }

    // Reset to normal state via template
    await injectButton(modal, {isloading: false});
};

/**
 * Inject AI button into modal
 * @param {HTMLElement} modal Modal element to inject button into
 * @param {Object} templateContext Context object for template rendering (optional)
 */
const injectButton = async(modal, templateContext = {}) => {
    const countspan = modal.querySelector("#the-count");
    if (!countspan) {
        return;
    }

    let button = modal.querySelector(".ai-alttext-btn");
    if (button) {
        button.remove();
    }

    // Render Mustache-Template with context
    const {html, js} = await Templates.renderForPromise('aiinjection_alttext/ai_button_container', templateContext);
    countspan.insertAdjacentHTML("afterend", html);
    if (js) {
        Templates.runTemplateJS(js);
    }

    // Event Listener für den Button
    button = modal.querySelector('.ai-alttext-btn');
    if (button) {
        button.addEventListener('click', handleButtonClick);
    }
};

/**
 * Initialize MutationObserver for modal detection
 */
const initModalObserver = () => {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Check if added node is a modal with tiny_image_altentry
                    if (node.classList?.contains('modal') && node.querySelector('.tiny_image_altentry')) {
                        setTimeout(() => injectButton(node), 100);
                    }
                    // Check if added node contains a modal with tiny_image_altentry
                    const modal = node.querySelector?.('.modal .tiny_image_altentry');
                    if (modal) {
                        const modalElement = modal.closest('.modal');
                        if (modalElement) {
                            setTimeout(() => injectButton(modalElement), 100);
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
 * Initialize AI alt text injection
 */
export const init = () => {
    // Use robust MutationObserver approach
    initModalObserver();

    // Also check existing modals on page load
    setTimeout(() => {
        const existingModals = document.querySelectorAll('.modal .tiny_image_altentry');
        existingModals.forEach(textarea => {
            const modal = textarea.closest('.modal');
            if (modal) {
                injectButton(modal);
            }
        });
    }, 500);
};
