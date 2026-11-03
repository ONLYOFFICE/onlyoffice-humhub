/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

humhub.module('onlyoffice.cspViolation', function () {

    const isDocsApiUrl = function (url) {
        return /\/web-apps\/apps\/api\/documents\/api\.js$/.test(new URL(url, window.location.href).pathname);
    };

    // Stops HumHub from reloading the page by hiding the CSP violation of the async function check in the Docs api.js.
    window.addEventListener('securitypolicyviolation', function (evt) {
        if (evt.blockedURI === 'eval' && isDocsApiUrl(evt.sourceFile)) {
            evt.stopImmediatePropagation();
        }
    }, true);

});
