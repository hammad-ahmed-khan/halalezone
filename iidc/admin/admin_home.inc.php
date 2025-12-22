<?php
if ($_SESSION['user_type'] == "admin") {
    include "../checkuser.inc.php";
    include "../config/paths.inc.php";
    include "../config/mysql_ftp.inc.php";
    include "../config/connect.inc.php";
?>
    <style>
        div#dashboard-menu,
        div#localDashboard {
            position: fixed;
            top: 125px;
            left: 50%;
            transform: translate(-50%, 0);
        }

        ul#dashboardMenu {
            list-style: none;
            display: inline-grid;
            grid-template-columns: repeat(2, 1fr);
            /* 4 items per row */
            gap: 5px;
            padding: 0;
        }

        ul#dashboardMenu li {
            display: flex;
            text-align: center;
            padding: 20px;
            /* height: 180px; */
            position: relative;
            z-index: 1;
            overflow: hidden;
            background: #f0f5e5 !important;
        }

        ul#dashboardMenu li i,
        ul#dashboardMenu li span.material-symbols-outlined {
            font-size: 60px !important;
            margin: 10px !important;
            color: #0b9444 !important;
        }

        ul#dashboardMenu li a:hover {
            color: #b00 !important;
        }

        ul#dashboardMenu li div.dashboard-menu-icon {
            float: left;
            width: 200px;
            text-align: center !important;
            text-decoration: none
        }

        ul#dashboardMenu li div.dashboard-menu-icon i {
            cursor: default
        }

        ul#dashboardMenu li h5 {
            text-transform: uppercase;
            white-space: nowrap;
            text-align: center !important
        }

        ul#dashboardMenu li div {
            font-size: 12px !important;
            text-align: left !important;
            overflow: hidden;
        }

        ul#dashboardMenu li div a,
        ul#dashboardMenu li div strong,
        ul#dashboardMenu li a {
            text-decoration: none;
            display: block;
            white-space: nowrap;
            padding: 2px 0;
            line-height: 14px;
            text-transform: capitalize;
            position: relative;
            color: var(--color100)
        }

        ul#dashboardMenu li div strong {
            padding-top: 10px;
            text-transform: uppercase;
            color: #000
        }

        ul#dashboardMenu li div a:before {
            content: "\00BB";
            font-size: 20px;
            margin-right: 5px
        }

        ul#dashboardMenu li div a.button:before {
            content: none
        }

        ul#dashboardMenu li div a:hover>* {
            color: #b00 !important;
        }

        ul#dashboardMenu li h3 {
            text-align: left;
            text-transform: capitalize;
            white-space: nowrap;
            margin-bottom: 10px
        }

        ul#dashboardMenu li h4 {
            text-transform: uppercase;
            white-space: nowrap;
            font-size: 1em;
            font-weight: bold;
            margin: 10px 0px 0px 0px;
            color: #0b9444 !important;
        }

        ul#dashboardMenu li.no-sub-menu {
            width: 24.55%;
            min-width: 200px
        }

        ul#dashboardMenu li.no-sub-menu i {
            float: none !important
        }

        ul#dashboardMenu li.no-sub-menu h3 {
            text-align: center
        }

        div#dashboard-menu .data-holder {
            padding: 10px 0
        }

        div#dashboard-menu .data-holder>* {
            max-width: 99%
        }

        #dashboardMenu li::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1
        }
    </style>
    <script>
        $("#page_title").html("Home");
    </script>
    <div id="pageInclude" style="text-align:center">
        <ul id="dashboardMenu" class="dashboardMenu">
            <li class="email_messages  dashboardMenuItem0" style="z-index: 1;" id="1727385863">
                <div><span class="material-symbols-outlined">group </span></div>
                <div class="submenuDiv">
                    <h4>Clients</h4>
                    <a href="/admin/?inc=clients">Clients list</a>
                    <a href="/company/index.php?inc=register">Add new Client</a>
                    <a href="/company/products/index.php?inc=products_home">Clients products</a>
                </div>
            </li>
            <li class="admin  dashboardMenuItem1" style="z-index: 1;" id="1738179888">
                <div><i class="fas fa-file-contract"></i></div>
                <div class="submenuDiv">
                    <h4>Issued Certificates</h4>
                    <a href="/certificates/annual/?inc=certificates">Annual certificates</a>
                    <a href="/admin/?inc=certificates&tp=a&offid=0">Slaughtering Certificates</a>
                    <h4>Issue Certificate</h4>
                    <a href="/certificates/annual/?inc=certificate_add_edit&offid=0">Annual certificate</a>
                    <a href="/certificates/?inc=certificate_ab&tp=a&offid=0">Slaughtering Certificate - Austria</a>
                    <a href="/certificates/?inc=certificate_ab&tp=a&offid=1">Slaughtering Certificate - Hungary</a>
                </div>

            </li>
            <li class="admin  dashboardMenuItem2" style="z-index: 1;" id="1738179889">
                <div><i class="fas fa-file-invoice"></i></div>
                <div class="submenuDiv">
                    <h4>Invoices</h4>
                    <a href="/invoices/?show=all">All Invoices</a>
                    <a href="/invoices/?show=paid">Paid Invoices</a>
                    <a href="/invoices/?show=unpaid">Unpaid Invoices</a>
                    <a href="/invoices/?show=overdue">Overdue Invoices</a>
                </div>
            </li>
            <li class="admin  dashboardMenuItem3" style="z-index: 1;" id="1738179890">
                <div><span class="material-symbols-outlined">notifications_active</span></div>
                <div class="submenuDiv">
                    <h4>New submissions/requests</h4>
                    <a href="/admin/?inc=new-clients">New clients</a>
                    <a href="/certificates/annual/?inc=certificates&status=new">Annual certificate</a>
                    <a href="/certificates/?inc=certificates&tp=a&offid=0&status=new">Slaughtering Certificate - IIDC Austria</a>
                    <a href="/certificates/?inc=certificates&tp=a&offid=1&status=new">Slaughtering Certificate - IIDC Hungary</a>
                </div>
            </li>
        </ul>
    </div><?php }; ?>