<?php
if (!defined("_HQC_")) {
	exit();
};
?>
<style>
	/* ============================================
   Annual Certificate Page - Complete Styling
   ============================================ */

/* Office Selection for Clients */
.office-select-container {
    max-width: 600px;
    margin: 0 auto;
}

.office-select-card {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 16px;
    border: 1px solid #bbf7d0;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-align: center;
}

.office-select-card .card-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 28px;
    margin: 0 auto 20px;
}

.office-select-card h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.office-select-card p {
    margin: 0 0 24px 0;
    font-size: 14px;
    color: #64748b;
}

.office-select-card select {
    width: 100%;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.office-select-card select:hover {
    border-color: #1a5f4a;
}

.office-select-card select:focus {
    outline: none;
    border-color: #1a5f4a;
    box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.12);
}

/* Version Badge */
.version-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #f1f5f9;
    color: #64748b;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    margin-bottom: 20px;
}

/* Form Container */
.annual-cert-form-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.annual-cert-form-container table#certTbl {
    width: 100%;
    margin: 0;
    border: none !important;
    border-collapse: collapse;
}

.annual-cert-form-container table#certTbl tr {
    border-bottom: 1px solid #f1f5f9;
}

.annual-cert-form-container table#certTbl tr:last-child {
    border-bottom: none;
}

.annual-cert-form-container table#certTbl th {
    padding: 16px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    background: #fafafa;
    text-align: left;
    vertical-align: top;
    width: 180px;
    border-right: 1px solid #f1f5f9;
}

.annual-cert-form-container table#certTbl td {
    padding: 10px 10px;
    background: #ffffff;
    vertical-align: top;
}

/* Sub Title Rows */
.annual-cert-form-container table#certTbl td.sub_title,
.annual-cert-form-container table#certTbl th.sub_title {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
    color: #166534;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    padding: 16px 20px;
    border-right: none;
}

/* Form Inputs */
.annual-cert-form-container input[type="text"],
.annual-cert-form-container input[type="number"],
.annual-cert-form-container select:not(.searchable),
.annual-cert-form-container textarea {
    padding: 0px 14px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
    font-family: inherit;
}
.annual-cert-form-container input[type="text"],
.annual-cert-form-container input[type="number"],
.annual-cert-form-container textarea {
    padding: 10px 14px;
     
 
}

.annual-cert-form-container input[type="text"]:focus,
.annual-cert-form-container input[type="number"]:focus,
.annual-cert-form-container select:focus,
.annual-cert-form-container textarea:focus {
    outline: none;
    border-color: #1a5f4a;
    box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
}

.annual-cert-form-container textarea {
    min-height: 80px;
    resize: vertical;
}

/* Company Selection */
.company-select-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.company-select-row select {
    flex: 1;
    min-width: 300px;
    max-width: 450px;
}

.btn-new-cert {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-new-cert:hover {
    background: linear-gradient(135deg, #155043 0%, #1a5f4a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
}

/* Company Info Display */
.company-info-display {
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    line-height: 1.6;
}

.company-info-display b {
    color: #1e293b;
}

.company-info-display .deleted-warning {
    display: inline-block;
    margin-top: 12px;
    padding: 8px 16px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    border-radius: 6px;
    font-weight: 600;
}

/* Manufacturing Sites */
#manufacturingSites {
    padding: 0;
    margin: 0;
    list-style: none;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

#manufacturingSites li {
    padding: 0px !important;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s ease;
    position: relative;
}

#manufacturingSites li:last-child {
    border-bottom: none;
}

#manufacturingSites li:hover {
    background: #f0fdf4;
}

#manufacturingSites li label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    padding-right: 40px;
}

#manufacturingSites li input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #1a5f4a;
}

#manufacturingSites li .siteName {
    font-weight: 600;
    color: #1e293b;
}

#manufacturingSites li .siteAddress {
    color: #64748b;
    font-size: 13px;
}

#manufacturingSites li .fa-clone {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #0369a1;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

#manufacturingSites li .fa-clone:hover {
    background: #e0f2fe;
}

.site-options-box {
    margin-top: 16px;
    padding: 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
}

.site-options-box label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #92400e;
    cursor: pointer;
    margin-bottom: 8px;
}

.site-options-box label:last-child {
    margin-bottom: 0;
}

/* Products List */
#productsOl {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none;
    max-height: 280px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

#productsOl li {
    padding: 5px !important;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s ease;
    position: relative;
    margin: 0 !important;
    list-style: none !important;
}

#productsOl li:last-child {
    border-bottom: none;
}

#productsOl li:hover {
    background: #f0fdf4;
}

#productsOl li label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    padding-right: 30px;
    line-height: 1.4;
    margin: 0pc;
    padding: 5px;
} 

#productsOl li input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #1a5f4a;
    flex-shrink: 0;
    margin-top: 0px;
}

#productsOl li.double {
    background: #fef2f2;
}

#productsOl li .prohibited {
    color: #dc2626 !important;
    font-size: 12px !important;
    margin-right: 4px;
}

.products-toolbar {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.products-toolbar select {
    padding: 8px 32px 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}

.products-toolbar a {
    color: #1a5f4a;
    font-size: 13px;
    text-decoration: none;
}

.products-toolbar a:hover {
    text-decoration: underline;
}

.products-count {
    font-size: 13px;
    color: #64748b;
}

.products-count span {
    font-weight: 600;
    color: #1a5f4a;
}

.products-warning {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    padding: 10px 14px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    font-size: 13px;
    color: #dc2626;
}

.products-warning i {
    font-size: 14px !important;
}

/* Prohibited Confirm */
#prohibitedConfirm {
    margin-top: 12px;
    padding: 12px 16px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 13px;
}

#prohibitedConfirm span {
    color: #dc2626;
    font-weight: 500;
}

#prohibitedConfirm label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: 12px;
    cursor: pointer;
}

/* Reference Standards List */
#halalStandards {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none;
    max-height: 220px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

#halalStandards li {
    padding: 0px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s ease;
    margin: 0pc;
    padding: 5px;

}

#halalStandards li:last-child {
    border-bottom: none;
}

#halalStandards li:hover {
    background: #f0fdf4;
}

#halalStandards li label {
	padding:0px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    line-height: 1.4;
}

#halalStandards li input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #1a5f4a;
    flex-shrink: 0;
    margin-top: 2px;
}

.list-hint {
    display: block;
    padding: 8px 16px;
    font-size: 12px;
    color: #64748b;
    font-style: italic;
}

.list-hint.warning {
    color: #dc2626;
}

/* Categories List */
.categoriesUl {
    padding: 0 !important;
    margin: 0 !important;
    list-style: none;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.categoriesUl li {
    padding: 0px;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.categoriesUl li:last-child {
    border-bottom: none;
}

.categoriesUl li.main-category {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.categoriesUl li.main-category:hover {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.categoriesUl li.sub-category {
    padding-left: 40px;
    background: #ffffff;
}

.categoriesUl li.sub-category:hover {
    background: #f0fdf4;
}

.categoriesUl li label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    padding-right: 50px;
}

.categoriesUl li input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #1a5f4a;
    flex-shrink: 0;
    margin-top: 2px;
}

.categoriesUl li b {
    font-weight: 600;
    color: #1e293b;
}

.categoriesUl li .fa-angle-double-down {
    position: absolute;
    right: 16px;
    top: 45%;
    transform: translateY(-50%);
    color: #64748b;
    cursor: pointer;
    padding: 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 14px !important;
}

.categoriesUl li .fa-angle-double-down:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.categoriesUl li .fa-question-circle {
    position: absolute;
    right: 44px;
    top: 50%;
    transform: translateY(-50%);
    color: #0369a1;
    cursor: pointer;
    font-size: 14px !important;
}

.categoriesUl li .fa-question-circle:hover {
    color: #0284c7;
}

/* Scope Textarea */
#scope_of_certification {
    width: 100% !important;
    min-height: 100px;
}

.char-counter {
    margin-top: 8px;
    font-size: 12px;
    color: #64748b;
}

.char-counter span {
    font-weight: 600;
    color: #1a5f4a;
}

/* Dates Section */
.dates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.date-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.date-field label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.date-field input {
    padding: 10px 14px;
}

.validity-field {
    display: flex;
    align-items: center;
    gap: 8px;
}

.validity-field input[type="number"] {
    width: 70px;
}

.validity-hint {
    font-size: 12px;
    color: #64748b;
    font-style: italic;
}

.validity-hint.warning {
    color: #dc2626;
}

#surveillance {
    margin-top: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

#surveillance input {
    margin: 0 8px;
}

/* Signatory Section */
#approval {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

#approval select {
    max-width: 400px;
}

#approval info {
    font-size: 13px;
    color: #64748b;
}

#approval .infoBox {
    padding: 10px 14px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    font-size: 12px;
    color: #0369a1;
}

/* Font Sizes Section */
#font_sizes {
    padding: 16px !important;
    margin: 12px 0 !important;
    background: #f8fafc;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px;
    list-style: none;
}

#font_sizes li {
    padding: 0px !important;
    margin: 0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    background: none !important;
}

#font_sizes li:last-child {
    border-bottom: none !important;
}

#font_sizes li label {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: #374151;
}

#font_sizes li input[type="number"] {
    width: 60px;
    padding: 6px 10px;
    font-size: 13px;
}

/* Product Columns Section */
#productsColumns {
    margin-top: 12px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

#sortableTitles {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 0 !important;
    margin: 16px 0 !important;
    list-style: none;
}

#sortableTitles li {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 0px !important;
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    margin: 0 !important;
    min-width: 160px;
}

#sortableTitles li b {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 10px !important;
    background: #f0fdf4 !important;
    color: #166534;
    border-radius: 6px;
    font-size: 12px !important;
    cursor: move;
}

#sortableTitles li b::after {
    content: "\f0b2";
    font-family: "Font Awesome 5 Free";
    font-size: 12px;
}

#sortableTitles li input[type="text"] {
    padding: 6px 10px !important;
    font-size: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
}

#sortableTitles li input[type="text"]:first-of-type {
    width: 100% !important;
}

#sortableTitles li input[type="text"]:last-of-type {
    width: 50px !important;
}

/* Certificate Options */
#annexOptions ul {
    padding: 12px !important;
    margin: 12px 0 0 0 !important;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    list-style: none;
}

#annexOptions ul li {
    padding: 0;
    border-bottom: 1px solid #e2e8f0;
}

#annexOptions ul li:last-child {
    border-bottom: none;
}

#annexOptions ul li label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #374151;
    cursor: pointer;
}

#annexOptions ul li input[type="checkbox"],
#annexOptions ul li input[type="radio"] {
    width: 16px;
    height: 16px;
    accent-color: #1a5f4a;
}

#annexOptions ul ul {
    margin: 8px 0 0 24px !important;
    padding: 12px !important;
    background: #ffffff;
    border: 1px dashed #e2e8f0;
}

/* Revision Section */
.revision-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.revision-row label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #374151;
}

.revision-row b {
    font-size: 13px;
    color: #374151;
}

.revision-row input[type="text"],
.revision-row input[type="number"] {
    padding: 8px 12px;
    font-size: 13px;
}

#auto_annex_number {
    padding: 6px 12px;
    background: #e0f2fe;
    border-radius: 6px;
    font-size: 12px;
    color: #0369a1;
}

/* Form Footer */
.annual-cert-form-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 24px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-top: 1px solid #bbf7d0;
    flex-wrap: wrap;
}

