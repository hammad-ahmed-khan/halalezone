<?php
if ($_SESSION['user_type'] == "admin") {
    include "../checkuser.inc.php";
    include "../config/paths.inc.php";
    include "../config/mysql_ftp.inc.php";
    include "../config/connect.inc.php";
?>
  <style>

/* Page background for preview */
body {
    background: #f8fafc;
    min-height: 100vh;
    margin: 0;
}

/* Modern SaaS Dashboard - Light Theme */
/* Works with existing HTML structure */

/* Reset and Base */
div#dashboard-menu,
div#localDashboard {
    position: relative !important;
    top: auto !important;
    left: auto !important;
    transform: none !important;
    max-width: 900px;
    margin: 40px auto;
    padding: 0 24px;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
}

#pageInclude {
    text-align: left !important;
}

/* Dashboard Grid */
ul#dashboardMenu {
    list-style: none;
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    gap: 24px !important;
    padding: 0 !important;
    margin: 0;
}

/* Card Styles */
ul#dashboardMenu li {
    display: flex !important;
    flex-direction: column !important;
    text-align: left !important;
    padding: 0 !important;
    height: auto !important;
    position: relative;
    z-index: 1;
    overflow: visible !important;
    background: #ffffff !important;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

ul#dashboardMenu li:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
    border-color: #d1d5db;
}

ul#dashboardMenu li::before {
    content: "" !important;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    border-radius: 16px 16px 0 0;
    z-index: 2;
    opacity: 0;
    transition: opacity 0.3s ease;
}

ul#dashboardMenu li:hover::before {
    opacity: 1;
}

/* Card accent colors */
ul#dashboardMenu li.dashboardMenuItem1::before {
    background: linear-gradient(90deg, #10b981, #34d399);
}

ul#dashboardMenu li.dashboardMenuItem2::before {
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
}

ul#dashboardMenu li.dashboardMenuItem3::before {
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

/* Icon Container */
ul#dashboardMenu li > div:first-child {
    padding: 24px 24px 0 24px;
}

ul#dashboardMenu li div.dashboard-menu-icon {
    float: none !important;
    width: auto !important;
    text-align: left !important;
    padding: 24px 24px 0 24px;
}

/* Icons */
ul#dashboardMenu li i,
ul#dashboardMenu li span.material-symbols-outlined {
    font-size: 28px !important;
    margin: 0 !important;
    width: 56px;
    height: 56px;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    transition: transform 0.3s ease;
}

ul#dashboardMenu li:hover i,
ul#dashboardMenu li:hover span.material-symbols-outlined {
    transform: scale(1.05);
}

/* Icon backgrounds per card */
ul#dashboardMenu li.dashboardMenuItem1 i,
ul#dashboardMenu li.dashboardMenuItem1 span.material-symbols-outlined {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    color: #059669 !important;
}

ul#dashboardMenu li.dashboardMenuItem2 i,
ul#dashboardMenu li.dashboardMenuItem2 span.material-symbols-outlined {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #2563eb !important;
}

ul#dashboardMenu li.dashboardMenuItem3 i,
ul#dashboardMenu li.dashboardMenuItem3 span.material-symbols-outlined {
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    color: #d97706 !important;
}

/* Submenu Container */
ul#dashboardMenu li div.submenuDiv {
    padding: 20px 24px 24px 24px;
    font-size: 14px !important;
    text-align: left !important;
}

/* Section Headers */
ul#dashboardMenu li h4 {
    text-transform: uppercase !important;
    white-space: nowrap;
    font-size: 16px !important;
    font-weight: 600 !important;
    letter-spacing: 0.06em;
    margin: 20px 0 12px 0 !important;
    padding: 0 !important;
    color: #9ca3af !important;
}

ul#dashboardMenu li h4:first-child {
    margin-top: 0 !important;
}

/* Remove old h3/h5 styling conflicts */
ul#dashboardMenu li h3,
ul#dashboardMenu li h5 {
    display: none;
}

/* Links */
ul#dashboardMenu li div a,
ul#dashboardMenu li a {
    text-decoration: none !important;
    display: flex !important;
    align-items: center;
    gap: 12px;
    white-space: nowrap;
    padding: 10px 14px !important;
    margin: 0 -14px;
    line-height: 1.4 !important;
    text-transform: none !important;
    position: relative;
    color: #4b5563 !important;
    font-size: 14px;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.2s ease;
}

ul#dashboardMenu li div a:hover,
ul#dashboardMenu li a:hover {
    background: #f9fafb;
    color: #111827 !important;
}

/* Link bullet points */
ul#dashboardMenu li div a:before {
    content: "" !important;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #d1d5db;
    flex-shrink: 0;
    margin-right: 0 !important;
    font-size: inherit !important;
    transition: all 0.2s ease;
}

ul#dashboardMenu li div a:hover:before {
    transform: scale(1.3);
}

/* Colored bullets on hover per card */
ul#dashboardMenu li.dashboardMenuItem1 div a:hover:before {
    background: #10b981;
}

