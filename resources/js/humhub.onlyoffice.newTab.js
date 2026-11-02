/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

humhub.module('onlyoffice.newTab', function (module) {

    const init = function (pjax) {
        if (!pjax) {
            // Capture phase runs before the pjax click handler, which skips links with a target
            document.addEventListener('click', openLinkInNewTab, true);
        }
    };

    const openLinkInNewTab = function (evt) {
        const link = evt.target.closest ? evt.target.closest('a[href]') : null;
        if (link && !link.hasAttribute('target') && isOpenUrl(link.href)) {
            link.setAttribute('target', '_blank');
        }
    };

    const isOpenUrl = function (href) {
        const url = new URL(href, window.location.href);
        return url.origin === window.location.origin
            && (/\/onlyoffice\/open\/?$/.test(url.pathname) || url.searchParams.get('r') === 'onlyoffice/open');
    };

    module.export({
        init: init,
    });

});
