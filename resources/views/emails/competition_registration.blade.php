<?php
$settings = settings();
?>
<!DOCTYPE html>
<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml">

<head>
    <title></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: inherit !important;
        }

        #MessageViewBody a {
            color: inherit;
            text-decoration: none;
        }

        p {
            line-height: inherit;
        }

        .desktop_hide,
        .desktop_hide table {
            mso-hide: all;
            display: none;
            max-height: 0px;
            overflow: hidden;
        }

        .image_block img+div {
            display: none;
        }

        sup, sub {
            font-size: 75%;
            line-height: 0;
        }

        @media (max-width:620px) {
            .desktop_hide table.icons-inner {
                display: inline-block !important;
            }

            .icons-inner {
                text-align: center;
            }

            .icons-inner td {
                margin: 0 auto;
            }

            .image_block div.fullWidth {
                max-width: 100% !important;
            }

            .mobile_hide {
                display: none;
            }

            .row-content {
                width: 100% !important;
            }

            .stack .column {
                width: 100%;
                display: block;
            }

            .mobile_hide {
                min-height: 0;
                max-height: 0;
                max-width: 0;
                overflow: hidden;
                font-size: 0px;
            }

            .desktop_hide,
            .desktop_hide table {
                display: table !important;
                max-height: none !important;
            }

            .row-4 .column-2 .block-1.heading_block td.pad {
                padding: 30px 10px 20px !important;
            }

            .row-4 .column-2 .block-1.heading_block h1 {
                font-size: 23px !important;
            }

            .row-5 .column-1 .block-1.paragraph_block td.pad > div,
            .row-6 .column-1 .block-1.paragraph_block td.pad > div,
            .row-6 .column-1 .block-2.paragraph_block td.pad > div {
                font-size: 13px !important;
            }

            .row-5 .column-1 .block-1.paragraph_block td.pad,
            .row-6 .column-1 .block-2.paragraph_block td.pad {
                padding: 0 25px 25px !important;
            }

            .row-5 .column-1 .block-2.button_block span {
                font-size: 10px !important;
                line-height: 20px !important;
            }

            .row-6 .column-1 .block-1.paragraph_block td.pad {
                padding: 0 25px 15px !important;
            }
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#ffffff;">
    <div style="max-width:650px; margin:0 auto; background-color:#ffffff;">
        <table border="0" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" style="width:100%; background-color:#ffffff;">
            <tbody>
                <tr>
                    <td>
                        <!-- All existing template content remains the same -->
                        {!! $content !!}
                        <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td style="padding: 20px 25px; text-align: center;">
                                    <hr style="border: none; border-top: 1px solid rgb(200, 200, 200); margin: 20px 0; max-width: 100%;">
                                    <p style="font-family: Arial, sans-serif; font-size: 13px; color: #555;">
                                        If you need any assistance, contact us at
                                        <a href="mailto:support@lqhmarkets.com" style="color: #00b98e;">support@lqhmarkets.com</a><br>
                                        LQH Integrated LTD, A2-704A, Al Hamra Industrial Zone-FZ<br>
                                        RAKEZ Business Centre, Ras Al Khaimah, UAE
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