ul#dashboardMenu li.dashboardMenuItem2 div a:hover:before {
    background: #3b82f6;
}

ul#dashboardMenu li.dashboardMenuItem3 div a:hover:before {
    background: #f59e0b;
}

/* Button links if any */
ul#dashboardMenu li div a.button:before {
    content: none !important;
    display: none;
}

/* Strong text */
ul#dashboardMenu li div strong {
    display: block;
    padding: 16px 0 8px 0 !important;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.06em;
    color: #9ca3af !important;
}

/* No sub menu cards */
ul#dashboardMenu li.no-sub-menu {
    width: auto !important;
    min-width: auto !important;
}

ul#dashboardMenu li.no-sub-menu i {
    float: none !important;
}

ul#dashboardMenu li.no-sub-menu h3 {
    text-align: left;
}

/* Data holder */
div#dashboard-menu .data-holder {
    padding: 16px 0;
}

div#dashboard-menu .data-holder > * {
    max-width: 100%;
}

/* Animation */
@keyframes fadeSlideIn {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

ul#dashboardMenu li {
    animation: fadeSlideIn 0.4s ease backwards;
}

ul#dashboardMenu li:nth-child(1) { animation-delay: 0.05s; }
ul#dashboardMenu li:nth-child(2) { animation-delay: 0.1s; }
ul#dashboardMenu li:nth-child(3) { animation-delay: 0.15s; }
ul#dashboardMenu li:nth-child(4) { animation-delay: 0.2s; }
ul#dashboardMenu li:nth-child(5) { animation-delay: 0.25s; }
ul#dashboardMenu li:nth-child(6) { animation-delay: 0.3s; }

/* Responsive */
@media (max-width: 768px) {
    div#dashboard-menu,
    div#localDashboard {
        padding: 0 16px;
        margin: 24px auto;
    }

    ul#dashboardMenu {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }

    ul#dashboardMenu li div.submenuDiv {
        padding: 16px 20px 20px 20px;
    }

    ul#dashboardMenu li > div:first-child {
        padding: 20px 20px 0 20px;
    }
}
    </style>
    <script>
        $("#page_title").html("Home");
    </script>
    <div id="pageInclude" style="text-align:center">
        <ul id="dashboardMenu" class="dashboardMenu">
 
            <li class="admin  dashboardMenuItem1" style="z-index: 1;" id="1738179888">
                <div><i class="fas fa-file-contract"></i></div>
                <div class="submenuDiv">
                    <h4>Issued Certificates</h4>
                    <a href="/iidc/certificates/annual/?inc=certificates">Annual certificates</a>
                    <a href="/iidc/admin/?inc=certificates&tp=a&offid=0">Slaughtering Certificates</a>
                    <h4>Issue Certificate</h4>
                    <a href="/iidc/certificates/annual/?inc=certificate_add_edit&offid=0">Annual certificate</a>
                    <a href="/iidc/certificates/?inc=certificate_ab&tp=a&offid=0">Slaughtering Certificate - Austria</a>
                    <a href="/iidc/certificates/?inc=certificate_ab&tp=a&offid=1">Slaughtering Certificate - Hungary</a>
                </div>

            </li>
            <li class="admin  dashboardMenuItem2" style="z-index: 1;" id="1738179889">
                <div><i class="fas fa-file-invoice"></i></div>
                <div class="submenuDiv">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Invoices</h4>
                            <a href="/iidc/invoices/?show=all">All Invoices</a>
                            <a href="/iidc/invoices/?show=paid">Paid Invoices</a>
                            <a href="/iidc/invoices/?show=unpaid">Unpaid Invoices</a>
                            <a href="/iidc/invoices/?show=overdue">Overdue Invoices</a>
                        </div>
                        <div class="col-md-6">
                            <h4>Create Invoices</h4>
                            <a href="/iidc/invoices/?show=draft">Draft invoices</a>
                            <a href="/iidc/invoices/index.php?inc=create_cohs_invoice">Shipment certificates invoice</a>
                            <a href="/iidc/invoices/index.php?inc=create_hqc_invoice">Annual certificates invoice</a>
                            <a href="/iidc/invoices/index.php?inc=create_general_invoice">General invoice</a>
                            <a href="/iidc/invoices/index.php?inc=create_credit_note">Create credit note</a>
                        </div>
                    </div>
                </div>
            </li>
            <li class="admin  dashboardMenuItem3" style="z-index: 1;" id="1738179890">
                <div><span class="material-symbols-outlined">notifications_active</span></div>
                <div class="submenuDiv">
                    <h4>New submissions/requests</h4>
                     <a href="/iidc/certificates/annual/?inc=certificate_add_edit&offid=0">Annual certificate</a>
                    <a href="/iidc/certificates/?inc=certificate_ab&tp=a&offid=0">Slaughtering Certificate - IIDC Austria</a>
                    <a href="/iidc/certificates/?inc=certificate_ab&tp=a&offid=1">Slaughtering Certificate - IIDC Hungary</a>
                </div>
            </li>
        </ul>
    </div><?php }; ?>