/**
 * PWA Component - Handles service worker and install prompt
 */
const pwa = function () {
    // Load Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker
                .register('/worker.js');
        });
    }

    // Prompt installer
    var installprompt;
    var installbutton = document.querySelector("#install-pwa");
    installbutton && installbutton.setAttribute("hidden", true);

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        installprompt = event;

        // Exec install
        if (installbutton) {
            installbutton.removeAttribute("hidden");
            installbutton.addEventListener("click", function () {
                installprompt && installprompt.prompt();
            });
        }
    });

    // Remove button after app installed
    window.addEventListener('appinstalled', function (event) {
        installprompt = null;
        installbutton && installbutton.setAttribute("hidden", true);
    });
};

export default pwa;
