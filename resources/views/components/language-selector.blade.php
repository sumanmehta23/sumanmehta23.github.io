{{-- Language Selector Dropdown Component - For header sections --}}
<style>
    .lang-select-container {
        position: relative;
        display: inline-block;
    }
    .lang-select-container .flag-preview {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        z-index: 10;
        width: 20px;
        height: 15px;
        background-size: cover;
        background-position: center;
        display: inline-block;
    }
    .lang-select-container select {
        padding-left: 35px !important;
    }
</style>
<div class="lang-select-container">
    <span class="flag-preview fi fi-gb" id="flag-preview-header" style="display:block;"></span>
    <select id="custom_translate_select_header" class="form-select form-select-sm" style="min-width: 100px;">
        <option value="en" data-flag="gb">EN</option>
        <option value="es" data-flag="es">ES</option>
        <option value="fr" data-flag="fr">FR</option>
        <option value="ar" data-flag="sa">AR</option>
        <option value="hi" data-flag="in">HI</option>
        <option value="pt" data-flag="pt">PT</option>
    </select>
</div>
<script>
    // Update flag icon when language changes
    document.addEventListener('DOMContentLoaded', function(){
        var select = document.getElementById('custom_translate_select_header');
        var flagPreview = document.getElementById('flag-preview-header');
        if (!select || !flagPreview) return;
        
        function updateFlag() {
            var selected = select.options[select.selectedIndex];
            var flagCode = selected ? selected.getAttribute('data-flag') : 'gb';
            flagPreview.className = 'flag-preview fi fi-' + flagCode;
            flagPreview.style.display = 'block';
        }
        
        // Initial flag update
        updateFlag();
        
        // Update flag when selection changes
        select.addEventListener('change', updateFlag);
        
        // Sync flag when Google Translate sets the value
        setTimeout(function() {
            updateFlag();
        }, 500);
    });
</script>


<script>
    // Initialize this dropdown using the shared Google Translate functions
    document.addEventListener('DOMContentLoaded', function(){
        if (window.__googleTranslateInitSelects) {
            window.__googleTranslateInitSelects(['custom_translate_select_header']);
        }
    });
</script>