.btn-cert-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-cert-action.reset {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-cert-action.reset:hover {
    background: #f1f5f9;
    color: #475569;
}

.btn-cert-action.secondary {
    background: #ffffff;
    color: #1a5f4a;
    border: 2px solid #bbf7d0;
}

.btn-cert-action.secondary:hover {
    background: #f0fdf4;
    border-color: #86efac;
}

.btn-cert-action.primary {
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    color: #ffffff;
}

.btn-cert-action.primary:hover {
    background: linear-gradient(135deg, #155043 0%, #1a5f4a 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
}

.btn-cert-action.authorize {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
    color: #ffffff;
}

.btn-cert-action.authorize:hover {
    background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.btn-cert-action.print {
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
    color: #ffffff;
}

.btn-cert-action.print:hover {
    background: linear-gradient(135deg, #075985 0%, #0369a1 100%);
    box-shadow: 0 4px 12px rgba(3, 105, 161, 0.3);
}

.btn-cert-action.digital {
    background: linear-gradient(135deg, #334155 0%, #475569 100%);
    color: #ffffff;
}

.btn-cert-action.digital:hover {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    box-shadow: 0 4px 12px rgba(51, 65, 85, 0.3);
}

.btn-cert-action.save-draft {
    background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
    color: #ffffff;
}

.btn-cert-action.save-draft:hover {
    background: linear-gradient(135deg, #92400e 0%, #b45309 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(180, 83, 9, 0.3);
}

.btn-cert-action.save-draft:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

#DownLoadZip {
    padding: 8px 14px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 13px;
}

#DownLoadZip label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #92400e;
    cursor: pointer;
}

/* Info Messages */
#productsInfo {
    margin-top: 24px;
    padding: 16px 24px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #dc2626;
    text-align: center;
}

/* Collapsible Sections */
.collapsible-header {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-right: 12px;
    margin-bottom: 8px;
}

.collapsible-header:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #1a5f4a;
}

.collapsible-header i {
    font-size: 12px !important;
    transition: transform 0.2s ease;
}

/* ============================================
   VALIDATION ERROR STYLES
   ============================================ */

/* Validation Error Styles */
.validation-error {
    border: 2px solid #dc2626 !important;
    background-color: #fef2f2 !important;
    animation: shake 0.5s ease-in-out;
}

.validation-error-label {
    color: #dc2626 !important;
    font-weight: 600 !important;
    background: #fef2f2 !important;
}

.validation-error-message {
    display: block;
    color: #dc2626;
    font-size: 12px;
    margin-top: 4px;
    padding: 4px 8px;
    background: #fef2f2;
    border-radius: 4px;
    border-left: 3px solid #dc2626;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Error Summary Box */
.validation-summary {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: none;
}

.validation-summary.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.validation-summary-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #dc2626;
    font-weight: 600;
    font-size: 15px;
}

.validation-summary-header i {
    font-size: 18px;
}

.validation-summary-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.validation-summary-list li {
    padding: 0;
    padding-left: 24px;
    position: relative;
    color: #991b1b;
    font-size: 13px;
    cursor: pointer;
    transition: color 0.2s;
}

.validation-summary-list li:hover {
    color: #dc2626;
    text-decoration: underline;
}

.validation-summary-list li::before {
    content: "×";
    position: absolute;
    left: 8px;
    color: #dc2626;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .annual-cert-form-container table#certTbl th {
        display: block;
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .annual-cert-form-container table#certTbl td {
        display: block;
    }
    
    .company-select-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .company-select-row select {
        min-width: 100%;
        max-width: 100%;
    }
    
    .dates-grid {
        grid-template-columns: 1fr;
    }
    
    #sortableTitles {
        flex-direction: column;
    }
    
    #sortableTitles li {
        min-width: 100%;
    }
    
    .annual-cert-form-footer {
        flex-direction: column;
    }
    
    .btn-cert-action {
        width: 100%;
        justify-content: center;
    }
    
    .revision-row {
        flex-direction: column;
        align-items: flex-start;
    }
}

label {
    padding: 5px !important;
}
</style>
<?php
//show php errors
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
if (!isset($_GET['act'])) {
	$_GET['act'] = 'add';
}

$user_type = $_SESSION["user_type"];

// Define crtNr and act variables
$crtNr = isset($_GET['crtNr']) ? $_GET['crtNr'] : '';
$act = isset($_GET['act']) ? $_GET['act'] : 'add';

// Set $dmc when decid parameter is present (DMC certificate request)
if (isset($_GET['decid']) && intval($_GET['decid']) > 0) {
    $dmc = intval($_GET['decid']);
}
?>

<script type="text/javascript">
	$("#page_title").html("Annual Certificate (Request / Update)")
</script>
 <?php
if (!isset($_REQUEST['offid'])) {
    // Get offices based on user type
    if ($_SESSION['user_type'] == 'client') {
        $userOffices = $amdb->get_results("SELECT offid,office_name FROM offices WHERE FIND_IN_SET($_GET[clid],clients) AND status='active'");
    } else {
        $userOffices = $amdb->get_results("SELECT offid,office_name FROM offices WHERE status='active' ORDER BY office_name ASC");
    }
    if ($userOffices) { 
?>
<div class="office-select-container">
    <div class="office-select-card">
        <div class="card-icon">
            <i class="fas fa-building"></i>
        </div>
        <h3>Select Certification Office</h3>
        <p>Choose the office that will process your certificate request</p>
        <select name="office" onchange="if(this.value) document.location='index.php?inc=certificate_add_edit&act=<?php echo isset($_GET['act']) ? $_GET['act'] : 'add'; ?>&clid=<?php echo isset($_GET['clid']) ? $_GET['clid'] : '0'; ?>&offid='+this.value<?php echo isset($_GET['decid']) ? '+ \'&decid=' . intval($_GET['decid']) . '\'' : ''; ?><?php echo isset($_GET['crtNr']) ? '+ \'&crtNr=' . intval($_GET['crtNr']) . '\'' : ''; ?>">
            <option value="">-- Select an Office --</option>
            <?php foreach ($userOffices as $office) { ?>
                <option value="<?php echo $office['offid']; ?>"><?php echo htmlspecialchars($office['office_name']); ?></option>
            <?php } ?>
        </select>
    </div>
</div>
<?php
        return;
    } else {
        $_GET['offid'] = 0;
    }
}
?>
 
<style>
	#approval div {
		margin-bottom: 5px
	}

	#approval span {
		font-weight: bold;
		display: inline-block;
		width: 110px
	}

	#certTbl textarea {
		width: 95%
	}

	ol#productsOl {
		margin-left: 30px
	}

	ul#productsOl li {
		list-style: decimal !important;
		margin-left: 0px;

	}

	ol#sortableTitles {
		margin: 10px 0px;
		padding: 0px;
		overflow: hidden
	}

	ol#sortableTitles li {
		float: left;
		background: #fff !important;
		padding: 5px;
		margin-right: 5px;
		border: 1px solid #bbb;
		border-radius: 5px;
	}

	ol#sortableTitles li input[type='text'] {
		padding: 5px !important
	}

	ol#sortableTitles li input[type='text']:last-child {
		width: 32px !important
	}

	ol#sortableTitles li input[type='text']:first-child {
		width: 100px !important
	}

	li.ui-sortable-handle b {
		background: #eee;
		display: block;
		padding: 2px;
		margin-bottom: 5px;
		width: 160px !important;
		position: relative;
		font-size: 11px !important;
	}

	li.ui-sortable-handle b:after {
		content: "\f0b2";
		font-family: "Font Awesome 5 Free";
		position: absolute;
		right: 5px;
		top: 5px;
		font-size: 14px;
		cursor: move;
	}

	.red {
		color: red
	}

	.fa-question-circle:hover {
		color: red
	}

	ul.categoriesUl li .fa-question-circle,
	ul.categoriesUl li .fa-angle-double-down {
		position: absolute;
		right: 20px;
		top: 45%;
		font-size: 16px !important;
	}

	.fa-angle-double-down {
		right: 5px;
	}

	ul.categoriesUl li {
		position: relative;
	}

	.categoriesUl li.category {
		padding: 2px;
	}

	i.fas.fa-exclamation-triangle {
		position: absolute;
		right: 40px;
		font-size: 14px !important;
	}

	.colorLabel {
		border-radius: 10px;
		display: inline-block;
		width: 40px;
		text-align:center !important;
	}

	.disabled {
		background: transparent;
	}

	.removeProduct {
		position: absolute;
		right: 10px;
		top: 0px
	}

	.removeProduct i,
	.removeProduct i span {
		font-size: 12px !important;
		color: firebrick;
	}
/* Annual Certificate Page Header */
.annual-cert-header {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.annual-cert-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.annual-cert-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.annual-cert-header-info {
    flex: 1;
    min-width: 200px;
}

.annual-cert-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.annual-cert-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
	text-align:left;
}


.annual-cert-header-info p strong {
    color: #1a5f4a;
}

/* Action Badge */
.action-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-badge.issue {
    background: #dcfce7;
    color: #166534;
}

.action-badge.update {
    background: #fef3c7;
    color: #92400e;
}

.action-badge.reissue {
    background: #e0e7ff;
    color: #3730a3;
}

.action-badge i {
    font-size: 11px;
}

/* Header Meta Tags */
.annual-cert-header-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.cert-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.cert-badge.annual {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #166534;
    border: 1px solid #bbf7d0;
}

.cert-badge.annual i {
    color: #1a5f4a;
}

.office-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
}

.office-badge i {
    color: #64748b;
}

/* Company Info Card */
.company-info-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.company-info-strip .company-icon {
    width: 40px;
    height: 40px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 16px;
}

.company-info-strip .company-details {
    flex: 1;
}

.company-info-strip .company-details .company-name {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 2px 0;
}

