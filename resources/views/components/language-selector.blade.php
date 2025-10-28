{{-- Language Selector Dropdown Component - For header sections --}}
<select id="custom_translate_select_header" class="form-select form-select-sm" style="min-width: 160px;">
    <option value="en">English</option>
    <option value="es">Spanish</option>
    <option value="fr">French</option>
    <option value="ar">Arabic</option>
    <option value="hi">Hindi</option>
    <option value="pt">Portuguese</option>
</select>

<script>
    // Initialize this dropdown using the shared Google Translate functions
    document.addEventListener('DOMContentLoaded', function(){
        if (window.__googleTranslateInitSelects) {
            window.__googleTranslateInitSelects(['custom_translate_select_header']);
        }
    });
</script>

