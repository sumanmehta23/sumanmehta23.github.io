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
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    </script>
@endif
@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Something went wrong',
            text: '{{ $errors->first() }}',
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
    $(document).ready(function() {
        $('.menu-item-main.has-sub').each(function() {
            if ($(this).find('.menu-item-sub').length === 0) {
                $(this).hide();
            }
        });

    $("#ibRequestForm").submit(function(e) {
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
            success: function(data) {
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

   <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2659568854245574');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=2659568854245574&ev=PageView&noscript=1"
        />
    </noscript>
    <!-- End Meta Pixel Code -->
@yield('scripts')
@include('sweetalert::alert')