.company-info-strip .company-details .company-address {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.company-info-strip .company-status {
    padding: 6px 12px;
    background: #dcfce7;
    color: #166534;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.company-info-strip .company-status.deleted {
    background: #fef2f2;
    color: #dc2626;
}

/* Company Selection State */
.company-select-prompt {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.company-select-prompt .prompt-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.company-select-prompt .prompt-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 22px;
}

.company-select-prompt .prompt-text h2 {
    margin: 0 0 4px 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.company-select-prompt .prompt-text p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}

.company-select-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.company-select-wrapper label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.company-select-wrapper .select-with-button {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.company-select-wrapper select {
    flex: 1;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.company-select-wrapper select:hover {
    border-color: #1a5f4a;
    background-color: #fafffe;
}

.company-select-wrapper select:focus {
    outline: none;
    border-color: #1a5f4a;
    box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.12);
}

.btn-new-certificate {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-new-certificate:hover {
    background: linear-gradient(135deg, #155043 0%, #1a5f4a 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
}

.company-select-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    padding: 12px 16px;
    background: #fefce8;
    border: 1px dashed #fbbf24;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
}

.company-select-hint i {
    color: #f59e0b;
}

/* Responsive */
@media (max-width: 768px) {
    .annual-cert-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .annual-cert-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .annual-cert-header-meta {
        justify-content: center;
    }
    
    .company-info-strip {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px;
    }
    
    .company-select-prompt {
        padding: 20px;
    }
    
    .company-select-prompt .prompt-header {
        flex-direction: column;
        text-align: center;
    }
    
    .company-select-wrapper .select-with-button {
        flex-direction: column;
    }
}
	
</style>
<script type="text/javascript">
	$("#page_title").html("Halal Certificate (Request / Update)");
	var prohibited = false;
	var formInteracted = false;

	// Field labels for better error messages
	var fieldLabels = {
		'clid': 'Company',
		'products': 'Products',
		'productsOl': 'Products',
		'reference_standards': 'Reference Halal Standards',
		'halalStandards': 'Reference Halal Standards',
		'scope_of_certification': 'Scope of Certification',
		'category': 'Category',
		'categoriesUl': 'Category',
		'date_of_issue': 'Date of Issue',
		'date_of_expiry': 'Date of Expiry',
		'initial_issue_date': 'Initial Issue Date',
		'signatory': 'Signatory',
		'manufacturingSites': 'Manufacturing Site'
	};

	function checkAnnexSepareted(val) {
		jQuery("#annexSepareted,#DownLoadZip").css("display", val)
		if (val == 'block')
			jQuery("#DownLoadZip").css("display", 'inline-block')
	}
	var offid = <?php echo $_GET['offid']; ?>;

	/**
	 * Clear all validation errors
	 */
	function clearValidationErrors() {
		// Remove error classes
		jQuery('.validation-error').removeClass('validation-error');
		jQuery('.validation-error-label').removeClass('validation-error-label');
		
		// Remove inline error messages
		jQuery('.validation-error-message').remove();
		
		// Hide summary
		jQuery('#validationSummary').removeClass('show');
		jQuery('#validationSummaryList').empty();
	}

	/**
	 * Show validation errors with highlighting and summary
	 */
	function showValidationErrors(errors) {
		var summaryHtml = '';
		var firstErrorElement = null;
		
		errors.forEach(function(error, index) {
			// Find the field element
			var $field = jQuery('#' + error.field);
			
			// If not found by ID, try other selectors
			if ($field.length === 0) {
				$field = jQuery('[name="' + error.field + '"]');
			}
			if ($field.length === 0) {
				$field = jQuery('.' + error.field).first();
			}
			if ($field.length === 0) {
				$field = jQuery('#' + error.field.replace('Ul', '').replace('Ol', ''));
			}
			
			if ($field.length > 0) {
				// Add error class
				$field.addClass('validation-error');
				
				// Find and highlight the label/header
				var $row = $field.closest('tr');
				if ($row.length > 0) {
					$row.find('th').first().addClass('validation-error-label');
				}
				
				// Store first error element for scrolling
				if (index === 0) {
					firstErrorElement = $field;
				}
			}
			
			// Add to summary list
			summaryHtml += '<li onclick="scrollToField(\'' + error.field + '\')">' + error.message + '</li>';
		});
		
		// Show summary box
		jQuery('#validationSummaryList').html(summaryHtml);
		jQuery('#validationSummary').addClass('show');
		
		// Scroll to summary
		if (jQuery('#validationSummary').length > 0) {
			jQuery('html, body').animate({
				scrollTop: jQuery('#validationSummary').offset().top - 100
			}, 500);
		} else if (firstErrorElement) {
			jQuery('html, body').animate({
				scrollTop: firstErrorElement.offset().top - 100
			}, 500);
		}
	}

	/**
	 * Scroll to a specific field
	 */
	function scrollToField(fieldId) {
		var $field = jQuery('#' + fieldId);
		if ($field.length === 0) {
			$field = jQuery('[name="' + fieldId + '"]');
		}
		if ($field.length === 0) {
			$field = jQuery('.' + fieldId).first();
		}
		
		if ($field.length > 0) {
			jQuery('html, body').animate({
				scrollTop: $field.offset().top - 100
			}, 500);
			
			// Focus the field if possible
			if ($field.is('input, select, textarea')) {
				$field.focus();
			}
		}
	}

	async function crtDoAct(act) {
		// === DMC Conducted Check (skip when creating DMC report itself) ===

		formInteracted = true;
		document.addEditForm.crtDo.value = act;
		
		// Clear previous validation errors
		clearValidationErrors();
		
		var errors = [];
		var reqs = ['products', 'clid', 'reference_standards', 'scope_of_certification', 'category', 'date_of_issue', 'date_of_expiry', 'initial_issue_date', 'signatory'];
		
if (act == "preview") {
    jQuery("#future_action_when").removeAttr("data-required");
    document.addEditForm.action = 'certificate.pdf.php';
    document.addEditForm.target = '_blank';
    document.addEditForm.method = 'post';
} else if (act == "digital") {
    jQuery("#future_action_when").removeAttr("data-required");
    document.addEditForm.action = 'certificate.pdf.php';
    document.addEditForm.target = '_blank';
    document.addEditForm.method = 'post';
} else {
    if (jQuery("#send_by_email").is(":checked")) jQuery("#future_action_when").attr("data-required", "yes");
    document.addEditForm.action = 'certificate_save.php';
    document.addEditForm.target = '_self';
    document.addEditForm.method = 'post';
}		
		// Manufacturing site validation
		if (jQuery("#awarded_to_site").is(":checked")) {
			var sitesSelected = jQuery("#manufacturingSites input[type='checkbox']:checked").length;
			if (sitesSelected == 0) {
				errors.push({
					field: 'manufacturingSites',
					message: 'Please select a manufacturing site (required when "Awarded to Manufacturing site" is checked)'
				});
			} else if (sitesSelected > 1) {
				errors.push({
					field: 'manufacturingSites',
					message: 'Please select only one manufacturing site'
				});
			}
		}
		
		checkForm(reqs);
		
		// Validate Company
		var clidVal = jQuery("#clid").val();
		if (!clidVal) {
			clidVal = jQuery("input[name='clid']").val();
		}
		if (!clidVal || clidVal.toString().trim() === '') {
			errors.push({
				field: 'clid',
				message: 'Company is required'
			});
		}
		
		// Validate Products
		if ($(".product:checked").length === 0) {
			errors.push({
				field: 'productsOl',
				message: 'Please select at least one product'
			});
		}
		
		// Validate Reference Standards
		if ($(".reference_standards:checked").length === 0) {
			errors.push({
				field: 'halalStandards',
				message: 'Please select at least one Reference Halal Standard'
			});
		}
		
		// Validate Scope of Certification
		var scopeVal = jQuery("#scope_of_certification").val();
		if (!scopeVal || scopeVal.trim() === '') {
			errors.push({
				field: 'scope_of_certification',
				message: 'Scope of Certification is required'
			});
		}
		
		// Validate Categories
		if ($(".category:checked").length === 0 && $(".main-cat-checkbox:checked").length === 0) {
			errors.push({
				field: 'categoriesUl',
				message: 'Please select at least one category'
			});
		}
		
		// Validate Date of Issue (for admin)
		<?php if ($_SESSION['user_type'] == "admin") { ?>
		var dateIssue = jQuery("#date_of_issue").val();
		if (!dateIssue || dateIssue.trim() === '') {
			errors.push({
				field: 'date_of_issue',
				message: 'Date of Issue is required'
			});
		}
		
		// Validate Date of Expiry
		var dateExpiry = jQuery("#date_of_expiry").val();
		if (!dateExpiry || dateExpiry.trim() === '') {
			errors.push({
				field: 'date_of_expiry',
				message: 'Date of Expiry is required'
			});
		}
		
		// Validate Initial Issue Date
		var initialDate = jQuery("#initial_issue_date").val();
		if (!initialDate || initialDate.trim() === '') {
			errors.push({
				field: 'initial_issue_date',
				message: 'Initial Issue Date is required'
			});
		}
		
		// Validate Signatory
		var signatoryVal = jQuery("#signatory").val();
		if (!signatoryVal || signatoryVal === '' || signatoryVal === 'Select Signatory') {
			errors.push({
				field: 'signatory',
				message: 'Please select a Signatory'
			});
		}
		<?php }; ?>
		
		// Validate prohibited products
		if (prohibited === true && !jQuery("#certificate_option_prohibited").is(":checked")) {
			errors.push({
				field: 'prohibitedConfirm',
				message: 'There is a prohibited product in the selected items. Please confirm to proceed.'
			});
		}

		// Show errors if any
		if (errors.length > 0) {
			showValidationErrors(errors);
			return false;
		}

		if ($(".product:checked").length > 0) {
			var selectedProducts = $('.product:checked').map(function() {
				return this.value;
			}).get().join(",");
			jQuery("#selectedProducts").val(selectedProducts);
		}

		//if (post_this_form("#addEditForm")) {
			document.addEditForm.submit();
		//}
	}

	/**
	 * Save progress without validation - AJAX submit, no redirect
	 */
	async function saveDraft() {
		var $btn = jQuery("#saveDraftBtn");
		var originalHtml = $btn.html();
		
		// Disable button and show saving state
		$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
		
		// Set the action to save (draft)
		document.addEditForm.crtDo.value = 'save';

		// Collect selected products
		if ($(".product:checked").length > 0) {
			var selectedProducts = $('.product:checked').map(function() {
				return this.value;
			}).get().join(",");
			jQuery("#selectedProducts").val(selectedProducts);
		}

		// Build FormData from the form
		var formData = new FormData(document.addEditForm);
		formData.append('save_draft', '1');

		try {
			var response = await fetch('certificate_save.php', {
				method: 'POST',
				body: formData
			});

			if (response.ok) {
				// Show success feedback
				$btn.html('<i class="fas fa-check"></i> Saved!');
				$btn.css('background', 'linear-gradient(135deg, #16a34a 0%, #22c55e 100%)');
				
				setTimeout(function() {
					$btn.html(originalHtml);
					$btn.css('background', '');
					$btn.prop('disabled', false);
				}, 2000);
			} else {
				alert_message('Failed to save progress. Please try again.');
				$btn.html(originalHtml).prop('disabled', false);
			}
		} catch (e) {
			alert_message('Failed to save progress. Please check your connection and try again.');
			$btn.html(originalHtml).prop('disabled', false);
		}
	}

	function openDMCReportForm() {
		var dmcUrl = jQuery("#DMCUrl");
		if (!dmcUrl.length || dmcUrl.data('href') == '') {
			alert_message('DMC Report form URL is not configured. Please ensure a valid DMC decision ID is available.');
			return;
		}

		// Set hidden flag so certificate_save.php knows to redirect to DMC after saving
		if (jQuery("#dmc_redirect").length === 0) {
			jQuery("#addEditForm").append('<input type="hidden" id="dmc_redirect" name="dmc_redirect" value="1" />');
		} else {
			jQuery("#dmc_redirect").val("1");
		}

		// Trigger save action — this validates and submits the form to certificate_save.php
		// certificate_save.php will detect dmc_redirect=1 and redirect to the DMC report URL with the saved crtNr
		crtDoAct('save');
	}

	function checkForm(reqs) {
		jQuery("#addEditForm").find("[data-required='yes']").removeAttr("data-required");
		reqs.forEach(function(item) {
			jQuery("#" + item).attr("data-required", "yes");
		});
	}

	function checkProductsCount() {
		checkedProducts = $(".product:checked").length;
		jQuery("#productsCount").html(checkedProducts);
		prohibited = false;
		if (checkedProducts > 0) {
			//find if there is any prohibited product
			$(".product:checked").each(function() {
				if ($(this).closest("li").find(".prohibited").length > 0) {
					prohibited = true;
					if (jQuery("#prohibitedConfirm").length == 0) {
						jQuery("#productTd").append("<div id='prohibitedConfirm' style='padding:5px;background:beige'><span style='color:red'>Prohibited product name(s) in the selected item(s).</span> Would you like to go ahead and process the certificate?<label><input type='checkbox' name='certificate_options[prohibited]' id='certificate_option_prohibited' value='yes'data-required='yes'/> Yes</label> </div>")
					}
				}
			})
		}

		if (prohibited == false) {
			jQuery("#prohibitedConfirm").remove();
		}
	}

	$(window).load(function(e) {
		dateFormat= "dd/mm/yyyy";
		$("#date_of_issue").datepicker({
			changeMonth: true,
			changeYear: true,
			format:dateFormat,
			dateFormat: dateFormat
		});
		$("#date_of_expiry").datepicker({
			changeMonth: true,
			changeYear: true,
			format:dateFormat,
			dateFormat: dateFormat
		});
		$("#status_sent_on").datepicker({
			changeMonth: true,
			changeYear: true,
			format:dateFormat,
			dateFormat: dateFormat
		});
		$("#status_recieved_on").datepicker({
			changeMonth: true,
			changeYear: true,
			format:dateFormat,
			dateFormat: dateFormat
		});
		$("#initial_issue_date").datepicker({
			changeMonth: true,
			changeYear: true,
			format:dateFormat,
			dateFormat: dateFormat
		});
		jQuery(".product").on("click", function() {
			checkProductsCount();
		})
		checkProductsCount();
		
		// Clear validation error when user interacts with a field
		jQuery(document).on('change keyup', '.validation-error', function() {
			jQuery(this).removeClass('validation-error');
			jQuery(this).closest('tr').find('th').removeClass('validation-error-label');
		});
		
		// Clear error when checkbox in error container is clicked
		jQuery(document).on('change', '.validation-error input[type="checkbox"]', function() {
			jQuery(this).closest('.validation-error').removeClass('validation-error');
		});
	});

	function nextYear() {
		jQuery("#surveillance").html("");
		var time = new Date().getTime();
		
		// Silently return if date is empty - validation happens on form submit
		if (jQuery("#date_of_issue").val().trim() == '') {
			return false;
		}
		
		date_of_issue = jQuery("#date_of_issue").val();
		cert_validity = jQuery("#cert_validity").val();
		$.post(prog_www + "/config/date_conv.inc.php?tm=" + time, {
				"getNextYear": "true",
				"date_of_issue": date_of_issue,
				"cert_validity": cert_validity
			},
			function(data) {
				if (data != "") {
					//parse json and fill the data
					data = JSON.parse(data);
					jQuery("#date_of_expiry").val(data['date_of_expiry']);
					if (data['surveillance']) {
						for (var fld in data['surveillance']) {
							sDate = data['surveillance'][fld];
							jQuery("#surveillance").append(fld + ' surveillance: <input name="certificate_options[surveillance][]"  type="text" class="date1" value="' + sDate + '"/> ');
						};
						rtfDate = data['Recertification'];
						jQuery("#surveillance").append(' Recertification:' + '<input name="certificate_options[recertification][]"  type="text" class="date1 disabled" value="' + rtfDate + '"/>');
					}
				};
			});
	}
	<?php if ($_SESSION['user_role'] != 'super_admin') { ?>

		function checkExpiryPeriod() {
			return false;
			iss = jQuery("#date_of_issue").val();
			exp = jQuery("#date_of_expiry").val();
			validity = jQuery("#certificateValidity").val();
			var time = new Date().getTime();
			jQuery("span#maxPeriod").removeClass("red");
			$.post(prog_www + "/config/date_conv.inc.php?checkPeriod=true&validity=" + validity + "&tm=" + time + "&iss=" + iss + "&exp=" + exp,
				function(data) {
					if (data != "") {
						nextYear(iss, "date_of_expiry");
						jQuery("span#maxPeriod").addClass("red");
					}
				});
		}
	<?php }; ?>

	function checProductBox(obj) {
		if (jQuery("#productsOl input[type='checkbox']").length) {
			jQuery("#productsOl input[type='checkbox']").prop("checked", obj.checked);
			checkProductsCount();
		}
	}

	function countChars(obj) {
		jQuery("#maxChars").html(jQuery(obj).attr("maxlength") - jQuery(obj).val().length)
	}

	function getSiteVersion(val) {
		<?php if (isset($_GET['clid'])) { ?>
			url = 'index.php?inc=certificate_add_edit&clid=<?php echo $_GET['clid']; ?>&act=<?php echo $_GET['act']; ?>&offid=<?php echo $_GET['offid']; ?><?php echo isset($_GET['crtNr']) ? '&crtNr=' . $_GET['crtNr'] : ''; ?>';
			window.location = url + val;
		<?php }; ?>
	}
	$(document).ready(function() {
		jQuery('.categoriesUl').css("width", jQuery('.categoriesUl').width() + 'px');
		$('.reference_standards').click(function(event) {
			jQuery("#referenceStandardsCount").removeClass("red");
			if ($(".reference_standards:checked").length > 5) {
				$(this).prop("checked", false);
				jQuery("#referenceStandardsCount").addClass("red");
				alert_message('Maximum 5 items (combined Halal standards)');
			}
		});
		$('.category').click(function(event) {
			jQuery("#categoriesCount").removeClass("red");
			if ($(".category:checked").length > 3) {
				$(this).prop("checked", false);
				jQuery("#categoriesCount").addClass("red");
				alert_message('Maximum 3 categories');
			}
		});
		$('.fa-question-circle').click(function(event) {
			catid = jQuery(this).data('id');
			$.post(prog_www + "/certificates/annual/get_info.php?act=getCategoryDescription&catid=" + catid,
				function(data) {
					if (data != "") {
						alert_message(data);
					}
				});
		})
	});

	function toggleCategory(cat) {
		if (jQuery('.cat_' + cat).is(":visible") == false)
			jQuery('.categoriesUl li.category').hide('slow');
		jQuery('.cat_' + cat).toggle('slow');
	}

	function checkOfficeSignature(offid) {
		jQuery('.officeSignature').checked = false;
		jQuery('.officeSignature').hide();
		if (document.getElementById('officeSignature_' + offid)) {
			document.getElementById('officeSignature_' + offid).style.display = 'inline-block';
		}

	}

	function copySiteAddress(obj) {
		var siteName = jQuery(obj).closest("li").find(".siteName").text();
		jQuery("#awarded_additional_title").val(siteName);
		var siteAddress = jQuery(obj).closest("li").find(".siteAddress").text();
		jQuery("#awarded_additional_text").val(siteAddress);
		jQuery("#insert_additional_title").prop("checked", true);
	}

	function clearAwardedAdditional() {
		jQuery("#awarded_additional_title").val('');
		jQuery("#awarded_additional_text").val('');
		jQuery("#insert_additional_title").prop("checked", false);
	}
</script>
<?php
include "$prog_path/config/connect.inc.php";

$company = "";
$scope_of_activities = '';
$reference_number = 1;
$row = array();
$certificate_options = array();
$annex_options = array();
$php = array();
$revision = array();
if (isset($_GET['act']) and $_GET['act'] == 'reissue') {
	$act = "edit";
	$_GET['reissue'] = 'y';
}
if (isset($_SESSION['clid']))
	$clid = $_SESSION['clid'];
elseif (isset($_GET['clid']))
	$clid = $_GET['clid'];

if (isset($act) and $act == "edit") {
	if (isset($_GET['ver'])) {
		if ($certificate_ver = $amdb->get_row("SELECT item_content FROM hqc_versions WHERE verid='$_GET[ver]'")) {
			$row = unserialize($certificate_ver['item_content']);
		}
	} else {
		$row = $amdb->get_row("SELECT *,acms_halal_certificates.offid as offid FROM $tbl[prefix]_halal_certificates, companies where  $tbl[prefix]_halal_certificates.clid = companies.clid and $tbl[prefix]_halal_certificates.crtNr='$crtNr'");
	}
} elseif ($clid != "") {
	$row = $amdb->get_row("SELECT * FROM companies where  clid = '$clid'");
	$act = "add";
}

if ($row) {
	if ($_GET['act'] == 'edit' && $row['date_of_issue'] == 0) {
		if (is_array(decode_json($row['certificate_content']))) {
			foreach (decode_json($row['certificate_content']) as $key => $content) {
				$doi = 0;
				$exp = 0;
				$date_of_issue = strtotime(fix_date($content['data']['date_of_issue']));
				$date_of_expiry = strtotime(fix_date($content['data']['date_of_expiry']));
				if ($date_of_issue > $doi) {
					$doi = $date_of_issue;
					$row['date_of_issue'] = $doi;
				}
				if ($date_of_expiry > $exp) {
					$exp = $date_of_expiry;
					$row['date_of_expiry'] = $exp;
				}
			};
		}
	}

	if (isset($row['options']) and trim($row['options']) != '' and is_array(json_decode($row['options'], true)))
		$certificate_options = json_decode($row['options'], true);
	if (isset($row['annex_options']) and trim($row['annex_options']) != '' and is_array(json_decode($row['annex_options'], true)))
		$annex_options = json_decode($row['annex_options'], true);
	if (isset($row['revision']) and trim($row['revision']) != '' and is_array(json_decode($row['revision'], true))) {
		$revision = json_decode($row['revision'], true);
		$revision['insert'] = true;
	}
	$awarded_to = $row['company_name'];
	$company_country = $row['country1'];
	$scope_of_activities = $row['scope_of_activities'];
	$company = "
	<input type='hidden' name='clid' value='$clid' />
	<b>$row[company_name]</b><br>
		$row[street1]<br/>
		$row[zip1], $row[city1]<br />
		$row[country1]<br />
		<b>EEC No.:</b>$row[ec_number]";
	if ($active = $amdb->get_row("SELECT * FROM users WHERE clid='$clid' AND active='n'")) {
		$company .= "<br/><h2 style=\"color:red\">THIS CLIENT IS DELETED</H2>";
	}
} else {
	$awarded_to = '';
	$products = array();
	if ($productsCount = $amdb->get_results("SELECT clid,count(clid) AS prds FROM acms_hdcs_products
								WHERE  approved='y' and status = 'active'
								GROUP BY clid")) {
		foreach ($productsCount as $product) {
			$products[$product['clid']] = $product['prds'];
		}
	}
	$result = get_clients("companies.clid,companies.company_name,companies.scope_of_activities,companies.email1,companies.country1");
	if (count($result) > 0) {
		$company = '<select size=1 name="clid" style="max-width:400px" id="clid" class="searchable" data-required="yes" onchange="redirectToNewCertificate();"><option value="">Select a company</option>';

		foreach ($result as $row) {
			if ($row['country1'] != 'Israel') {
				if (isset($clid) and $clid == $row['clid']) {
					$awarded_to = $row['company_name'];
					$company_country = $row['country1'];
					$scope_of_activities = $row['scope_of_activities'];
					$company .= "<option value='$row[clid]' selected=\"selected\">$row[company_name]</option>";
				} else {
					if (isset($products[$row['clid']]))
						$totPrds = $products[$row['clid']];
					else
						$totPrds = '0';
					$company .= "<option value='$row[clid]'>$row[company_name] ($totPrds products)</option>";
				}
			}
		}
		$company .= "</select>";
	}
	$act = "add";
}
if (isset($_SESSION['offid']) && $_SESSION['offid'] != 0)
	$_GET['offid'] = $_SESSION['offid'];

if (!isset($_GET['offid']))
	$_GET['offid'] = 0;
if ($user_type != "client") {
	if (!$template = $amdb->get_row("SELECT content,php,revision FROM office_certificate_templates WHERE offid='$_GET[offid]' and status='active' and type='annual'"))
		$template = $amdb->get_row("SELECT content,php,revision FROM office_certificate_templates WHERE offid='0' and status='active' and type='annual'");
}

// Determine action type and styling
$isEdit = isset($_GET['act']) && $_GET['act'] == 'edit';
$isReissue = isset($_GET['act']) && $_GET['act'] == 'reissue';

if ($isEdit) {
    $actionText = 'Update';
    $actionClass = 'update';
    $actionIcon = 'fa-edit';
} elseif ($isReissue) {
    $actionText = 'Reissue';
    $actionClass = 'reissue';
    $actionIcon = 'fa-redo';
} else {
    $actionText = 'Issue';
    $actionClass = 'issue';
    $actionIcon = 'fa-plus-circle';
}

// Get office name if available
$officeName = '';
if (isset($office) && isset($office['office_name'])) {
    $officeName = $office['office_name'];
}

// === DMC Conducted Check (for blocking HC issuance) ===
$dmcConducted = false;
$dmcPending = false;
$dmcMeeting = null;


if (isset($clid) && $clid > 0) {


    // First check: if the certificate itself is already approved by DMC, trust that
    if (isset($row['approved_by_dmc']) && $row['approved_by_dmc'] == 'yes') {
        $dmcConducted = true;
    } else {
        // Second check: look for an approved DMC decision for this client
        $dmcCheck = $amdb->get_row(
            "SELECT decid, status, meeting_date, event_details FROM hqc_committee_decision 
             WHERE clid = '" . intval($clid) . "' AND status = 'approved'
             ORDER BY decid DESC LIMIT 1"
        );
        if ($dmcCheck) {
            $dmcMeeting = $dmcCheck;
            $dmcConducted = true;
        } else {
            // Check if there's a pending decision
            $dmcCheckPending = $amdb->get_row(
                "SELECT decid, status, meeting_date, event_details FROM hqc_committee_decision 
                 WHERE clid = '" . intval($clid) . "' AND status = 'pending'
                 ORDER BY decid DESC LIMIT 1"
            );
            if ($dmcCheckPending) {
                $dmcMeeting = $dmcCheckPending;
                $dmcPending = true;
            }
        }
    }
}

?>
	<form action="" method="post" target="_blank" data-target="fIframe" id="addEditForm" name="addEditForm">
	
		<!-- Validation Summary Box -->
		<div id="validationSummary" class="validation-summary">
			<div class="validation-summary-header">
				<i class="fas fa-exclamation-triangle"></i>
				<span>Please fix the following errors:</span>
			</div>
			<ul class="validation-summary-list" id="validationSummaryList"></ul>
		</div>

		<?php /* if (isset($clid) && $clid > 0 && !$dmcConducted) { ?>
		<?php if ($dmcPending && $dmcMeeting) { 
			$dmcEventDetails = json_decode($dmcMeeting['event_details'], true);
		?>
		<div id="dmcWarningBanner" style="
			background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
			border: 1px solid #93c5fd;
			border-radius: 8px;
			padding: 14px 20px;
			margin: 10px 0 16px;
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		">
			<i class="fas fa-clock" style="color: #2563eb; font-size: 22px;"></i>
			<div style="flex:1; min-width:200px;">
				<strong style="color: #1e40af;">DMC Meeting Pending Approval</strong><br/>
				<span style="color: #1e3a5f; font-size: 13px;">
					A DMC meeting has been scheduled<?php if (is_array($dmcEventDetails) && isset($dmcEventDetails['date'])) { echo ' on <strong>' . date("d/m/Y", strtotime($dmcEventDetails['date'])) . '</strong>'; } ?>. Print and Authorize actions are blocked until the meeting is conducted and approved.
				</span>
			</div>
			<a href="/iidc/committee/index.php?inc=meetings" 
			   style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; 
			          background:linear-gradient(135deg, #2563eb, #3b82f6); color:#fff; 
			          border-radius:6px; text-decoration:none; font-weight:600; font-size:13px; white-space:nowrap;">
				<i class="fas fa-eye"></i> View DMC Meeting
			</a>
		</div>
		<?php } else { ?>
		<div id="dmcWarningBanner" style="
			background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
			border: 1px solid #fed7aa;
			border-radius: 8px;
			padding: 14px 20px;
			margin: 10px 0 16px;
			display: flex;
			align-items: center;
			gap: 12px;
			flex-wrap: wrap;
		">
			<i class="fas fa-exclamation-triangle" style="color: #ea580c; font-size: 22px;"></i>
			<div style="flex:1; min-width:200px;">
				<strong style="color: #9a3412;">DMC Meeting Not Yet Conducted</strong><br/>
				<span style="color: #78350f; font-size: 13px;">
					Print and Authorize actions are blocked until a DMC meeting is conducted and approved for this client.
				</span>
			</div>
			<a href="/iidc/committee/index.php?inc=schedule_committee&act=add&clid=<?php echo $clid; ?>&offid=<?php echo $_GET['offid']; ?>" 
			   style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; 
			          background:linear-gradient(135deg, #ea580c, #f97316); color:#fff; 
			          border-radius:6px; text-decoration:none; font-weight:600; font-size:13px; white-space:nowrap;">
				<i class="fas fa-calendar-plus"></i> Schedule DMC Meeting
			</a>
		</div>
		<?php } ?>
		<?php } */ ?>
	
		<input type="hidden" name="act" id="act" value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reissue') ? "add" : $act ?>" />
		<input type="hidden" value="" name="crtDo" />
		<?php if (isset($dmc)) { ?>
		<input type="hidden" value="<?php echo $dmc; ?>" name="decid" />
		<?php } ?>
		<input type="hidden" value="certsList" name="afterPrint" id="afterPrint" />
		<input type="hidden" value="" name="products" id="selectedProducts" />
		<input type="hidden" data-check=".product" value=".product" data-min='1' data-error="Please select at least one product" />
		<input type="hidden" data-check=".reference_standards" data-min='1' data-error="Please select at least one Halal Reference standards" />
		<input type="hidden" data-check=".category" data-min='1' data-error="Please select at least one category" />
		<input type="hidden" value="<?php echo $_GET['offid']; ?>" name="offid" />
		<input type="hidden" value="<?php echo ($_SESSION['user_type'] == 'client') ? 'new' : 'active'; ?>" name="status" />
		<?php if (isset($_GET['verid'])) { ?>
			<input type="hidden" name="certificate_options[verid]" value="<?php echo $_GET['verid']; ?>" />
		<?php }; ?>
		<?php if (isset($_GET['stid'])) { ?>
			<input type="hidden" name="certificate_options[stid]" value="<?php echo $_GET['stid']; ?>" />
		<?php }; ?>
		<input type="hidden" id="approval_required" name="certificate_options[approval_required]" value="no" />
		<?php

		if ($user_type == "client" or $act == "edit" and !isset($_GET['reissue']))
    		echo "<input type=\"hidden\" name=\"clid\" id=\"clid\" value=\"$clid\">";
		if (isset($act) and $act == "edit") {
		?>
			<input type="hidden" id="crtNr" name="crtNr" value="<?php echo $crtNr; ?>" />
			<?php if (trim($row['certificate_nr']) != '') { ?>
				<input type="hidden" name="certificate_nr" value="<?php echo $row['certificate_nr']; ?>" />
			<?php }; ?>
		<?php
		}
		if (isset($_GET['reissue'])) {
			echo "<input type=\"hidden\" name=\"clid\" value=\"$clid\"/>
<input type=\"hidden\" name=\"reissue\" value=\"y\"/>";
		}
		?>
		 <div class="annual-cert-header">
        <div class="annual-cert-header-content">
            <div class="annual-cert-header-icon">
                <i class="fas fa-certificate"></i>
            </div>
            
            <div class="annual-cert-header-info">
                <h2>
                    Annual Halal Certificate
                    <span class="action-badge <?php echo $actionClass; ?>">
                        <i class="fas <?php echo $actionIcon; ?>"></i>
                        <?php echo $actionText; ?>
                    </span>
                </h2>
                <p>Halal certification for products and manufacturing processes</p>
            </div>
            
            <div class="annual-cert-header-meta">
                <span class="cert-badge annual">
                    <i class="fas fa-award"></i>
                    Annual Certificate
                </span>
                
                <?php if ($officeName != '') { ?>
                    <span class="office-badge">
                        <i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($officeName); ?>
                    </span>
                <?php } ?>
            </div>
        </div>
        
         
    </div>

<div class="annual-cert-form-container">
    <table id="certTbl" class="alternate">

			<tr>
				<th style="min-width:150px">Company:*</th>
				<td colspan="4">
					<?php
					echo $company;
					if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_GET[offid]'")) {
						if (isset($office['options']) && is_array(json_decode($office['options'], true))) {
							$options = json_decode($office['options'], true);
							if (isset($options['restricted_standards']))
								$restricted_standards = $options['restricted_standards'];
							else
								$restricted_standards = array();
						}
					}
					?>
					<script>
						function redirectToNewCertificate() {
							document.location.href = 'index.php?inc=certificate_add_edit&clid=' + document.getElementById("clid").value + '&act=add&offid=<?php echo $_GET['offid']; ?>';
						}
					</script>

					<?php if (!isset($_GET['clid'])) { ?>
						<button onclick="redirectToNewCertificate()" class="btn btn-primary" type="button" style="margin-left:10px;">New Certificate</button>
					<?php } ?>
				</td>
			</tr>

			<?php
			
			if (trim($awarded_to) != '') {  ?>
				<?php
				$certificate_validity = 1;
				$annual_permissions = array();
				if (trim($office['certificate_permissions']) != '' && is_array(json_decode($office['certificate_permissions'], true))) {
					$certificate_permissions = json_decode($office['certificate_permissions'], true);
					if (isset($certificate_permissions['annual']['validity']))
						$certificate_validity = $certificate_permissions['annual']['validity'];
					if (isset($certificate_permissions['annual'])) {
						$annual_permissions = $certificate_permissions['annual'];
					}
				}
				$certificate_validity = 4;				
				?>
				<tr>
					<th>Manufacturing Sites:</th>
					<td colspan="3">
						<?php

						if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status!='deleted' AND clid='$_GET[clid]'")) {
							if (count($sites) > 0) {
								if ($act == 'edit')
									$selectedSite = explode(',', $row['manufacturing_site']);
								else
									$selectedSite = array();
						?>
								<ul style="padding:0px" id="manufacturingSites" class="alternateOn">
									<?php foreach ($sites as $site) {
										if (trim($site['site_address']) != '' and is_array(json_decode($site['site_address'], true))) {
											$site_address = json_decode($site['site_address'], true);
										}
									?>
										<li style="padding: 2px;;">
											<label><input type="checkbox" name="manufacturing_site[]" value="<?php echo $site['stid']; ?>" <?php echo ($act == 'edit' && in_array($site['stid'], $selectedSite)) ? 'checked' : ''; ?> /> <?php echo (trim($site['site_name']) != '') ? '<b class="siteName">' . $site['site_name'] . '</b>' : ''; ?> <span class="siteAddress"><?php echo (isset($site_address)) ? $site_address['street'] . ', ' . $site_address['zipcode'] . ' ' . $site_address['city'] . ', ' . $site_address['country'] : ''; ?></span></label> <i class="far fa-clone" style="display:none;color:darkcyan;margin-top:5px;font-size:12px !important;position:absolute;right:10px;" onclick="copySiteAddress(this)"><span>Copy into additional title</span></i>
										</li>
									<?php }; ?>
								</ul>
								<div style="margin-top:20px;border:1px solid #eee;padding:0px; background: lightyellow;">
									<label><input type="checkbox" name="certificate_options[manufacturing_sites_OL]" <?php echo (isset($certificate_options['manufacturing_sites_OL'])) ? 'checked' : ''; ?> /> &nbsp;&nbsp;Manufacturing sites on one line</label>
									<br />
									<label><input type="checkbox" name="certificate_options[awarded_to_site]" <?php echo (isset($certificate_options['awarded_to_site'])) ? 'checked' : ''; ?> id="awarded_to_site" onclick="$('#awarded_as_site_label').css('display',this.checked?'inline-block':'none')" /> &nbsp;&nbsp;Awarded to Manufacturing site</label>

									<label id="awarded_as_site_label" style="display: <?php echo (isset($certificate_options['awarded_to_site'])) ? 'inline-block' : 'none'; ?> ;"><input type="checkbox" name="certificate_options[awarded_as_site]" <?php echo (isset($certificate_options['awarded_as_site'])) ? 'checked' : ''; ?> id="awarded_as_site" /> Print company address as Manufacturing site address</label>
								</div>
						<?php };
						}; ?>
					</td>
				</tr>
			<?php };  ?>
			<?php if (isset($_GET['clid']) and trim($_GET['clid']) != '') { ?>

				<?php
				if ($sitesData = $amdb->get_results("SELECT companies_production_sites.*,acms_hdcs_products.site,acms_hdcs_products.prdid FROM companies_production_sites
									LEFT JOIN  acms_hdcs_products ON acms_hdcs_products.site = companies_production_sites.stid
									where  companies_production_sites.clid = '$_GET[clid]' and companies_production_sites.status != 'deleted' and acms_hdcs_products.site!='0' AND acms_hdcs_products.status='active' GROUP BY acms_hdcs_products.site")) {
					if (isset($sitesData) and count($sitesData) > 0) {
						$selectFromSites = array();
						foreach ($sitesData as $site) {
							if (trim($site['site_name']) != '') {
								$selectFromSites[$site['stid']] = $site['site_name'];
							} else {
								if (is_array(json_decode($site['site_address'], true))) {
									$site_address = json_decode($site['site_address'], true);
									$selectFromSites[$site['stid']] = $site_address['street'];
								}
							}
						}
						asort($selectFromSites);
					}
				}

				if ($products_version  = $amdb->get_results("SELECT verid,version_name FROM companies_products_version WHERE clid = '$_GET[clid]' and status != 'deleted'  ORDER BY version_name ASC")) {
					$versions = array();
					if ($_GET['act'] != 'edit') {
						$whr = " AND status='active'";
					} else {
						$whr = " AND status='active'";
					}
					foreach ($products_version as $version) {
						if ($versionsData = $amdb->get_row("SELECT prdid,versions FROM acms_hdcs_products WHERE FIND_IN_SET($version[verid],versions) AND clid = '$_GET[clid]' $whr")) {
							$versions[$version['verid']] = $version['version_name'];
						}
					}
				}
				?>
				<tr>
					<th>Products:* <input type="checkbox" onclick="checProductBox(this)" /></th>
					<td colspan="4" id="productTd">
						<?php
						if ($act == 'edit' or $act == "reissue")
							$products = explode(',', $row['products']);
						else
							$products = array();
						if (isset($clid)) {
							$whr = '';
							if (isset($_GET['stid']) and trim($_GET['stid']) != '') {
								$whr = "and acms_hdcs_products.site='$_GET[stid]'";
							}
							if (isset($_GET['verid']) and trim($_GET['verid']) != '') {
								$whr = "and FIND_IN_SET($_GET[verid],versions)";
							}

							$whr .= " AND acms_hdcs_products.status='active'";
							if (($_GET['act'] == 'edit' or $_GET['act'] == 'reissue')) {
								$oldProducts = $amdb->get_results("SELECT * FROM acms_hdcs_products where prdid IN ($row[products]) AND approved='y' AND acms_hdcs_products.status='deleted' ORDER BY prdid ASC");
							}

							$resultPrd = $amdb->get_results("SELECT * FROM acms_hdcs_products where  approved='y' and clid = '$_REQUEST[clid]' $whr ORDER BY prdid ASC");

							if (isset($_GET['oldProducts']) && isset($oldProducts) && count($oldProducts) > 0) {
								$resultPrd = array_merge($oldProducts, $resultPrd);
							}

							if (count($resultPrd) == 0) {
								echo "<div style='color:red'>No products found for this company</div>";
							}
						?>
							<?php if (isset($selectFromSites) or isset($versions) > 0) { ?>
								<select name="productionSiteVersion" size="1" onchange="getSiteVersion(this.value)">
									<option value="">All products</option>
									<?php if (isset($oldProducts) or isset($_GET['oldProducts'])) { ?>
										<option value="&oldProducts=1" <?php echo (isset($_GET['oldProducts']) && $_GET['oldProducts'] == 1) ? 'selected' : ''; ?>>All Including deleted products</option>
									<?php }; ?>
									<?php if (isset($selectFromSites) and count($selectFromSites) > 0) { ?>
										<?php foreach ($selectFromSites as $stid => $sitename) { ?>
											<option value="&stid=<?php echo $stid; ?>" <?php echo (isset($_GET['stid']) && $_GET['stid'] == $stid) ? 'selected' : ''; ?>>Site: <?php echo $sitename; ?></option>
										<?php }; ?>
									<?php }; ?>
									<?php if (isset($versions) and count($versions) > 0) { ?>
										<?php foreach ($versions as $verid => $version_name) { ?>
											<option value="&verid=<?php echo $verid; ?>" <?php echo (isset($_GET['verid']) && $_GET['verid'] == $verid) ? 'selected' : ''; ?>>Version: <?php echo $version_name; ?></option>
										<?php }; ?>
									<?php }; ?>
								</select>
							<?php }; ?>
							<a href="/products?idclient=<?php echo $_GET['clid']; ?>" onclick="set_session_url('goBack','Annual certificate')">Manage Products</a> (Total selected products: <span id="productsCount"></span>)
							<?php if (isset($oldProducts) and count($oldProducts) > 0 && !isset($_GET['oldProducts'])) { ?>
								<div style="color:darkred;margin:5px 0"><i class="fas fa-exclamation-triangle" style="position: inherit;color: red;"></i> There are one or more products are deleted from the system. To reuse them again select (All including deleted products). </div>
						<?php };
							if (isset($resultPrd) and count($resultPrd) > 0) {
								$selected_products = array();
								$prohibited_products = array();
								$doubles = 0;
								echo "<ul style=\"padding:10px;max-height:250px;overflow:auto;margin-top:15px !important;\" id=\"productsOl\">";
								foreach ($resultPrd as $rowPrd) {

									$product_item = $rowPrd['article_nr'] . $rowPrd['product_name'] . $rowPrd['description'];
									$liStyle = '';
									$double = false;
									if (!in_array($product_item, $selected_products) && trim($rowPrd['product_name']) != '') {
										$selected_products[] = $product_item;
									} else {
										$liStyle = "color:red;";
										$double = true;
										if (isset($_GET['verid']))
											$doubles++;
									}
									if (in_array($rowPrd['prdid'], $products))
										$checked = " checked";
									else
										$checked = "";
									if ($double == false) {
										echo "<li style=\"position:relative; $liStyle\">";
										if (!strstr(strtolower($rowPrd['product_name']), 'halal') && isset($rowPrd['prohibited']) && trim($rowPrd['prohibited']) == 'yes') {
											echo "<i class='fas fa-exclamation-triangle prohibited' style='color:red'></i>";
											if ($badWord = explode('||', prohibited_words($rowPrd['article_nr'] . '||' . $rowPrd['product_name'], true))) {
												$rowPrd['product_name'] = $badWord[1];
												$rowPrd['article_nr'] = $badWord[0];
											}
										}
										echo "<label><input type=\"checkbox\" class=\"product\" data-name=\"product[$rowPrd[prdid]]\" value=\"$rowPrd[prdid]\" $checked/> $rowPrd[product_name]";
										if (trim($rowPrd['article_nr']) != "")
											echo " (" . clean_string($rowPrd['article_nr']) . ")";
										if (trim($rowPrd['description']) != "")
											echo " - " . clean_string($rowPrd['description']);

										echo "</label>";
										echo "</li>";
									} elseif (isset($_GET['verid'])) {
										echo "<li style=\"position:relative;\" id=\"product_$rowPrd[prdid]\" class=\"double\">";
										echo "<label><input type=\"checkbox\" class=\"product\" data-name=\"product[$rowPrd[prdid]]\" value=\"$rowPrd[prdid]\" $checked/> $rowPrd[product_name]";
										if (trim($rowPrd['article_nr']) != "")
											echo " (" . clean_string($rowPrd['article_nr']) . ")";
										if (trim($rowPrd['description']) != "")
											echo " - " . clean_string($rowPrd['description']);
										echo "</label>";
										echo "<span style=\"color:red\"> (Duplicate product)</span>";
										echo "<span class=\"removeProduct\"><i class=\"fas fa-times\" onclick=\"removeProduct($rowPrd[prdid])\"><span>Remove product</span></i></span>";
										echo "</li>";
									}
								}
								echo "</ul>";
								if (isset($doubles) && $doubles > 0) {
									echo "<div style=\"color:red;padding:2px 0;position:relative\">There are $doubles duplicate products. Please remove the duplicates from the product list.<span class=\"removeProduct\"><i class=\"fas fa-times\" onclick=\"removeProduct('*')\"><span>Remove all duplicates</span></i></span></div>";
								}
							}
						}
						?>
					</td>
				</tr>
				<tr>
					<th>Reference halal standards:* </th>
					<td colspan="3">
						<?php
						if (isset($row['reference_standards']) && is_array(json_decode($row['reference_standards'], true)))
							$stnids = json_decode($row['reference_standards'], true);
						else
							$stnids = array();

						if ($act == 'add')
							$stid = '';
						?>
						<ul id="halalStandards" style="padding: 10px;margin:0px;height:200px;overflow:auto" class="alternateOn">
							<?php
							$standards = array();

							if (count($stnids) > 0)
								$whr = "OR stnid IN (" . implode(',', $stnids) . ")";
							else
								$whr = '';
							$standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE status='active' $whr ORDER BY code ASC");

							if (isset($_SESSION['user']) && ($_SESSION['user']['uid'] == 4 or $_SESSION['user']['uid'] == 5) or (isset($options['print_jakim']) && $options['print_jakim'] == 'yes')) {
								$SM_Pass = true;
							} else {
								$SM_Pass = false;
							}

							foreach ($standards as $standard) {
							?>
								<li><label>
										<input type="checkbox" name="reference_standards[]" class="reference_standards" value="<?php echo $standard['stnid']; ?>" <?php echo (in_array($standard['stnid'], $stnids)) ? 'checked' : ''; ?> data-standard="<?php echo $standard['code']; ?>" data-org="<?php echo $standard['organisation']; ?>" /><?php echo $standard['code']; ?>: <?php echo $standard['description']; ?></label></li>
							<?php };	?>
						</ul>
						<i style="padding-left:10px" id="referenceStandardsCount">Maximum 5 items</i>
					</td>
				</tr>
				<tr>
					<th>Scope of certification:*</th>
					<td colspan="3">
						<textarea name="scope_of_certification" id="scope_of_certification" style="width:95%;height:80px" maxlength="500" onkeyup="countChars(this)" data-required="yes"><?php echo ($act == 'edit' or isset($_GET['reissue'])) ? @$row['scope_of_certification'] : $scope_of_activities; ?></textarea>
						<div>Maximum characters:<span id="maxChars">500</span></div>
					</td>
				</tr>
				<tr>
    <th>Category:*</th>
    <td colspan="3">
        <?php
        if (($act == 'edit' or isset($_GET['reissue'])) && isset($row['category']) && is_array(json_decode($row['category'], true)))
            $catids = json_decode($row['category'], true);
        else
            $catids = array();
        if ($act == 'add')
            $catid = '';
        ?>
        <ul style="padding: 10px; margin: 0px;" class="alternateOn categoriesUl">
            <?php
            if ($categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE status='active'")) {
                $category_name = '';
                foreach ($categories as $category) {
            ?>
                    <?php if ($category['category_name'] != $category_name) { ?>
                        <li class="main-category">
                            <label>
                                <input type="checkbox" name="category[]" class="category main-cat-checkbox" 
                                       value="<?php echo $category['catid']; ?>" 
                                       data-parent="<?php echo $category['category']; ?>"
                                       <?php echo (isset($catids) && in_array($category['catid'], $catids)) ? 'checked' : ''; ?> />
                                <b><?php echo $category['category'] . ": " . $category['category_name']; ?></b>
                            </label>
                            <i class="fas fa-angle-double-down" onclick="toggleCategory('<?php echo $category['category']; ?>')"></i>
                            <?php $category_name = $category['category_name']; ?>
                        </li>
                    <?php }; ?>
                    <li class="cat_<?php echo $category['category']; ?> sub-category" style="display:none">
                        <label>
                            <input type="checkbox" name="sub_category[]" class="sub-category-checkbox" 
                                   value="<?php echo $category['catid']; ?>"
                                   data-parent="<?php echo $category['category']; ?>"
                                   <?php echo (isset($catids) && in_array($category['catid'], $catids)) ? 'checked' : ''; ?> />
                            <span><?php echo $category['code']; ?>: <?php echo $category['description']; ?></span>
                        </label>
                        <?php if (trim($category['exapmle']) != '') { ?>
                            <i class="far fa-question-circle" data-id="<?php echo $category['catid']; ?>"></i>
                        <?php }; ?>
                    </li>
            <?php };
            }; ?>
        </ul>
        <i style="padding-left:10px" id="categoriesCount">Maximum 3 categories</i>
    </td>
</tr>
				<?php if ($_SESSION['user_type'] == "admin") { ?>
					<?php if (isset($_GET['reissue'])) {
						$act = "add";
					};
					?>
					<?php
					if ($_GET['act'] == 'edit')
						$disabled = 'readonly class="disabled"';
					else
						$disabled = '';
 					?>
					<tr>
						<th>Issue & Expiry Dates:*</th>
						<td>
							<b>Issue:</b> <input type="text" name="date_of_issue" class="date1" id="date_of_issue" onchange="nextYear()" value="<?php echo ($_GET['act'] == 'edit' and $row['date_of_issue'] != 0) ? web_date($row['date_of_issue']) : ''; ?>" <?php echo $disabled; ?> 
							<b>Expiry:</b> <input type="text" name="date_of_expiry" class="date1" id="date_of_expiry" value="<?php echo ($act == 'edit' and $row['date_of_expiry'] != 0) ? web_date($row['date_of_expiry']) : ''; ?>" />
							<b>Validity:</b>
							<input type="number" name="certificate_options[cert_validity]" id="cert_validity" onchange="nextYear()" max="<?php echo $certificate_validity; ?>" min="1" value="<?php echo isset($certificate_options['cert_validity']) ? $certificate_options['cert_validity'] : 1; ?>" <?php echo $disabled; ?> style="width:50px;" /> years
							<span id="maxPeriod"><i>(Maximum <?php echo $certificate_validity; ?> year<?php echo $certificate_validity != 1 ? 's' : ''; ?></i>)</span>
							<b>Initial issue date:</b> <input type="text" class="date1" name="initial_issue_date" id="initial_issue_date" value="<?php echo ($act == 'edit') ? web_date($row['initial_issue_date']) : ''; ?>" />
							<div style="margin-top: 5px;" id="surveillance">
								<?php if (isset($certificate_options['surveillance']) and count($certificate_options['surveillance']) > 0) {
									$preSur = array('1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th');
									$surveillance = $certificate_options['surveillance'];
									foreach ($surveillance as $key => $sur) {
										echo $preSur[$key] . ' Surveillance: <input type="text" class="date1" name="certificate_options[surveillance][]" value="' . $sur . '" />';
									};
								?>
									Recertification: <input type="text" class="date1 disabled" name="" value="<?php echo ($_GET['act'] == 'edit' and $row['date_of_expiry'] != 0) ? web_date($row['date_of_expiry']) : ''; ?>" disabled />
								<?php }; ?>
							</div>
						</td>
					</tr>
					<tr id="ApprovalTr">
						<th>Signatory:</th>
						<td colspan="4" id="approval">
							<select name="signatory" class="searchable" id="signatory">
								<option value="">Select Signatory</option>
								<?php
								$signatories = get_signatories('annual', $_GET['offid']);
								if (count($signatories) > 0) {
									foreach ($signatories as $signatory) {
										if (isset($row['signatory']) && $row['signatory'] == $signatory['id'])
											echo "<option value=\"" . $signatory['id'] . "\" selected=\"selected\">" . htmlspecialchars($signatory['name']) . " (" . $signatory['position'] . ")</option>";
										else
											echo "<option value=\"" . $signatory['id'] . "\">" . htmlspecialchars($signatory['name']) . " (" . $signatory['position'] . ")</option>";
									}
								}
								?>
							</select>
							<info>The certificate will be signed by the selected signatory</info>
							<div class="infoBox">You can add or remove Signatories by clicking on setups->certificates Signatories</div>
						</td>
					</tr>
				<?php }; ?>
				<input type="hidden" name="offid" value="<?php echo $_GET['offid']; ?>" />
				<?php
				$certificate_fonts = array();
				$fonts = array(
					'awarded_to' => 'Awarded company name|14',
					'company_address' => 'Company address|14',
					'manufacturing_address' => 'Manufacturing site address(es)|14',
					'reference_standards' => 'Reference halal standards',
					'scope_of_certification' => 'Scope of certification',
					'category' => 'Product category',
					'products' => 'Products columns on annex pages(s)'
				);
				if (isset($certificate_options['fonts'])) {
					$certificate_fonts = $certificate_options['fonts'];
				}
				?>
				<tr>
					<th>Layout font sizes & columns:</th>
					<td colspan="3">
						<b onclick="jQuery('#font_sizes').toggle('slow')" style="cursor:pointer"><i class="fas fa-angle-double-down" style="font-size:14px !important"></i> Font sizes</b>
						<ul style="padding:10px;margin:10px 0px;display:none;border:1px solid #eee;overflow:hidden" id="font_sizes">
							<li style="padding:5px">Font sizes are in pixels</li>
							<?php
							foreach ($fonts as $fontKey => $fontValue) {
								$defSize = 15;
								if (strstr($fontValue, '|')) {
									$fontValues = explode('|', $fontValue);
									$fontValue = $fontValues[0];
									$defSize = $fontValues[1];
								}
							?>
								<li style="padding:5px 0px;margin:0px 2px;background:none !important;border-bottom:1px solid #ccc"><label><input type="number" name="certificate_options[fonts][<?php echo $fontKey; ?>]" id="<?php echo $fontKey; ?>" style="width:60px;" data-required="yes" value="<?php echo (isset($certificate_fonts[$fontKey])) ? $certificate_fonts[$fontKey] : $defSize; ?>" /> <?php echo $fontValue; ?> (default size: <?php echo $defSize; ?>)</label></li>
							<?php	} 	?>
						</ul>
						<b onclick="jQuery('#productsColumns').toggle('slow')" style="cursor:pointer"><i class="fas fa-angle-double-down" style="font-size:14px !important"></i>Product columns</b>
						<div id="productsColumns" style="display:none">
							<?php
							$annex_titles = array(
								'columns' => 'AutNr,article_nr,product_name,description,brand_name',
								'AutNr' => 'Nr',
								'AutNr_width' => '1.5',
								'article_nr' => 'Article code',
								'article_nr_width' => '3.5',
								'product_name' => 'Product name',
								'product_name_width' => '6',
								'description' => 'Description',
								'description_width' => '3',
								'brand_name' => 'Brand name',
								'brand_name_width' => '3'
							);
							if ($act == 'edit') {
								if (isset($annex_options['columns'])) {
									if (count(explode(',', $annex_options['columns'])) != count(explode(',', $annex_titles['columns'])))
										$annex_options['columns'] = $annex_titles['columns'];
								}
								foreach ($annex_titles as $keyTitle => $title) {
									if (isset($annex_options[$keyTitle]))
										$annex_titles[$keyTitle] = $annex_options[$keyTitle];
								}
							}
							$productsColumns = array();
							?>
							<input type="hidden" name="annex_options[columns]" id="annex_options_columns" style="width:100% !important" value="<?php echo $annex_titles['columns']; ?>" />
							<?php ob_start(); ?>
							<b><input type="checkbox" value="yes" name="annex_options[add_AutNr]" id="adAutNr" <?php echo $act == 'edit' && isset($annex_options['add_AutNr']) ? 'checked="checked"' : ($act == 'add' ? 'checked="checked"' : ''); ?> /> Serial number</b>
							<input type="text" name="annex_options[AutNr]" id="annex_title_AutNr" data-required="yes" style="width:80px" title="Auto number" value="<?php echo $annex_titles['AutNr']; ?>" />
							<input type="text" name="annex_options[AutNr_width]" id="annex_title_AutNr_width" data-required="yes" style="width:40px" title="Auto number width" value="<?php echo $annex_titles['AutNr_width']; ?>" />cm
							<?php $productsColumns['AutNr'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start() ?>
							<b><input type="checkbox" value="yes" name="annex_options[add_article_nr]" id="adArticleNr" <?php echo ($act == 'edit' && isset($annex_options['add_article_nr'])) ? 'checked="checked"' : ''; ?> /> Article Nr/Code</b>
							<input type="text" name="annex_options[article_nr]" id="annex_title_article_nr" data-required="yes" style="width:110px" title="Article code" value="<?php echo $annex_titles['article_nr']; ?>" />
							<input type="text" name="annex_options[article_nr_width]" id="annex_title_article_nr_width" data-required="yes" style="width:40px" title="Article code width" value="<?php echo $annex_titles['article_nr_width']; ?>" />cm
							<?php $productsColumns['article_nr'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_product_name]" checked="checked" disabled="disabled" /> Product name</b>
							<input type="text" name="annex_options[product_name]" id="annex_title_product_name" data-required="yes" style="width:110px" title="Product Name" value="<?php echo $annex_titles['product_name']; ?>" />
							<input type="text" name="annex_options[product_name_width]" id="annex_title_product_name_width" data-required="yes" style="width:40px" title="Product name Width" value="<?php echo $annex_titles['product_name_width']; ?>" />cm
							<?php $productsColumns['product_name'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_description]" value="yes" <?php echo ($act == 'edit' && isset($annex_options['add_description'])) ? 'checked="checked"' : ''; ?> /> Description</b>
							<input type="text" name="annex_options[description]" id="annex_title_description" data-required="yes" style="width:110px" title="Description" value="<?php echo $annex_titles['description']; ?>" />
							<input type="text" name="annex_options[description_width]" id="annex_title_description_width" data-required="yes" style="width:40px" title="Description width" value="<?php echo $annex_titles['description_width']; ?>" />cm
							<?php $productsColumns['description'] = ob_get_contents();
							ob_end_clean(); ?>
							<?php ob_start(); ?>
							<b><input type="checkbox" name="annex_options[add_brand_name]" value="yes" <?php echo ($act == 'edit' && isset($annex_options['add_brand_name'])) ? 'checked="checked"' : ''; ?> /> Brand name</b>
							<input type="text" name="annex_options[brand_name]" id="brand_name" data-required="yes" style="width:110px" title="Brand name" value="<?php echo $annex_titles['brand_name']; ?>" />
							<input type="text" name="annex_options[brand_name_width]" id="brand_name_width" data-required="yes" style="width:40px" title="Description width" value="<?php echo $annex_titles['brand_name_width']; ?>" />cm
							<?php $productsColumns['brand_name'] = ob_get_contents();
							ob_end_clean(); ?>
							<ol id="sortableTitles">
								<?php
								$columns = explode(',', $annex_titles['columns']);
								foreach ($columns as $column) { ?>
									<li data-column="<?php echo $column; ?>"><?php echo $productsColumns[$column]; ?></li>
								<?php } ?>
							</ol>
							To rearrange product columns click on <i class="fas fa-expand-arrows-alt" style="font-size: 12px !important;"></i> and drug the column to the desired position.
						</div>
				</tr>

				<?php
				$template['php'] = '{
    "images": {
        "main_signature": {
            "title": "Insert main page signature"
        },
        "main_stempel": {
            "title": "Insert main page stempel"
        },
		"main_halal_stempel": {
			"title": "Insert main page halal logo"
		},
		"main_eiaci": {
			"title": "Insert main page EIACI Logo"
		},
        "annex_signature": {
            "title": "Insert annex signature"
        },
        "annex_stempel": {
            "title": "Insert annex page stempel"
        },
		"annex_halal_stempel": {
			"title": "Insert annex page halal logo"
		},
		"annex_eiaci": {
			"title": "Insert annex page EIACI Logo"
		}
    },
    "digital-print": "yes",
    "font_sizes": "yes"
}';
				$inputs['options'] = '';
				$inputs['main'] = '';
				if (isset($template)) {
					if (trim($template['php']) != '' && is_array(json_decode($template['php'], true))) {
						$php = json_decode($template['php'], true);
						if (isset($php['images'])) {
							$images = $php['images'];
							foreach ($images as $key => $image) {
								$imageChecked = '';
								if (isset($certificate_options['image'])) {
									$optionImages = $certificate_options['image'];
									if (in_array($key, $optionImages))
										$imageChecked = 'checked';
								}
								$inputs['options'] .= '<li><label><input type="checkbox" name="certificate_options[image][]" value="' . $key . '" ' . $imageChecked . '/> ' . $image['title'] . '</label></li>';
							};
						};
						if (isset($php['annex-images'])) {
							$images = $php['annex-images'];
							foreach ($images as $key => $image) {
								$imageChecked = '';
								if (isset($certificate_options['annex-image'])) {
									$optionImages = $certificate_options['annex-image'];
									if (in_array($key, $optionImages))
										$imageChecked = 'checked';
								}
								$inputs['options'] .= '<li><label><input type="checkbox" name="certificate_options[annex-image][]" value="' . $key . '" ' . $imageChecked . '/> ' . $image['title'] . '</label></li>';
							};
						}
					};
					$annexNormal = '';
					$annexPageOnly = '';
					$annexSepareted = '';
					if (strstr($template['content'], 'annexPage')) {
						if (isset($certificate_options['annexPages'])) {
							if ($certificate_options['annexPages'] == 'annexPageOnly')
								$annexPageOnly = 'checked';
							elseif ($certificate_options['annexPages'] == 'annexSepareted')
								$annexSepareted = 'checked';
							else
								$annexNormal = 'checked';
						} else {
							$annexNormal = 'checked';
						}
						if ($annexSepareted == '')
							$annexSeparetedFirstPage = 'normal';
						elseif (isset($certificate_options['annexSeparetedFirstPage']))
							$annexSeparetedFirstPage = $certificate_options['annexSeparetedFirstPage'];
						else
							$annexSeparetedFirstPage = 'normal';

						if (isset($certificate_options['auto_certificate_number']))
							$auto_certificate_number = 'yes';

						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'none\')"><input type="radio" name="certificate_options[annexPages]" value="normal" ' . $annexNormal . '/> Normal print</label></li>';
						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'none\')"><input type="radio" name="certificate_options[annexPages]" value="annexPageOnly" ' . $annexPageOnly . ' /> Print annex page only</label></li>';
						$inputs['options'] .= '<li><label onclick="checkAnnexSepareted(\'block\')"><input type="radio" name="certificate_options[annexPages]" value="annexSepareted" ' . $annexSepareted . ' /> Print annex on separate pages</label>
			<ul style="display:' . ($annexSepareted != '' ? 'block' : 'none') . '" id="annexSepareted">
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="normal" ' . ($annexSeparetedFirstPage == 'normal' ? 'checked' : '') . '/> Normal print without the first page</label></li>
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="major" ' . ($annexSeparetedFirstPage == 'major' ? 'checked' : '') . '/> Annex pages with one major first page</label></li>
			<li><label><input type="radio" name="certificate_options[annexSeparetedFirstPage]" value="preceded" ' . ($annexSeparetedFirstPage == 'preceded' ? 'checked' : '') . '/> Each annex page preceded by the first page</label></li>';
						$inputs['options'] .= '<li style="background: beige; color: green;"><label><input type="checkbox" name="certificate_options[auto_certificate_number]" value="yes" ' . (isset($auto_certificate_number) ? 'checked' : '') . '/> Auto certificate sub-number.</label> (Ex. NL1051050005.1, NL1051050005.2)</li>';
						$inputs['options'] .= '</ul></li>';
					}
				};
				//get inserted in template inputs
				if ($contentOptions = parse_shortcode('input', $template['content'])) {
					$elements = array(
						'checkbox' => '<label><input type="[type]" name="certificate_options[[name]]" id="certificate_option_[name]" [props] />[title]</label>',
						'text' => '<input type="[type]" name="certificate_options[[name]]" id="certificate_option_[name]" [value] [props]/>',
						'textarea' => '<textarea name="certificate_options[[name]]" id="certificate_option_[name]" class="certificate_option" [props]>[value]</textarea>'
					);
					if (count($contentOptions) > 0) {
						foreach ($contentOptions as $contentOption) {
							$elementName = $contentOption['name'];
							$element = str_replace(
								array('[type]', '[name]', '[title]'),
								array($contentOption['type'], $elementName, isset($contentOption['title']) ? $contentOption['title'] : ''),
								$elements[$contentOption['type']]
							);
							$props = '';
							foreach ($contentOption['props'] as $key => $value) {
								if (!strstr($element, '[' . $key . ']'))
									$props .= $key . '="' . $value . '" ';
							};
							if (isset($certificate_options[$elementName])) {
								if ($contentOption['type'] == 'checkbox')
									$props .= 'checked="checked"';
								else
									$element = str_replace('[value]', $certificate_options[$elementName], $element);
							}
							$element = str_replace(array('[props]', '[value]'), array($props, ''), $element);
							if (isset($contentOption['group']) and $contentOption['group'] == 'options') {
								$inputs['options'] .= '<li>' . $element . '</li>';
							} else {
								$inputs['main'] .= '<tr><th>' . (isset($contentOption['title']) ? $contentOption['title'] : '') . ':</th><td colspan="3">' . $element . '</td></tr>';
							}
						}
					}
				}
				if (trim($inputs['main']) != '') {
					echo $inputs['main'];
				}
				?>
				<tr>
					<th>Certificate options:</th>
					<td colspan="3" id="annexOptions">
						<b>Sort products on annex page: </b>
						<select name="product_sort_by" size="1">
							<option value="">No sorting</option>
							<option value="product_name" <?php echo ($act == 'edit' && $row['product_sort_by'] == "product_name") ? 'selected="selected"' : ''; ?>>Sort by product name</option>
							<option value="article_nr" <?php echo ($act == 'edit' && $row['product_sort_by'] == "article_nr") ? 'selected="selected"' : ''; ?>>Sort by article number</option>
						</select>
						<?php
						if (trim($inputs['options']) != '') { ?>
							<ul style="margin:0px;padding:0px;width:100%"><?php echo $inputs['options'] ?></ul>
						<?php }; ?>
					</td>
				</tr>
				<tr>
					<th>Certificate File Name:</th>
					<td colspan="3">
						<?php
						if (isset($certificate_options['certificate_file_name']))
							$CRTFileName = $certificate_options['certificate_file_name'];
						else
							$CRTFileName = '';
						?>
						<select name="certificate_options[certificate_file_name]">
							<option value="certificate_number">Certificate number</option>
							<option value="company_name_crt_number" <?php echo ($CRTFileName == 'company_name_crt_number') ? 'selected' : ''; ?>>Company name & Certificate number</option>
							<option value="company_name" <?php echo ($CRTFileName == 'company_name') ? 'selected' : ''; ?>>Company name</option>
							<option value="product_name" <?php echo ($CRTFileName == 'product_name') ? 'selected' : ''; ?>>Product name</option>
							<option value="article_nr" <?php echo ($CRTFileName == 'article_nr') ? 'selected' : ''; ?>>Article code</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Certificate language:</th>
					<td>
						<select name="certificate_options[language]" id="certificate_language">
							<option value="en" <?php echo (isset($certificate_options['language']) && $certificate_options['language'] == 'en') ? 'selected' : ''; ?>>English</option>
							<option value="de" <?php echo (isset($certificate_options['language']) && $certificate_options['language'] == 'de') ? 'selected' : ''; ?>>German</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>Annex number & Revision:</th>
					<td colspan="3">
						<input name="revision[insert]" value="yes" type="checkbox" <?php echo isset($revision['insert']) ? 'checked' : ''; ?> onclick="autoAnnexNumber(this)">Insert: </label> <b>Annex number:</b> <input type="number" min="1" name="certificate_option[annex_number]" style="width:60px" value="<?php echo isset($certificate_options['annex_number']) ? $certificate_options['annex_number'] : '1'; ?>" />
						<label style="padding: 6px 10px;background:lightblue;display:" <?php echo isset($certificate_options['annex_number']) ? 'none' : 'none'; ?>" id="auto_annex_number"><input type="checkbox" name="certificate_option[auto_annex_number]" <?php echo (isset($certificate_options['auto_annex_number'])) ? 'checked' : ''; ?> /> Auto-number</label>
						<b style="width:auto">Revision number:</b> <input type="text" name="revision[number]" style="width:40px" value="<?php echo isset($revision['number']) ? $revision['number'] : '1.0'; ?>" />
						<b>Revision date:</b> <input type="text" class="date1" name="revision[date]" value="<?php echo isset($revision['date']) ? $revision['date'] : date("d.m.Y"); ?>" />
					</td>
				</tr>
				<?php if (isset($dmc)) { ?>
				<tr>
					<td colspan="5" style="text-align:center; padding: 12px 20px;">
						<div style="color: #dc2626; font-weight: 700; font-size: 14px; text-transform: uppercase; margin-bottom: 8px;">
							TO ISSUE OR RE-ISSUE A CERTIFICATE, A VALID DMC REPORT IS REQUIRED.
						</div>
						<!--
						<div style="color: #991b1b; font-size: 13px;">
							Note: A password is required to generate the report. You can find the password in your account profile.<br/>
							Click on new window on the Menubar and under DMC on my account
						</div>
-->
					</td>
				</tr>
				<?php } ?>
				<tr>
    <td colspan="5">
	  <div class="annual-cert-form-footer">
        <?php /* if (isset($dmc)) { ?>
            <button type="button" class="btn-cert-action digital" onclick="crtDoAct('digital')">
                <i class="fas fa-mouse-pointer"></i>
                Digital
            </button>
        <?php } */ ?>
        
        <button type="reset" class="btn-cert-action reset">
            <i class="fas fa-undo"></i>
            Reset
        </button>
        
        <?php
        $request = ($user_type == 'admin') ? 'Save' : 'Request';
        $buttonText = ($_GET['act'] == "edit") ? "Update" : ($_GET['act'] == "reissue" ? "Reissue" : $request);
        $buttonIcon = ($_GET['act'] == "edit") ? "fa-save" : ($_GET['act'] == "reissue" ? "fa-redo" : "fa-paper-plane");
        ?>
        
        <button type="button" class="btn-cert-action save-draft" id="saveDraftBtn" onclick="saveDraft()">
            <i class="fas fa-save"></i>
            Save
        </button>

        <!--
        <button type="button" class="btn-cert-action primary" id="addUpdateReissue" onclick="crtDoAct('save')">
            <i class="fas <?php echo $buttonIcon; ?>"></i>
            <?php echo $buttonText; ?>
        </button>
		-->
        
        <button type="button" class="btn-cert-action secondary" onclick="crtDoAct('preview')">
            <i class="fas fa-eye"></i>
            Preview
        </button>
        
        <?php if ($_SESSION['user_type'] == "admin" || isset($dmc)) { ?>
            <?php if ($user_type == "admin" && $_GET['offid'] != '0' && !isset($dmc)) { ?>
                <button type="button" class="btn-cert-action authorize" onclick="crtDoAct('authorize')"
                    <?php if (!$dmcConducted) { ?>
                    disabled title="DMC meeting must be conducted before authorizing" style="opacity:0.5; cursor:not-allowed;"
                    <?php } ?>>
                    <i class="fas fa-check-double"></i>
                    Authorize
                </button>
            <?php } ?>
            
            <?php if (isset($dmc)) { ?>
            <button type="button" class="btn-cert-action print" id="printActionButton" onclick="openDMCReportForm()">
                <i class="fas fa-file-alt"></i>
                Create DMC Report
            </button>
            <?php } else { ?>
            <button type="button" class="btn-cert-action print" id="printActionButton" onclick="crtDoAct('print')"
                <?php if (!$dmcConducted) { ?>
                disabled title="DMC meeting must be conducted before printing" style="opacity:0.5; cursor:not-allowed;"
                <?php } ?>>
                <i class="fas fa-print"></i>
                Print
            </button>
            <?php } ?>
            
            <span id="DownLoadZip" style="display:none;">
                <label>
                    <input type="checkbox" name="downLoadZipFile" id="downLoadZipFile" value="yes">
                    Download individual certificates
                </label>
            </span>
        <?php } ?>
			</div>
    </td>
</tr>
<?php }; ?> 
	   </table>
</div>

		<?php if (isset($dmc)) { ?>
			<?php
			$dmcFormUrl = '/iidc/committee/dmc/?crtNr=' . intval($_GET['crtNr']) 
				. '&clid=' . intval($_GET['clid']) 
				. '&offid=' . intval($_GET['offid']) 
				. '&decid=' . intval($dmc)
				. '&ref=cert';
			?>
			<input type="hidden" id="DMCUrl" data-href="<?php echo $dmcFormUrl; ?>" title="Create DMC Report" data-resize="true" data-width="1080" data-height="700" onclick="doIframe(this)"></input>
		<?php } elseif ($act == 'add') { ?>
			<input type="hidden" id="DMCUrl" data-href="" title="Create DMC Report" data-resize="true" onclick="doIframe(this)"></input>
		<?php }; ?>
	</form>

	<?php if (isset($_GET['clid']) and $_SESSION['user_type'] == "admin") { ?>
		Please make a preview before you print the certificate.
	<?php } else { ?>
		<div id="productsInfo" style="color:red;margin-top:20px">Before you request a certificate make sure to add some products to the company.</div>
	<?php }; ?>
</center>

<!--End add/edit halal_certificates-->
<template id="remarksStyle">
	<label><input type="checkbox" name="[name][bold]" value="strong" class='remarksBold' /><strong>&nbsp;&nbsp;Font-weight Bold</strong></label>
	<label><input type="checkbox" name="[name][italic]" value="italic" class="remarkItalic" /><i>&nbsp;&nbsp;Font-style Italic</i></label>
	Text color:
	<label class="colorLabel" style="background:black"><input type="radio" name="[name][color]" value="black" class="colorPicked" checked /></label>
	<label class="colorLabel" style="background:red"><input type="radio" name="[name][color]" value="red" class="colorPicked" /></label>
	<label class="colorLabel" style="background:blue"><input type="radio" name="[name][color]" value="blue" class="colorPicked" /></label>
	<label class="colorLabel" style="background:green"><input type="radio" name="[name][color]" value="green" class="colorPicked" /></label>
</template>

<script src="/iidc/scripts/color-picker/jqColorPicker.min.js"></script>
<script>
	var remarksStyle = {};
	var JAKIM = '<?php echo isset($options['print_jakim']) ? $options['print_jakim'] : 'no'; ?>';
	<?php
	if (isset($certificate_options['lastPageRemarksStyle'])) {
		echo 'remarksStyle["lastPageRemarksStyle"] = ' . json_encode($certificate_options['lastPageRemarksStyle']) . ';' . "\n";
	}
	if (isset($certificate_options['remarksStyle'])) {
		echo 'remarksStyle["remarksStyle"] = ' . json_encode($certificate_options['remarksStyle']) . ';' . "\n";
	}
	?>
	var offid = <?php echo isset($_SESSION['offid']) ? $_SESSION['offid'] : 0; ?>;
	$(function() {
		$("#sortableTitles").sortable({
			stop: function(event, ui) {
				cols = []
				jQuery("#sortableTitles li").each(function(index, element) {
					cols.push(jQuery(this).data('column'));
				});
				jQuery("#annex_options_columns").val(cols.join(','));
			}
		});
		$("#sortableTitles").disableSelection();
	});

	function checkMiic(obj) {
		if (jQuery(obj).is(":checked")) {
			jQuery("#office_address").val(0);
			jQuery("#office_address_tr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});
		} else {
			jQuery("#office_address_tr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});

		}

	}

	function halalStandardsCheck() {
		var checkedTot = 0,
			OIC = 0;

		jQuery("#halalStandards input[type=checkbox]").each(function() {
			if (jQuery(this).is(":checked")) {
				sta = jQuery(this).data('standard').split(' ')[0];
				if (sta.toUpperCase() == 'MS') {
					checkedTot++;
				}

				if (sta.toUpperCase() == 'OIC/SMIIC') {
					OIC++;
				}
			}
		})
		if (OIC > 0) {
			//TODO: update this
			jQuery("#insertHAKLogo").css("display", "none");
		} else {
			jQuery("#insertHAKLogo").css("display", "none");
		}

		if (checkedTot > 0 && userType == 'hqc_office') {
			//check if office_address has a attr data-offid
			if (jQuery("#office_address").data('offid') == undefined) {
				jQuery("#office_address").data('offid', jQuery("#office_address").val());
			}

			jQuery("#office_address").val(0);
			jQuery("#office_address_tr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});

			//unselect all signatories_main_director
			jQuery("#signatories_main_director option").each(function() {
				jQuery(this).removeAttr("selected");
			})

			//select first index in signatories_main_director using javascript
			document.getElementById('signatories_main_director').selectedIndex = 0;
			//disable signatories_main_director
			jQuery("#ApprovalTr").css({
				"visibility": "hidden",
				"position": "fixed",
				"left": "-9000px"
			});
			//check checkbox value = main_signature
			jQuery("#annexOptions input[type=checkbox]").each(function() {

				if (jQuery(this).val() == 'main_signature' || jQuery(this).val() == 'main_stempel' || jQuery(this).val() == 'annex_signature' || jQuery(this).val() == 'annex_stempel') {
					jQuery(this).attr("checked", true);
				}
			})
		} else {
			jQuery("#office_address_tr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});
			jQuery("#ApprovalTr").css({
				"visibility": "visible",
				"position": "relative",
				"left": "0px"
			});
			jQuery("#printActionButton").css("display", "");
			if (jQuery("#office_address").data('offid') != undefined) {
				jQuery("#office_address").val(jQuery("#office_address").data('offid'));
			}
		}
	}


	jQuery(document).ready(function() {
		jQuery(".certificate_option").each(function() {
			name = jQuery(this).attr('name');
			className = name.replace('certificate_options[', '').replace(']', '');
			replaceWith = name.replace('remarks', 'remarksStyle').replace('lastPageRemarks', 'lastPageRemarksStyle');
			template = jQuery("#remarksStyle").html();
			if (name == 'certificate_options[remarks]' || name == 'certificate_options[lastPageRemarks]') {
				jQuery(this).after('<div class="' + className + 'Style" style="padding:5px">' + template.replace(/\[name\]/g, replaceWith) + '</div>');
			}
			jQuery(".colorPicked").on("click", function() {
				jQuery(this).parents('td').find(".certificate_option").css("color", jQuery(this).val());
			})
			jQuery(".remarksBold").on("click", function() {
				if (jQuery(this).is(":checked")) {
					jQuery(this).parents('td').find(".certificate_option").css("font-weight", "bold");
				} else {
					jQuery(this).parents('td').find(".certificate_option").css("font-weight", "normal");
				}
			})
			jQuery(".remarkItalic").on("click", function() {
				if (jQuery(this).is(":checked")) {
					jQuery(this).parents('td').find(".certificate_option").css("font-style", "italic");
				} else {
					jQuery(this).parents('td').find(".certificate_option").css("font-style", "normal");
				}
			})
		})

		if (remarksStyle.remarksStyle != undefined) {
			remarksStyles = remarksStyle.remarksStyle;
			remarkClass = 'div.remarksStyle';
			if (remarksStyles.color != undefined) {
				jQuery(remarkClass).find('.colorPicked[value=' + remarksStyles.color + ']').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("color", remarksStyles.color);
			}
			if (remarksStyles.bold != undefined) {
				jQuery(remarkClass).find('.remarksBold').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-weight", "bold");
			}
			if (remarksStyles.italic != undefined) {
				jQuery(remarkClass).find('.remarkItalic').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-style", "italic");
			}
		}

		if (remarksStyle.lastPageRemarksStyle != undefined) {
			lastPageRemarksStyle = remarksStyle.lastPageRemarksStyle;
			remarkClass = 'div.lastPageRemarksStyle';
			if (remarksStyles.color != undefined) {
				jQuery(remarkClass).find('.colorPicked[value=' + lastPageRemarksStyle.color + ']').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("color", lastPageRemarksStyle.color);
			}
			if (lastPageRemarksStyle.bold != undefined) {
				jQuery(remarkClass).find('.remarksBold').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-weight", "bold");
			}
			if (lastPageRemarksStyle.italic != undefined) {
				jQuery(remarkClass).find('.remarkItalic').prop('checked', true);
				jQuery(remarkClass).parent().find('textarea').css("font-style", "italic");
			}
		}

		//check if #annexSepareted is visible
		if (jQuery("#annexSepareted").css("display") == "block") {
			jQuery("#DownLoadZip").css("display", "inline-block");
		}

		jQuery(".reference_standards").on("click", function() {
			//halalStandards HQCScheme

			parentStandard = jQuery(this).parents('ul').prop('id') == 'HQCScheme' ? '#halalStandards' : '#HQCScheme';

			// jQuery(parentStandard + " input[type=checkbox]").each(function() {
			// 	if (jQuery(this).is(":checked")) {
			// 		jQuery(this).removeAttr("checked");
			// 	}
			// })
			//	halalStandardsCheck(this);
		})
		//halalStandardsCheck(this);
		if (jQuery("#productsOl").length > 0) {
			jQuery("#productsInfo").css("display", "none");
		}
		jQuery("#addUpdateReissue").attr("data-value", jQuery("#addUpdateReissue").val());
		jQuery("#certificate_option_reprint").on("click", function() {
			if (jQuery(this).is(":checked")) {
				jQuery("#addUpdateReissue").val('Authorize Client');
			} else {
				jQuery("#addUpdateReissue").val(jQuery("#addUpdateReissue").data('value'));
			}
		})


		// Handle main category checkbox - select/deselect all sub-categories
$('.main-cat-checkbox').click(function(event) {
    var parentCat = $(this).data('parent');
    var isChecked = $(this).is(':checked');
    
    // Select/deselect all sub-categories under this main category
    $('.sub-category-checkbox[data-parent="' + parentCat + '"]').prop('checked', isChecked);
    
    // Check total selected categories
    checkCategoryLimit();
});

// Handle sub-category checkbox
$('.sub-category-checkbox').click(function(event) {
    checkCategoryLimit();
    
    // Optional: Check parent if at least one child is selected
    var parentCat = $(this).data('parent');
    var anyChecked = $('.sub-category-checkbox[data-parent="' + parentCat + '"]:checked').length > 0;
    $('.main-cat-checkbox[data-parent="' + parentCat + '"]').prop('checked', anyChecked);
});

// Function to check category selection limit
function checkCategoryLimit() {
    var totalChecked = $('.main-cat-checkbox:checked').length;
    
    jQuery("#categoriesCount").removeClass("red");
    if (totalChecked > 3) {
        jQuery("#categoriesCount").addClass("red");
        alert_message('Maximum 3 categories');
        return false;
    }
    return true;
}

// Update the existing .category click handler - remove old one and use new logic
$('.category').off('click').on('click', function(event) {
    if ($(this).hasClass('main-cat-checkbox')) {
        // Handled by main-cat-checkbox handler
        return;
    }
    jQuery("#categoriesCount").removeClass("red");
    if ($(".main-cat-checkbox:checked").length > 3) {
        $(this).prop("checked", false);
        jQuery("#categoriesCount").addClass("red");
        alert_message('Maximum 3 categories');
    }
});

	})

	async function removeProduct(prdid) {
		if (prdid == '*') {
			await confirm_message("Are you sure you want to remove all product from the list?");
			jQuery("#productsOl .double").remove();
		} else {
			await confirm_message("Are you sure you want to remove this product?");
			jQuery("#product_" + prdid).remove();
		}
	}

</script>