{{-- Reusable Language Dropdown Component with Flags --}}
{{-- Usage: @include('components.language-dropdown', ['selectId' => 'custom_translate_select_header', 'flagPreviewId' => 'flag-preview-header']) --}}

@php
    $selectId = $selectId ?? 'custom_translate_select';
    $flagPreviewId = $flagPreviewId ?? 'flag-preview-default';
    $containerClass = $containerClass ?? 'lang-select-container';
    $selectClass = $selectClass ?? 'form-select form-select-sm';
    $selectStyle = $selectStyle ?? 'min-width: 100px;';
    $dropdownId = $selectId . '_custom';
@endphp

<style>
    .{{ $containerClass }} {
        position: relative;
        display: inline-block;
    }
    
    /* Custom Dropdown Button */
    .{{ $containerClass }} .custom-dropdown-btn {
        display: flex;
        align-items: center;
        padding: 8px 30px 8px 35px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        min-width: 100px;
        position: relative;
        user-select: none;
        transition: all 0.2s;
    }
    
    .{{ $containerClass }} .custom-dropdown-btn:hover {
        border-color: #999;
    }
    
    .{{ $containerClass }} .custom-dropdown-btn.open {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    /* Flag in selected display */
    .{{ $containerClass }} .selected-flag {
        position: absolute;
        left: 8px;
        width: 20px;
        height: 15px;
        background-size: cover;
        background-position: center;
    }
    
    /* Selected text */
    .{{ $containerClass }} .selected-text {
        flex: 1;
        font-size: 14px;
        color: #333;
    }
    
    /* Arrow indicator */
    .{{ $containerClass }} .dropdown-arrow {
        position: absolute;
        right: 8px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #666;
        transition: transform 0.3s ease;
    }
    
    .{{ $containerClass }} .custom-dropdown-btn.open .dropdown-arrow {
        transform: rotate(180deg);
    }
    
    /* Dropdown list */
    .{{ $containerClass }} .custom-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.3s ease, opacity 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .{{ $containerClass }} .custom-dropdown-list.open {
        max-height: 300px;
        opacity: 1;
    }
    
    /* Dropdown option */
    .{{ $containerClass }} .dropdown-option {
        display: flex;
        align-items: center;
        padding: 10px 35px;
        cursor: pointer;
        transition: background-color 0.2s;
        position: relative;
    }
    
    .{{ $containerClass }} .dropdown-option:hover {
        background-color: #f5f5f5;
    }
    
    .{{ $containerClass }} .dropdown-option.selected {
        background-color: #007bff;
        color: #fff;
    }
    
    /* Flag in option */
    .{{ $containerClass }} .option-flag {
        position: absolute;
        left: 8px;
        width: 20px;
        height: 15px;
        background-size: cover;
        background-position: center;
    }
    
    /* Option text */
    .{{ $containerClass }} .option-text {
        font-size: 14px;
        color: inherit;
    }
    
    /* Hidden native select for Google Translate compatibility */
    .{{ $containerClass }} select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }
</style>

<div class="{{ $containerClass }}">
    <!-- Hidden native select for Google Translate -->
    <select id="{{ $selectId }}" class="{{ $selectClass }}" style="display: none;">
        <option value="en" data-flag="gb">EN</option>
        <option value="es" data-flag="es">ES</option>
        <option value="fr" data-flag="fr">FR</option>
        <option value="ar" data-flag="sa">AR</option>
        <option value="hi" data-flag="in">HI</option>
        <option value="pt" data-flag="pt">PT</option>
    </select>
    
    <!-- Custom dropdown -->
    <div class="custom-dropdown-wrapper" id="{{ $dropdownId }}">
        <div class="custom-dropdown-btn" role="button" tabindex="0">
            <span class="selected-flag fi fi-gb" id="{{ $flagPreviewId }}"></span>
            <span class="selected-text">EN</span>
            <span class="dropdown-arrow"></span>
        </div>
        <div class="custom-dropdown-list">
            <div class="dropdown-option selected" data-value="en" data-flag="gb">
                <span class="option-flag fi fi-gb"></span>
                <span class="option-text">EN</span>
            </div>
            <div class="dropdown-option" data-value="es" data-flag="es">
                <span class="option-flag fi fi-es"></span>
                <span class="option-text">ES</span>
            </div>
            <div class="dropdown-option" data-value="fr" data-flag="fr">
                <span class="option-flag fi fi-fr"></span>
                <span class="option-text">FR</span>
            </div>
            <div class="dropdown-option" data-value="ar" data-flag="sa">
                <span class="option-flag fi fi-sa"></span>
                <span class="option-text">AR</span>
            </div>
            <div class="dropdown-option" data-value="hi" data-flag="in">
                <span class="option-flag fi fi-in"></span>
                <span class="option-text">HI</span>
            </div>
            <div class="dropdown-option" data-value="pt" data-flag="pt">
                <span class="option-flag fi fi-pt"></span>
                <span class="option-text">PT</span>
            </div>
        </div>
    </div>
