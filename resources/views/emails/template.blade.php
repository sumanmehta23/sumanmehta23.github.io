<?php
$settings=settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i" rel="stylesheet">
    <style type="text/css">
        .rollover:hover .rollover-first {
            max-height: 0px !important;
            display: none !important;
        }

        .rollover:hover .rollover-second {
            max-height: none !important;
            display: block !important;
        }

        .rollover span {
            font-size: 0px;
        }

        u+.body img~div div {
            display: none;
        }

        #outlook a {
            padding: 0;
        }

        span.MsoHyperlink,
        span.MsoHyperlinkFollowed {
            color: inherit;
            mso-style-priority: 99;
        }

        a.es-button {
            mso-style-priority: 100 !important;
            text-decoration: none !important;
        }

        a[x-apple-data-detectors],
        #MessageViewBody a {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        .es-desk-hidden {
            display: none;
            float: left;
            overflow: hidden;
            width: 0;
            max-height: 0;
            line-height: 0;
            mso-hide: all;
        }

        @media only screen and (max-width:600px) {
            .es-p-default {}

            *[class="gmail-fix"] {
                display: none !important;
            }

            p,
            a {
                line-height: 150% !important;
            }

            h1,
            h1 a,
            h2,
            h2 a,
            h3,
            h3 a,
            h4,
            h4 a,
            h5,
            h5 a,
            h6,
            h6 a {
                line-height: 120% !important;
            }

            .es-header-body h1 a,
            .es-content-body h1 a,
            .es-footer-body h1 a {
                font-size: 30px !important;
            }

            .es-header-body h2 a,
            .es-content-body h2 a,
            .es-footer-body h2 a {
                font-size: 24px !important;
            }

            .es-header-body h3 a,
            .es-content-body h3 a,
            .es-footer-body h3 a {
                font-size: 20px !important;
            }

            h1 {
                font-size: 30px !important;
                text-align: left;
            }

            h2 {
                font-size: 24px !important;
                text-align: left;
            }

            h3 {
                font-size: 20px !important;
                text-align: left;
            }

            h4 {
                font-size: 24px !important;
                text-align: left;
            }

            h5 {
                font-size: 20px !important;
                text-align: left;
            }

            h6 {
                font-size: 16px !important;
                text-align: left;
            }

            .es-menu td a {
                font-size: 14px !important;
            }

            .es-header-body p,
            .es-content-body p,
            .es-footer-body p,
            .es-infoblock p {
                font-size: 14px !important;
            }

            .es-infoblock a {
                font-size: 12px !important;
            }

            .es-m-txt-c,
            .es-m-txt-c h1,
            .es-m-txt-c h2,
            .es-m-txt-c h3,
            .es-m-txt-c h4,
            .es-m-txt-c h5,
            .es-m-txt-c h6 {
                text-align: center !important;
            }

            .es-m-txt-r,
            .es-m-txt-r h1,
            .es-m-txt-r h2,
            .es-m-txt-r h3,
            .es-m-txt-r h4,
            .es-m-txt-r h5,
            .es-m-txt-r h6 {
                text-align: right !important;
            }

            .es-m-txt-j,
            .es-m-txt-j h1,
            .es-m-txt-j h2,
            .es-m-txt-j h3,
            .es-m-txt-j h4,
            .es-m-txt-j h5,
            .es-m-txt-j h6 {
                text-align: justify !important;
            }

            .es-m-txt-l,
            .es-m-txt-l h1,
            .es-m-txt-l h2,
            .es-m-txt-l h3,
            .es-m-txt-l h4,
            .es-m-txt-l h5,
            .es-m-txt-l h6 {
                text-align: left !important;
            }

            .es-m-txt-r img,
            .es-m-txt-c img,
            .es-m-txt-l img {
                display: inline !important;
            }

            .es-m-fw,
            .es-m-fw.es-fw,
            .es-m-fw .es-button {
                display: block !important;
            }

            .es-m-il,
            .es-m-il .es-button,
            .es-social,
            .es-social td,
            .es-menu {
                display: inline-block !important;
            }

            .es-adaptive table,
            .es-left,
            .es-right {
                width: 100% !important;
            }

            .es-content table,
            .es-header table,
            .es-footer table,
            .es-content,
            .es-footer,
            .es-header {
                width: 100% !important;
                max-width: 600px !important;
            }

            .adapt-img {
                width: 100% !important;
                height: auto !important;
            }

            .es-mobile-hidden,
            .es-hidden {
                display: none !important;
            }

            .es-desk-hidden {
                width: auto !important;
                overflow: visible !important;
                float: none !important;
                max-height: inherit !important;
                line-height: inherit !important;
            }

            tr.es-desk-hidden {
                display: table-row !important;
            }

            table.es-desk-hidden {
                display: table !important;
            }

            td.es-desk-menu-hidden {
                display: table-cell !important;
            }

            .es-menu td {
                width: 1% !important;
            }

            table.es-table-not-adapt,
            .esd-block-html table {
                width: auto !important;
            }

            .h-auto {
                height: auto !important;
            }
        }

        @media screen and (max-width:384px) {
            .mail-message-content {
                width: 414px !important;
            }

            .verify-email {
                width: 140px !important;
            }
        }

        @media only screen and (max-width: 600px) {

            a.es-button,
            button.es-button {
                font-size: 30px !important;
            }
        }
    </style>
