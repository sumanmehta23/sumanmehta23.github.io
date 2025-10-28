{{-- Google Translate Component - Reusable across all pages --}}
<style>
    #google_translate_container { position: fixed; top: 10px; right: 10px; z-index: 2000; }
    /* Hide Google automatic top banner completely */
    .goog-te-banner-frame.skiptranslate { display: none !important; }
    .goog-te-banner-frame { display: none !important; }
    .VIpgJd-ZVi9od-ORHb-OEVmcd { display: none !important; }
    body, html { top: 0 !important; }
    /* Show only the dropdown, hide Google branding text/icon */
    #google_translate_element select { min-width: 180px; }
    .goog-logo-link, .goog-te-gadget span { display:none !important; }
    .goog-te-gadget { color: transparent !important; }
</style>

<div id="google_translate_container">
    <!-- Hidden Google widget (required for scripts) -->
    <div id="google_translate_element" aria-hidden="true" style="display:none"></div>
</div>

<script type="text/javascript">
    function googleTranslateElementInit() {
        if (document.querySelector('#google_translate_element .goog-te-combo')) return; // Already initialized
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'es,fr,ar,hi,pt,en',
            autoDisplay: false,
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
    
    (function(){
        if (!window.__googleTranslateLoaded) {
            var gt = document.createElement('script');
            gt.type = 'text/javascript';
            gt.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            document.body.appendChild(gt);
            window.__googleTranslateLoaded = true;
        }
        
        // Shared cookie and translation functions
        function setCookie(name, value, days, domain){
            var d=new Date(); d.setTime(d.getTime()+days*24*60*60*1000);
            var cookie = name+"="+encodeURIComponent(value)+";expires="+d.toUTCString()+";path=/";
            if (domain) cookie += ";domain="+domain;
            document.cookie = cookie;
        }
        function getCookie(name){
            var m=document.cookie.match(new RegExp('(?:^|; )'+name.replace(/([.$?*|{}()\[\]\\\/\+^])/g,'\\$1')+'=([^;]*)'));
            return m?decodeURIComponent(m[1]):null;
        }
        function currentLang(){
            var v=getCookie('googtrans');
            return v? (v.split('/')[2]||'en') : 'en';
        }
        function applyTranslation(code){
            var val='/en/'+code;
            setCookie('googtrans', val, 365);
            var host = location.hostname;
            if (!/^\d+\.\d+\.\d+\.\d+$/.test(host)) {
                setCookie('googtrans', val, 365, '.'+host);
            }
            var combo=document.querySelector('select.goog-te-combo');
            if(combo){ combo.value=code; combo.dispatchEvent(new Event('change')); return; }
            setTimeout(function(){
                var c2=document.querySelector('select.goog-te-combo');
                if(c2){ c2.value=code; c2.dispatchEvent(new Event('change')); }
                else location.reload();
            },300);
        }
        
        // Make functions globally available
        window.__googleTranslateApply = applyTranslation;
        window.__googleTranslateCurrent = currentLang;
        
        // Initialize all custom selects if they exist
        function initCustomSelects(selectIds) {
            var current = currentLang();
            selectIds.forEach(function(id) {
                var sel = document.getElementById(id);
                if(sel){
                    sel.value = current;
                    sel.addEventListener('change', function(){ applyTranslation(this.value); });
                }
            });
        }
        
        if(document.readyState === 'loading'){
            document.addEventListener('DOMContentLoaded', function(){
                window.__googleTranslateInitSelects = initCustomSelects;
            });
        } else {
            window.__googleTranslateInitSelects = initCustomSelects;
        }
    })();
</script>