</div>
@include('components.google-translate')
<script>
    (function() {
        var selectId = '{{ $selectId }}';
        var flagPreviewId = '{{ $flagPreviewId }}';
        var dropdownId = '{{ $dropdownId }}';
        
        function initLanguageDropdown() {
            var select = document.getElementById(selectId);
            var btn = document.querySelector('#' + dropdownId + ' .custom-dropdown-btn');
            var list = document.querySelector('#' + dropdownId + ' .custom-dropdown-list');
            var options = document.querySelectorAll('#' + dropdownId + ' .dropdown-option');
            var selectedFlag = document.getElementById(flagPreviewId);
            var selectedText = document.querySelector('#' + dropdownId + ' .selected-text');
            
            if (!select || !btn || !list || !selectedFlag || !selectedText) return;
            
            var languages = {
                'en': { flag: 'gb', text: 'EN' },
                'es': { flag: 'es', text: 'ES' },
                'fr': { flag: 'fr', text: 'FR' },
                'ar': { flag: 'sa', text: 'AR' },
                'hi': { flag: 'in', text: 'HI' },
                'pt': { flag: 'pt', text: 'PT' }
            };
            
            function updateDisplay(value) {
                var lang = languages[value] || languages['en'];
                selectedFlag.className = 'selected-flag fi fi-' + lang.flag;
                selectedText.textContent = lang.text;
                
                // Update selected option highlight
                options.forEach(function(opt) {
                    if (opt.getAttribute('data-value') === value) {
                        opt.classList.add('selected');
                    } else {
                        opt.classList.remove('selected');
                    }
                });
            }
            
            function closeDropdown() {
                btn.classList.remove('open');
                list.classList.remove('open');
            }
            
            function openDropdown() {
                btn.classList.add('open');
                list.classList.add('open');
            }
            
            function selectLanguage(value) {
                select.value = value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                updateDisplay(value);
                closeDropdown();
                
                // Apply Google Translate
                if (window.__googleTranslateApply) {
                    window.__googleTranslateApply(value);
                }
            }
            
            // Button click - toggle dropdown
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (btn.classList.contains('open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });
            
            // Option click
            options.forEach(function(option) {
                option.addEventListener('click', function() {
                    var value = this.getAttribute('data-value');
                    selectLanguage(value);
                });
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !list.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            // Keyboard support
            btn.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    if (btn.classList.contains('open')) {
                        closeDropdown();
                    } else {
                        openDropdown();
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });
            
            // Sync with native select changes (from Google Translate)
            select.addEventListener('change', function() {
                updateDisplay(this.value);
            });
            
            // Initial update
            updateDisplay(select.value);
            
            // Sync when Google Translate sets the value
            setTimeout(function() {
                updateDisplay(select.value);
            }, 500);
            
            // Watch for value changes
            if (window.MutationObserver) {
                var observer = new MutationObserver(function() {
                    updateDisplay(select.value);
                });
                if (select.value) {
                    observer.observe(select, { attributes: true, attributeFilter: ['value'] });
                }
            }
            
            // Initialize with Google Translate
            if (window.__googleTranslateInitSelects) {
                window.__googleTranslateInitSelects([selectId]);
                
                // Update display after Google Translate sets the value
                setTimeout(function() {
                    updateDisplay(select.value);
                }, 600);
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLanguageDropdown);
        } else {
            initLanguageDropdown();
        }
    })();
</script>

