@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            showConfirmButton: true
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Something went wrong',
            text: '{!! session('error') !!}',
            showConfirmButton: true
        });
    </script>
@endif
@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Something went wrong',
            text: '{!! $errors->first() !!}',
            showConfirmButton: true
        });
    </script>
@endif
<script src="/admin_assets/assets/libs/@popperjs/core/umd/popper.min.js"></script>
<script src="/admin_assets/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/admin_assets/assets/js/defaultmenu.min.js"></script>
<script src="/admin_assets/assets/libs/node-waves/waves.min.js"></script>
<script src="/admin_assets/assets/js/sticky.js"></script>
<script src="/admin_assets/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/admin_assets/assets/js/simplebar.js"></script>
<script src="/admin_assets/assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js"></script>
<script src="/admin_assets/assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>
<script src="/admin_assets/assets/libs/flatpickr/flatpickr.min.js"></script>
<script src="/admin_assets/assets/libs/apexcharts/apexcharts.min.js"></script>
<script>
    $(document).ready(function () {
        $('.menu-item-main.has-sub').each(function () {
            if ($(this).find('.menu-item-sub').length === 0) {
                $(this).hide();
            }
        });

        $("#ibRequestForm").submit(function (e) {
            e.preventDefault();
            var formData = $("#ibRequestForm").serializeArray();
            // console.log(formData);
            formData.push({
                name: 'action',
                value: 'requestIB'
            });
            $.ajax({
                url: "/admin/ajax",
                type: "POST",
                data: formData,
                responseType: 'json',
                success: function (data) {
                    // console.log('test');
                    // data = JSON.parse(data.trim());
                    if (data.status == true) {
                        swal.fire({
                            icon: "success",
                            title: "IB Request Successfully Updated",
                        }).then((val) => {
                            location.reload();
                        });
                    } else {
                        swal.fire({
                            icon: "error",
                            title: "Something went wrong.",
                            text: "Please try again or contact support."
                        }).then((val) => {
                            location.reload();
                        });
                    }
                }
            });
        });
    });
</script>

<!-- Server Time Display - Real-time update with seconds (runs continuously) -->
<script>
(function() {
    'use strict';

    // Prevent multiple initializations
    if (window.serverTimeInitialized) {
        return;
    }
    window.serverTimeInitialized = true;

    let timeInterval = null;
    let retryCount = 0;
    const maxRetries = 20; // Maximum retries to find element

    // Wait for DOM to be ready
    function initServerTime() {
        const displayElement = document.getElementById('server-time-display');

        if (!displayElement) {
            retryCount++;
            if (retryCount < maxRetries) {
                // Retry after a short delay if element not found
                setTimeout(initServerTime, 100);
            } else {
                console.warn('Server time display element not found after multiple retries');
            }
            return;
        }

        // Clear any existing interval
        if (timeInterval) {
            clearInterval(timeInterval);
        }

        // Get initial server time string (formatted in server timezone: Asia/Kuwait)
        const initialServerTime = '{{ now()->format("H:i:s") }}';
        const timeParts = initialServerTime.split(':');

        if (timeParts.length !== 3) {
            console.error('Invalid server time format:', initialServerTime);
            return;
        }

        const initialHours = parseInt(timeParts[0], 10);
        const initialMinutes = parseInt(timeParts[1], 10);
        const initialSeconds = parseInt(timeParts[2], 10);

        // Validate parsed values
        if (isNaN(initialHours) || isNaN(initialMinutes) || isNaN(initialSeconds)) {
            console.error('Failed to parse server time:', initialServerTime);
            return;
        }

        // Calculate total seconds since midnight for initial time
        let totalSeconds = initialHours * 3600 + initialMinutes * 60 + initialSeconds;
        const startTime = Date.now();

        function updateServerTime() {
            try {
                const currentElement = document.getElementById('server-time-display');
                if (!currentElement) {
                    return; // Element removed, stop updating
                }

                // Calculate elapsed milliseconds since page load
                const elapsedMs = Date.now() - startTime;
                const elapsedSeconds = Math.floor(elapsedMs / 1000);

                // Add elapsed seconds to initial time
                let currentTotalSeconds = (totalSeconds + elapsedSeconds) % 86400; // 86400 seconds in a day

                // Handle negative (shouldn't happen, but safety check)
                if (currentTotalSeconds < 0) {
                    currentTotalSeconds += 86400;
                }

                // Convert back to hours, minutes, seconds
                const hours = Math.floor(currentTotalSeconds / 3600);
                const minutes = Math.floor((currentTotalSeconds % 3600) / 60);
                const seconds = currentTotalSeconds % 60;

                // Format as HH:mm:ss
                const timeString =
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');

                // Update the display element
                currentElement.textContent = timeString;
            } catch (error) {
                console.error('Error updating server time:', error);
            }

            // Update immediately
            updateServerTime();

            // Update every second (1000 milliseconds) - runs continuously without page refresh
            timeInterval = setInterval(updateServerTime, 1000);
        }

        // Update immediately
        updateServerTime();

        // Update every second (1000 milliseconds) - runs continuously without page refresh
        timeInterval = setInterval(updateServerTime, 1000);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initServerTime);
    } else {
        // DOM is already ready, initialize immediately
        initServerTime();
    }

    // Also try initialization after a short delay as fallback
    setTimeout(function() {
        if (!timeInterval) {
            initServerTime();
        }

        // Also try initialization after a short delay as fallback
        setTimeout(function () {
            if (!timeInterval) {
                initServerTime();
            }
        }, 500);
    });
});
</script>
@yield('scripts')
@include('sweetalert::alert')

@include('components.google-translate')
