/**
 * KaisarCode Script
 * Summary: Main JavaScript for the KaisarCode site.
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 */
import core from '../../../core/views/js/script.js';

(function() {
    'use strict';

    document.addEventListener('click', function(e) {
        var article = e.target.closest('article[data-href]');
        if (article) {
            location.href = article.dataset.href;
        }
    });
})();