</head>

<body>
    <table role="presentation" width="100%" bgcolor="#f4f4f4" border="0" cellpadding="0" cellspacing="0"
        class="m_-2550223905729420962pc-project-body"
        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;min-width:600px;background-color:#f4f4f4;table-layout:fixed">
        <tr>
            <td valign="top" align="center" style="padding:0;Margin:0">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="600" align="center"
                    class="m_-2550223905729420962pc-project-container"
                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;max-width:600px;width:600px">
                    <tr>
                        <td align="left" valign="top" style="padding:0px;Margin:0">
                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%">
                                <tr>
                                    <td valign="top" style="padding:0;Margin:0">
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                            width="100%"
                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                            <tr>
                                                <td bgcolor="#ffffff" valign="top"
                                                    class="m_-2550223905729420962pc-w520-padding-30-30-30-30 m_-2550223905729420962pc-w620-padding-35-35-35-35"
                                                    style="padding:40px;Margin:0;border-radius:0px;background-color:#ffffff">
                                                    <table role="presentation" width="100%" border="0"
                                                        cellpadding="0" cellspacing="0"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                        <tr>
                                                            <td style="padding:0px 0px 70px 0px;Margin:0">
                                                                <table role="presentation" width="100%" border="0"
                                                                    cellpadding="0" cellspacing="0"
                                                                    class="m_-2550223905729420962pc-width-fill m_-2550223905729420962pc-w620-gridCollapsed-0"
                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                    <tr
                                                                        class="m_-2550223905729420962pc-grid-tr-first m_-2550223905729420962pc-grid-tr-last">
                                                                        <td align="left" valign="middle"
                                                                            class="m_-2550223905729420962pc-grid-td-first m_-2550223905729420962pc-w620-padding-20-0"
                                                                            style="Margin:0;padding-top:0px;padding-right:10px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="0"
                                                                                cellspacing="0" role="presentation"
                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                <tr>
                                                                                    <td align="left" valign="middle"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            role="presentation"
                                                                                            align="left"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                            <tr>
                                                                                                <td align="left"
                                                                                                    valign="top"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        width="100%"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                        <tr>
                                                                                                            <td align="left"
                                                                                                                valign="top"
                                                                                                                style="padding:0;Margin:0">
                                                                                                                <img height="52"
                                                                                                                    src="{{ $settings['copyright_site_name_text'] . '/' . $settings['admin_sidebar_logo'] }}"
                                                                                                                    width="125"
                                                                                                                    alt=""
                                                                                                                    data-bit="iit"
                                                                                                                    class="CToWUd"
                                                                                                                    style="display:block; font-size:14px; border:0; outline:0; text-decoration:none; line-height:14px; width:125px; height:auto;">
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                        <td align="left" valign="middle"
                                                                            class="m_-2550223905729420962pc-grid-td-last m_-2550223905729420962pc-w620-padding-20-0"
                                                                            style="Margin:0;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:10px">
                                                                            <table width="100%" border="0"
                                                                                cellpadding="0" cellspacing="0"
                                                                                role="presentation"
                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:100%">
                                                                                <tr>
                                                                                    <td valign="middle" align="right"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table role="presentation"
                                                                                            width="100%"
                                                                                            align="right"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%">
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    align="right"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        align="right"
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                        <tr>
                                                                                                            <td align="right"
                                                                                                                style="padding:0;Margin:0">
                                                                                                                <table
                                                                                                                    cellspacing="0"
                                                                                                                    role="presentation"
                                                                                                                    align="right"
                                                                                                                    border="0"
                                                                                                                    cellpadding="0"
                                                                                                                    class="m_-2550223905729420962pc-w620-gridCollapsed-0"
                                                                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                                    <tr
                                                                                                                        class="m_-2550223905729420962pc-grid-tr-first m_-2550223905729420962pc-grid-tr-last">
                                                                                                                        <td valign="middle"
                                                                                                                            class="m_-2550223905729420962pc-grid-td-first m_-2550223905729420962pc-grid-td-last m_-2550223905729420962pc-w620-padding-0-10"
                                                                                                                            style="Margin:0;padding-bottom:0px;padding-left:0px;padding-top:0px;padding-right:0px">
                                                                                                                            <table
                                                                                                                                cellspacing="0"
                                                                                                                                role="presentation"
                                                                                                                                border="0"
                                                                                                                                cellpadding="0"
                                                                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                                                                <tr>
                                                                                                                                    <td align="right"
                                                                                                                                        valign="middle"
                                                                                                                                        style="padding:0;Margin:0">
                                                                                                                                        <table
                                                                                                                                            align="right"
                                                                                                                                            border="0"
                                                                                                                                            cellpadding="0"
                                                                                                                                            cellspacing="0"
                                                                                                                                            role="presentation"
                                                                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                                                            <tr>
                                                                                                                                                <td align="right"
                                                                                                                                                    valign="top"
                                                                                                                                                    style="padding:0;Margin:0">
                                                                                                                                                    <table
                                                                                                                                                        cellpadding="0"
                                                                                                                                                        cellspacing="0"
                                                                                                                                                        role="presentation"
                                                                                                                                                        align="right"
                                                                                                                                                        border="0"
                                                                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                                                                                        <tr>
                                                                                                                                                            <td align="right"
                                                                                                                                                                valign="top"
                                                                                                                                                                style="padding:0;Margin:0">
                                                                                                                                                                <div
                                                                                                                                                                    style="color:#000000;text-align:right;text-align-last:right;line-height:16.9px;font-family:Cairo, Arial, Helvetica, sans-serif;font-size:14px;font-weight:500;font-variant-ligatures:normal">
                                                                                                                                                                    <div>
                                                                                                                                                                        <span>Your
                                                                                                                                                                            Partner
                                                                                                                                                                            in
                                                                                                                                                                            Profitable
                                                                                                                                                                            Trading</span>
                                                                                                                                                                    </div>
                                                                                                                                                                </div>
                                                                                                                                                            </td>
                                                                                                                                                        </tr>
                                                                                                                                                    </table>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </table>
                                                                                                                                    </td>
                                                                                                                                </tr>
                                                                                                                            </table>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table cellspacing="0" role="presentation" width="100%"
                                                        border="0" cellpadding="0"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                        <tr>
                                                            <td class="m_-2550223905729420962pc-w620-valign-middle m_-2550223905729420962pc-w620-halign-center"
                                                                style="padding:0;Margin:0">
                                                                <table cellspacing="0" role="presentation"
                                                                    width="100%" border="0" cellpadding="0"
                                                                    class="m_-2550223905729420962pc-width-fill m_-2550223905729420962pc-w620-gridCollapsed-0 m_-2550223905729420962pc-w620-halign-center"
                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                    <tr
                                                                        class="m_-2550223905729420962pc-grid-tr-first m_-2550223905729420962pc-grid-tr-last">
                                                                        <td align="left" valign="middle"
                                                                            class="m_-2550223905729420962pc-grid-td-first"
                                                                            style="Margin:0;padding-left:0px;padding-top:0px;padding-right:0px;padding-bottom:0px">
                                                                            <table cellpadding="0" cellspacing="0"
                                                                                role="presentation" width="247"
                                                                                border="0"
                                                                                class="m_-2550223905729420962pc-w620-halign-center"
                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:247px">
                                                                                <tr>
                                                                                    <td align="left" valign="bottom"
                                                                                        class="m_-2550223905729420962pc-w620-halign-center m_-2550223905729420962pc-w620-valign-middle"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table role="presentation"
                                                                                            width="247"
                                                                                            align="left"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            class="m_-2550223905729420962pc-w620-halign-center"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:247px">
                                                                                            <tr>
                                                                                                <td align="left"
                                                                                                    valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        width="100%"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                        <tr>
                                                                                                            <td align="left"
                                                                                                                valign="top"
                                                                                                                class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                                style="padding:0px 0px 10px 0px;Margin:0">
                                                                                                                <img data-bit="iit"
                                                                                                                    height="212"
                                                                                                                    src="https://ci3.googleusercontent.com/meips/ADKq_NYBLtdD-pqYVOLZYRQk2b8uKdqUhHmB299ScLdGuWlkzHQIBUFAv0aI8IN1CYc39Tems453AntaNWgfykqhDN-uYebzmGq1tdD1Z7vIadbU=s0-d-e1-ft#https://cloudfilesdm.com/postcards/image-1713706891422.png"
                                                                                                                    tabindex="0"
                                                                                                                    width="215"
                                                                                                                    alt=""
                                                                                                                    class="m_-2550223905729420962pc-w620-align-center CToWUd a6T"
                                                                                                                    style="display:block;font-size:14px;border:0;outline:0;text-decoration:none;line-height:14px;width:215px;height:auto">
                                                                                                                <div dir="ltr"
                                                                                                                    class="a6S"
                                                                                                                    style="left:347px;top:359.583px;opacity:0.01">
                                                                                                                </div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td align="left"
                                                                                                    valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                    style="padding:0;Margin:0">
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    align="left"
                                                                                                    class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        align="left"
                                                                                                        class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                        <tr>
                                                                                                            <td align="left"
                                                                                                                class="m_-2550223905729420962pc-w620-valign-middle m_-2550223905729420962pc-w620-halign-center"
                                                                                                                style="padding:0;Margin:0">
                                                                                                                <table
                                                                                                                    role="presentation"
                                                                                                                    align="left"
                                                                                                                    border="0"
                                                                                                                    cellpadding="0"
                                                                                                                    cellspacing="0"
                                                                                                                    class="m_-2550223905729420962pc-w620-gridCollapsed-0 m_-2550223905729420962pc-w620-halign-center"
                                                                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                                    <tr
                                                                                                                        class="m_-2550223905729420962pc-grid-tr-first m_-2550223905729420962pc-grid-tr-last">
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                        <td align="left" valign="middle"
                                                                            class="m_-2550223905729420962pc-grid-td-last"
                                                                            style="Margin:0;padding-bottom:0px;padding-left:0px;padding-top:0px;padding-right:0px">
                                                                            <table cellpadding="0" cellspacing="0"
                                                                                role="presentation" width="100%"
                                                                                border="0"
                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:100%">
                                                                                <tr>
                                                                                    <td align="right" height="223"
                                                                                        valign="middle"
                                                                                        class="m_-2550223905729420962pc-w620-halign-center m_-2550223905729420962pc-w620-valign-middle m_-2550223905729420962pc-w620-height-223"
                                                                                        style="padding:0;Margin:0;height:223px">
                                                                                        <table cellspacing="0"
                                                                                            role="presentation"
                                                                                            width="100%"
                                                                                            align="right"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            class="m_-2550223905729420962pc-w620-halign-center"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%">
                                                                                            <tr>
                                                                                                <td align="right"
                                                                                                    valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        align="right"
                                                                                                        border="0"
                                                                                                        class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:232px">
                                                                                                        <tr>
                                                                                                            <td valign="top"
                                                                                                                style="padding:0px 0px 10px 0px;Margin:0">
                                                                                                                <table
                                                                                                                    cellspacing="0"
                                                                                                                    role="presentation"
                                                                                                                    border="0"
                                                                                                                    cellpadding="0"
                                                                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:232px">
                                                                                                                    <tr>
                                                                                                                        <td align="right"
                                                                                                                            valign="top"
                                                                                                                            style="padding:0; margin:0;">
                                                                                                                            <div
                                                                                                                                style="font-family:Cairo, Arial, Helvetica, sans-serif; font-size:24px; color:#000000; text-align-last:right; font-variant-ligatures:normal; font-weight:bold; line-height:42.6px; letter-spacing:-0.4px; text-align:right;">
                                                                                                                                <div>
                                                                                                                                    <span
                                                                                                                                        style="font-size:21px; font-weight:500; font-style:normal;">{{ $title_right}}</span>
                                                                                                                                </div>
                                                                                                                                <div>
                                                                                                                                    <span
                                                                                                                                        style="font-size:30px;">{{ $subtitle_right }}</span>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td align="right"
                                                                                                    valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <table
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        align="right"
                                                                                                        border="0"
                                                                                                        class="m_-2550223905729420962pc-w620-halign-center"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                                        <tr>
                                                                                                            <td valign="top"
                                                                                                                class="m_-2550223905729420962pc-w620-spacing-0-0-20-0"
                                                                                                                style="padding:0px 0px 40px 0px;Margin:0">
                                                                                                                <table
                                                                                                                    cellspacing="0"
                                                                                                                    role="presentation"
                                                                                                                    border="0"
                                                                                                                    cellpadding="0"
                                                                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                                                    <tr>
                                                                                                                        <td valign="top"
                                                                                                                            align="right"
                                                                                                                            style="padding:0;Margin:0">
                                                                                                                            <div class="m_-2550223905729420962pc-w620-fontSize-16 m_-2550223905729420962pc-w620-lineHeight-26"
                                                                                                                                style="text-align:right;font-family:Cairo, Arial, Helvetica, sans-serif;text-align-last:right;line-height:28.1px;letter-spacing:-0.2px;font-weight:300;font-variant-ligatures:normal;color:#000000;font-size:18px">

                                                                                                                            </div>
                                                                                                                        </td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="padding:0; margin:0;">
                            <table cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                border="0"
                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0;">
                                <tr>
                                    <td class="m_-2550223905729420962pc-w620-spacing-0-0-0-0"
                                        style="padding:0; margin:0;">
                                        <table cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                            border="0"
                                            style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0;">
                                            <tr>
                                                <td bgcolor="#ffffff" valign="top"
                                                    class="m_-2550223905729420962pc-w620-padding-30-40-0-40"
                                                    style="padding:0px 60px 40px 60px; margin:0; border-radius:0; background-color:#ffffff;">
                                                    <table width="100%" border="0" cellpadding="0"
                                                        cellspacing="0" role="presentation"
                                                        style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0;">
                                                        <tr>
                                                            <td valign="top" align="left"
                                                                style="padding:0 0 10px 0; margin:0;">
                                                                <table border="0" cellpadding="0" cellspacing="0"
                                                                    role="presentation"
                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                    <tr>
                                                                        <td align="left" valign="top"
                                                                            style="padding:0; margin:0;">
                                                                            <div
                                                                                style="line-height:21.8px; color:#000000; letter-spacing:-0.2px; text-align:left; font-family:Cairo, Arial, Helvetica, sans-serif; font-weight:normal; text-align-last:left; font-size:14px; font-variant-ligatures:normal;">
                                                                                <div>
                                                                                    <span
                                                                                        style="font-weight:700; font-style:normal;">Dear
                                                                                        {{ $name }},</span>
                                                                                </div>

                                                                                @if (!isset($content))
                                                                                    <div>
                                                                                        <span>Your {{ $acc_type }}
                                                                                            MT5 account is ready! You're all set to dive into the exciting world of trading.</span>
                                                                                    </div>
                                                                                    <div><span></span></div>
                                                                                    <div>
                                                                                        <span>Here are your MT5 account details</span>
                                                                                    </div>
                                                                                @else
                                                                                    <div
                                                                                        style="color:#000 !important;">
                                                                                        {!! $content !!}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if (!isset($content))
                        <tr>
                            <td valign="top" style="padding:0;Margin:0">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                    role="presentation"
                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                    <tr>
                                        <td class="m_-2550223905729420962pc-w620-spacing-0-0-0-0"
                                            style="padding:0px;Margin:0">
                                            <table border="0" cellpadding="0" cellspacing="0"
                                                role="presentation" width="100%"
                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                <tr>
                                                    <td bgcolor="#ffffff" valign="top"
                                                        class="m_-2550223905729420962pc-w620-padding-20-30-30-30"
                                                        style="padding:0px 40px 40px 40px;Margin:0;border-radius:0px;background-color:#ffffff">
                                                        <table cellpadding="0" cellspacing="0" role="presentation"
                                                            width="100%" border="0"
                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                            <tr>
                                                                <td style="padding:0;Margin:0">
                                                                    <table border="0" cellpadding="0"
                                                                        cellspacing="0" role="presentation"
                                                                        width="100%"
                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:100%">
                                                                        <tr>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px 20px 0px 0px; Margin:0; width:245px; border-bottom:1px solid #e5e5e5;">
                                                                                <table cellpadding="0" cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    border="0"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <div
                                                                                                style="letter-spacing:-0.2px; line-height:26px; color:#000000; text-align:left; font-variant-ligatures:normal; font-family:Cairo, Arial, Helvetica, sans-serif; font-size:16px; text-align-last:left; font-weight:normal;">
                                                                                                <div>
                                                                                                    <span>ACCOUNT ID</span>
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 10px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                                <table role="presentation"
                                                                                    width="100%" border="0"
                                                                                    cellpadding="0" cellspacing="0"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td valign="top"
                                                                                            align="left"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <table cellspacing="0"
                                                                                                role="presentation"
                                                                                                border="0"
                                                                                                cellpadding="0"
                                                                                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0; width:22px;">
                                                                                                <tr>
                                                                                                    <td valign="top"
                                                                                                        align="left"
                                                                                                        style="padding:0; Margin:0;">
                                                                                                        <div
                                                                                                            style="color:#000000; text-align-last:left; line-height:26px; text-align:left; font-family:Cairo, Arial, Helvetica, sans-serif; font-weight:bold;font-size:18px; font-variant-ligatures:normal;">
                                                                                                            <div>
                                                                                                                <span>{{ $code }}</span>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px 20px 0px 0px; Margin:0; width:245px; border-bottom:1px solid #e5e5e5;">
                                                                                <table border="0" cellpadding="0"
                                                                                    cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <div style="font-variant-ligatures:normal; font-size:16px; text-align-last:left; letter-spacing:-0.2px; font-weight:normal; line-height:26px; color:#000000; text-align:left; font-family:Cairo, Arial, Helvetica, sans-serif;"><div><span>MASTER PASSWORD</span></div></div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 10px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                                <table cellpadding="0" cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    border="0"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <table border="0"
                                                                                                cellpadding="0"
                                                                                                cellspacing="0"
                                                                                                role="presentation"
                                                                                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0; width:214px;">
                                                                                                <tr>
                                                                                                    <td valign="top"
                                                                                                        align="left"
                                                                                                        style="padding:0; Margin:0;">
                                                                                                        <div
                                                                                                            style="text-align-last:left; line-height:26px; font-family:Cairo, Arial, Helvetica, sans-serif; font-size:18px; font-variant-ligatures:normal; font-weight:bold; text-align:left; color:#000000;"><div><span>{{ $trader_password }}</span></div></div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 0px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px 20px 0px 0px; Margin:0; width:245px; border-bottom:1px solid #e5e5e5;">
                                                                                <table border="0" cellpadding="0"
                                                                                    cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <div style="line-height:26px; font-weight:normal; text-align:left; font-family:Cairo, Arial, Helvetica, sans-serif; color:#000000; letter-spacing:-0.2px; font-variant-ligatures:normal; font-size:16px; text-align-last:left;"><div><span>INVESTOR
                                                                                                        PASSWORD</span></div></div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 10px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                                <table cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    border="0" cellpadding="0"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <table cellpadding="0"
                                                                                                cellspacing="0"
                                                                                                role="presentation"
                                                                                                border="0"
                                                                                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0; width:214px">
                                                                                                <tr>
                                                                                                    <td align="left"
                                                                                                        valign="top"
                                                                                                        style="padding:0; Margin:0;">
                                                                                                        <div
                                                                                                            style="font-size:18px; font-variant-ligatures:normal; text-align:left;line-height:26px; color:#000000; text-align-last:left; font-family:Cairo, Arial, Helvetica, sans-serif; font-weight:bold;"><div><span>{{ $investor_password }}</span></div></div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 0px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px 20px 0px 0px; Margin:0; border-bottom:1px solid #e5e5e5; width:245px;">
                                                                                <table border="0" cellpadding="0"
                                                                                    cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                                    <tr>
                                                                                        <td valign="top"
                                                                                            align="left"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <div style="font-weight:normal; text-align:left; font-variant-ligatures:normal; font-family:Cairo, Arial, Helvetica, sans-serif; color:#000000; text-align-last:left; letter-spacing:-0.2px; font-size:16px; line-height:26px"><div><span>LEVERAGE</span></div></div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 10px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                                <table width="100%" border="0"
                                                                                    cellpadding="0" cellspacing="0"
                                                                                    role="presentation"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <table cellpadding="0"
                                                                                                cellspacing="0"
                                                                                                role="presentation"
                                                                                                border="0"
                                                                                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0; width:22px;">
                                                                                                <tr>
                                                                                                    <td align="left"
                                                                                                        valign="top"
                                                                                                        style="padding:0; Margin:0;">
                                                                                                        <div style="font-size:18px; line-height:26px; color:#000000;text-align-last:left; text-align:left; font-family:Cairo, Arial, Helvetica, sans-serif; font-weight:bold; font-variant-ligatures:normal;"><div><span>{{ $leverage }}</span></div></div></td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="top"
                                                                                style="padding:20px 0px 20px 0px; Margin:0; border-bottom:1px solid #e5e5e5;">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px 20px 0px 0px; Margin:0; width:245px;">
                                                                                <table width="100%" border="0"
                                                                                    cellpadding="0" cellspacing="0"
                                                                                    role="presentation"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <div style="text-align:left; font-variant-ligatures:normal; font-family:Cairo, Arial, Helvetica, sans-serif; text-align-last:left; line-height:26px; font-weight:normal; letter-spacing:-0.2px; font-size:16px; color:#000000;"><div><span>MT5
                                                                                                        SERVER</span></div></div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td align="left" valign="middle"
                                                                                style="padding:0px; Margin:0;">
                                                                                <table cellspacing="0"
                                                                                    role="presentation" width="100%"
                                                                                    border="0" cellpadding="0"
                                                                                    style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:collapse; border-spacing:0px;">
                                                                                    <tr>
                                                                                        <td align="left"
                                                                                            valign="top"
                                                                                            style="padding:0; Margin:0;">
                                                                                            <table border="0"
                                                                                                cellpadding="0"
                                                                                                cellspacing="0"
                                                                                                role="presentation"
                                                                                                style="mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse:separate; border-spacing:0; width:214px;">
                                                                                                <tr>
                                                                                                    <td align="left"
                                                                                                        valign="top"
                                                                                                        style="padding:0; Margin:0;">
                                                                                                        <div style="font-weight:bold; text-align-last:left; line-height:26px; font-family:Cairo, Arial, Helvetica, sans-serif; font-size:18px; color:#000000; font-variant-ligatures:normal; text-align:left;"><div><span>{{ $server_name }}</span></div></div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </table>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>
                                                                            <td valign="top" align="left"
                                                                                style="padding:40px 0px 20px 0px; Margin:0;">
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td valign="top" style="padding:0;Margin:0">
                            <table cellpadding="0" cellspacing="0" role="presentation" width="100%"
                                border="0"
                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                <tr>
                                    <td style="padding:0px;Margin:0">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                            role="presentation"
                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                            <tr>
                                                <td bgcolor="#ffffff" valign="top"
                                                    style="padding:0px 60px 40px 60px;Margin:0;border-radius:0px;background-color:#ffffff">
                                                    <table cellpadding="0" cellspacing="0" role="presentation"
                                                        width="100%" border="0"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                        <tr>
                                                            <td align="left" valign="top"
                                                                style="padding:0px 0px 10px 0px;Margin:0">
                                                                <div style="padding:0px;background-color:transparent">
                                                                    <div
                                                                        style="min-width:320px;max-width:600px;word-wrap:break-word;word-break:break-word;background-color:#000000;margin:0 auto">
                                                                        <div
                                                                            style="height:100%;background-color:transparent;border-collapse:collapse;display:table;width:100%">
                                                                            <div
                                                                                style="vertical-align:top;max-width:320px;min-width:223.02px;display:table-cell">
                                                                                <div
                                                                                    style="height:100%;width:100% !important;border-radius:0px">
                                                                                    <div
                                                                                        style="border-top:0px solid transparent;border-left:0px solid transparent;border-right:0px solid transparent;border-bottom:0px solid transparent;border-radius:0px;box-sizing:border-box;height:100%;padding:0px">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if (!isset($content))
                                                        <tr>
                                                            <td align="left" valign="top"
                                                                style="padding:0;Margin:0">
                                                                <div
                                                                    style="font-variant-ligatures:normal;text-align:left;text-align-last:left;font-family:Cairo, Arial, Helvetica, sans-serif;color:#000000;line-height:21.8px;font-size:14px;letter-spacing:0.2px;font-weight:normal">
                                                                    <div>
                                                                        <span style="letter-spacing:0.2px">You're now ready to begin your trading journey.</span>
                                                                    </div>
                                                                    <div>
                                                                        <span style="letter-spacing:0.2px">Let the trading journey begin! If you have any questions, feel free to reach out to us for assistance.</span>
                                                                    </div>
                                                                    <div>
                                                                        <div><a href="https://download.metatrader.com/cdn/mobile/mt5/ios?server=LQHIntegrated-Server">Download MetaTrader iOS </a><br>
                                                                        <a href="https://download.metatrader.com/cdn/mobile/mt5/android?server=LQHIntegrated-Server">Download MetaTrader Android</a><br>
                                                                        <a href="https://download.metatrader.com/cdn/web/lqh.integrated.ltd/mt5/lqhintegrated5setup.exe">Download Desktop </a></div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endif

                                                        @if (isset($site_link))
                                                            <tr>
                                                                <td align="center" style="padding:0;Margin:0">
                                                                    <span class="es-button-border"
                                                                        style="border-style:solid;border-color:#2CB543;background:{{ $settings['sidebar_color'] }};border-width:0;display:inline-block;border-radius:0;width:auto">
                                                                        <a href="{{ $site_link }}" target="_blank"
                                                                            class="es-button"
                                                                            style="mso-style-priority:100 !important;text-decoration:none !important;mso-line-height-rule:exactly;padding:10px 20px 10px 20px;display:inline-block;background:{{  $settings['sidebar_color']  }};border-radius:0;font-size:20px;font-family:'playfair display', georgia, 'times new roman', serif;font-weight:bold;font-style:normal;line-height:43.2px;color:#fff;width:auto;text-align:center;letter-spacing:0;mso-padding-alt:0;mso-border-alt:10px solid {{  $settings['sidebar_color']  }}">{{ $btn_text ?? 'Verify' }}</a>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        @if (isset($after_btn_text))
                                                            <tr>
                                                                <td align="left" style="padding:0;Margin:0">
                                                                    <div style="line-height:21.8px;color:#000000;letter-spacing:-0.2px;text-align:left;font-weight:normal;text-align-last:left;font-size:14px;font-variant-ligatures:normal"><?php echo $after_btn_text; ?></div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                            width="100%"
                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;font-family:Montserrat, sans-serif">
                                            <tr>
                                                <td align="left"
                                                    style="padding:10px;Margin:0;font-family:Montserrat, sans-serif;word-break:break-word">
                                                    <table cellspacing="0" height="0px" width="100%"
                                                        align="center" border="0" cellpadding="0"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0;table-layout:fixed;vertical-align:top;border-top:0px solid #000000"
                                                        role="none">
                                                        <tr style="vertical-align:top">
                                                            <td
                                                                style="padding:0;Margin:0;word-break:break-word;border-collapse:collapse !important;vertical-align:top;font-size:0px;line-height:0px">
                                                                <span>&nbsp;</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" style="padding:0;Margin:0">
                <table cellpadding="0" cellspacing="0" role="presentation" width="100%" border="0"
                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                        <td style="padding:0px;Margin:0">
                            <table cellspacing="0" role="presentation" width="100%" border="0"
                                cellpadding="0"
                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                <tr>
                                    <td bgcolor="#ffffff" valign="top"
                                        class="m_-2550223905729420962pc-w520-padding-10-25-10-25 m_-2550223905729420962pc-w620-padding-10-30-10-30"
                                        style="padding:10px 40px 10px 40px;Margin:0;border-radius:0px;background-color:#ffffff">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                            role="presentation"
                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%">
                                            <tr>
                                                <td valign="top" style="padding:0;Margin:0">
                                                    <table cellspacing="0" role="presentation" width="100%"
                                                        border="0" cellpadding="0"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                        <tr>
                                                            <td height="1" valign="top"
                                                                style="padding:0;Margin:0;border-bottom:2px solid #d9d9d9;line-height:1px;font-size:1px">
                                                                &nbsp;
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" style="padding:0;Margin:0">
                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                        <td style="padding:0px;Margin:0">
                            <table cellspacing="0" role="presentation" width="100%" border="0"
                                cellpadding="0"
                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                <tr>
                                    <td bgcolor="#ffffff" valign="top"
                                        class="m_-2550223905729420962pc-w520-padding-30-30-30-30 m_-2550223905729420962pc-w620-padding-35-35-35-35"
                                        style="padding:40px 40px 0px 40px;Margin:0;background-color:#ffffff;border-radius:0px">
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation"
                                            width="100%"
                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                            <tr>
                                                <td style="padding:0;Margin:0">
                                                    <table cellpadding="0" cellspacing="0" role="presentation"
                                                        width="100%" border="0"
                                                        class="m_-2550223905729420962pc-width-fill m_-2550223905729420962pc-w620-gridCollapsed-1"
                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                        <tr
                                                            class="m_-2550223905729420962pc-grid-tr-first m_-2550223905729420962pc-grid-tr-last">
                                                            <td valign="top" align="left"
                                                                class="m_-2550223905729420962pc-grid-td-first m_-2550223905729420962pc-w620-padding-40-0"
                                                                style="Margin:0;padding-bottom:0px;padding-left:0px;width:100%;padding-top:0px;padding-right:20px">
                                                                <table role="presentation" width="100%"
                                                                    border="0" cellpadding="0" cellspacing="0"
                                                                    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0;width:100%">
                                                                    <tr>
                                                                        <td align="left" height="250"
                                                                            valign="top"
                                                                            style="padding:0;Margin:0;height:250px">
                                                                            <table cellspacing="0" role="presentation"
                                                                                width="100%" align="left"
                                                                                border="0" cellpadding="0"
                                                                                style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;width:100%">
                                                                                <tr>
                                                                                    <td align="left" valign="top"
                                                                                        style="padding:0;Margin:0">
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" valign="top"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table align="left"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            role="presentation"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-spacing-0-0-22-0"
                                                                                                    style="padding:0px 0px 15px 0px;Margin:0">
                                                                                                    <table
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                                        <tr>
                                                                                                            <td align="left"
                                                                                                                valign="top"
                                                                                                                style="padding:0;Margin:0">
                                                                                                                <div
                                                                                                                    style="color:#000;text-align-last:left;font-family:Cairo, Arial, Helvetica, sans-serif;font-weight:normal;line-height:17.2px;letter-spacing:-0.2px;font-variant-ligatures:normal;font-size:12px;text-align:left">
                                                                                                                    <div>
                                                                                                                        <span
                                                                                                                            style="font-weight:700;font-style:normal">Need
                                                                                                                            Help?</span>
                                                                                                                    </div>
                                                                                                                    <div>
                                                                                                                        <span>Our customer support is here to help you 24/5. If you have any questions or need assistance with your account, please don’t hesitate to contact our Support Team.</span>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" valign="top"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            role="presentation"
                                                                                            align="left"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    style="padding:0px 0px 8px 0px;Margin:0">
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td valign="top" align="left"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table role="presentation"
                                                                                            align="left"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    style="padding:0;Margin:0">
                                                                                                    <div
                                                                                                        style="font-size:16px;font-weight:600;font-variant-ligatures:normal;color:#1595e7;line-height:27.4px;font-family:Cairo, Arial, Helvetica, sans-serif">
                                                                                                        <div>
                                                                                                            <span>
                                                                                                                <a href="mailto:{{ $email }}"
                                                                                                                    target="_blank"
                                                                                                                    style="mso-line-height-rule:exactly;text-decoration:underline">{{ $email }}</a>
                                                                                                            </span>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td align="left" valign="top"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            role="presentation"
                                                                                            align="left"
                                                                                            border="0"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                            <tr>
                                                                                                <td align="left"
                                                                                                    style="padding:15px 0px 0px 0px;Margin:0">
                                                                                                    <table
                                                                                                        align="left"
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        class="m_-2550223905729420962pc-w620-gridCollapsed-0"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">

                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" valign="top"
                                                                                        style="padding:0;Margin:0">
                                                                                        <table align="left"
                                                                                            border="0"
                                                                                            cellpadding="0"
                                                                                            cellspacing="0"
                                                                                            role="presentation"
                                                                                            style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                                                            <tr>
                                                                                                <td valign="top"
                                                                                                    class="m_-2550223905729420962pc-w620-spacing-0-0-22-0"
                                                                                                    style="padding:0px 0px 15px 0px;Margin:0">
                                                                                                    <table
                                                                                                        cellspacing="0"
                                                                                                        role="presentation"
                                                                                                        border="0"
                                                                                                        cellpadding="0"
                                                                                                        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0">
                                                                                                        <tr>
                                                                                                            <td align="left"
                                                                                                                valign="top"
                                                                                                                style="padding:0;Margin:0">
                                                                                                                <div
                                                                                                                    style="color:#000;text-align-last:left;font-family:Cairo, Arial, Helvetica, sans-serif;font-weight:normal;line-height:17.2px;letter-spacing:-0.2px;font-variant-ligatures:normal;font-size:12px;text-align:left">
                                                                                                                    <div>
                                                                                                                        <span>All official communication from LQH Markets (LQH Integrated Ltd.) will be conducted solely through our official email addresses, using the <a href="https://www.lqhmarkets.com/">LQHMarkets.com</a> domain.</span>
                                                                                                                        <p>Our mailing address is as follows:</p>
                                                                                                                                LQH Integrated Ltd<br>
                                                                                                                                A2-704A, Al Hamra Industrial Zone-FZ<br>
                                                                                                                                RAKEZ Business Centre<br>
                                                                                                                                Ras Al Khaimah, UAE<br>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
    </td>
    </tr>
    </table>
</body>

</html>
