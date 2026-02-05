/**
 * Auth Token - Multi-tab session support
 * Stores token in sessionStorage and cookies (per-tab isolation)
 */
(function() {
    var AUTH_TOKEN_KEY = 'auth_token';
    var TAB_ID_KEY = 'tab_id';
    var APP_URL = (typeof window.SDO_BACTRACK_APP_URL !== 'undefined') ? window.SDO_BACTRACK_APP_URL : '/SDO-BACtrack';
    var TOKEN_PARAM = (typeof window.SDO_BACTRACK_TOKEN_PARAM !== 'undefined') ? window.SDO_BACTRACK_TOKEN_PARAM : 'auth_token';
    var ADMIN_PREFIX = APP_URL + '/admin/';

    // Generate or retrieve unique tab ID
    function getTabId() {
        var tabId = sessionStorage.getItem(TAB_ID_KEY);
        if (!tabId) {
            tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            try {
                sessionStorage.setItem(TAB_ID_KEY, tabId);
            } catch (e) {}
        }
        return tabId;
    }

    function getCookieName() {
        return TOKEN_PARAM;
    }

    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }

    function setCookie(name, value, hours) {
        var expires = "";
        if (hours) {
            var date = new Date();
            date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        var path = APP_URL + '/';
        document.cookie = name + "=" + (value || "") + expires + "; path=" + path + "; SameSite=Lax";
    }

    function deleteCookie(name) {
        var path = APP_URL + '/';
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=" + path + ";";
    }

    function getTokenFromUrl() {
        var params = new URLSearchParams(window.location.search);
        return params.get(TOKEN_PARAM) || null;
    }

    function storeTokenFromUrl() {
        var token = getTokenFromUrl();
        if (token) {
            try {
                sessionStorage.setItem(AUTH_TOKEN_KEY, token);
                // Store in cookie as fallback (8 hours = session lifetime)
                setCookie(getCookieName(), token, 8);
            } catch (e) {}
        } else {
            // On refresh: check sessionStorage first (per-tab), then cookie
            var storedToken = sessionStorage.getItem(AUTH_TOKEN_KEY);
            if (storedToken) {
                // Restore token to URL immediately so PHP can read it
                var url = new URL(window.location.href);
                url.searchParams.set(TOKEN_PARAM, storedToken);
                window.location.replace(url.toString());
                return;
            }
            // Fallback to cookie if sessionStorage is empty
            var cookieToken = getCookie(getCookieName());
            if (cookieToken) {
                try {
                    sessionStorage.setItem(AUTH_TOKEN_KEY, cookieToken);
                    var url = new URL(window.location.href);
                    url.searchParams.set(TOKEN_PARAM, cookieToken);
                    window.location.replace(url.toString());
                    return;
                } catch (e) {}
            }
        }
    }
    
    function clearAuthCookie() {
        try {
            deleteCookie(getCookieName());
            sessionStorage.removeItem(AUTH_TOKEN_KEY);
        } catch (e) {}
    }
    
    // Clear cookie on logout links
    document.addEventListener('click', function(e) {
        var target = e.target.closest('a[href*="logout"]');
        if (target) {
            clearAuthCookie();
        }
    }, true);

    function getToken() {
        var token = sessionStorage.getItem(AUTH_TOKEN_KEY);
        if (!token) {
            // Fallback to cookie for this tab
            token = getCookie(getCookieName());
            if (token) {
                try {
                    sessionStorage.setItem(AUTH_TOKEN_KEY, token);
                } catch (e) {}
            }
        }
        return token;
    }

    function appendTokenToUrl(url) {
        if (!url || typeof url !== 'string') return url;
        var token = getToken();
        if (!token) return url;
        try {
            var u = new URL(url, window.location.origin);
            u.searchParams.set(TOKEN_PARAM, token);
            return u.pathname + u.search + (u.hash || '');
        } catch (e) {
            var sep = url.indexOf('?') !== -1 ? '&' : '?';
            return url + sep + TOKEN_PARAM + '=' + encodeURIComponent(token);
        }
    }

    function addTokenToLinks() {
        var token = getToken();
        if (!token) return;

        var links = document.querySelectorAll('a[href^="' + ADMIN_PREFIX + '"], a[href^="/' + ADMIN_PREFIX.replace(/^\//, '') + '"]');
        links.forEach(function(a) {
            var href = a.getAttribute('href');
            if (href && href.indexOf(TOKEN_PARAM + '=') === -1) {
                a.setAttribute('href', appendTokenToUrl(href));
            }
        });

        var forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            if (form.querySelector('input[name="' + TOKEN_PARAM + '"]')) return;
            var action = form.getAttribute('action') || window.location.href;
            if (action.indexOf(ADMIN_PREFIX) !== -1 || action.indexOf('/admin/') !== -1 || !form.action) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = TOKEN_PARAM;
                input.value = token;
                form.appendChild(input);
            }
        });
    }

    function buildApiUrl(url) {
        var token = getToken();
        if (!token) return url;
        try {
            var u = new URL(url, window.location.origin);
            u.searchParams.set(TOKEN_PARAM, token);
            return u.pathname + u.search + (u.hash || '');
        } catch (e) {
            var sep = url.indexOf('?') !== -1 ? '&' : '?';
            return url + sep + TOKEN_PARAM + '=' + encodeURIComponent(token);
        }
    }

    function buildPageUrl(pathOrUrl) {
        var token = getToken();
        if (!token) return pathOrUrl;
        try {
            var u = new URL(pathOrUrl, window.location.origin);
            u.searchParams.set(TOKEN_PARAM, token);
            return u.toString();
        } catch (e) {
            var sep = pathOrUrl.indexOf('?') !== -1 ? '&' : '?';
            return pathOrUrl + sep + TOKEN_PARAM + '=' + encodeURIComponent(token);
        }
    }

    window.SDO_BACTRACK_buildApiUrl = buildApiUrl;
    window.SDO_BACTRACK_buildPageUrl = buildPageUrl;
    window.SDO_BACTRACK_getToken = getToken;

    function init() {
        storeTokenFromUrl();
        addTokenToLinks();

        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form.tagName === 'FORM' && !form.querySelector('input[name="' + TOKEN_PARAM + '"]')) {
                var token = getToken();
                if (token) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = TOKEN_PARAM;
                    input.value = token;
                    form.appendChild(input);
                }
            }
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
