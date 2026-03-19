/**
 * SFDA Applications jqGrid Module
 * Grid-based interface with modal forms and file uploads
 */

var sfdaGrid;
var currentApplicationId = null;
var sfdaFilesUploaded = [];

// Upload fields configuration
var uploadFields = [
    'commercial_registration_certificate',
    'upload_product_information',
    'invoice',
    'proof_of_payment',
    'sfda_facility_certificate'
];

$(document).ready(function() {
    initSfdaGrid();
    bindEventHandlers();
    
    // Setup initial client selection for client users
    var config = window.sfdaConfig || {};
    if (config.isClient && config.defaultClient) {
        $('#sfda-clientid').val(config.defaultClient);
        loadSfdaGrid();
    }
});

// Initialize jqGrid
function initSfdaGrid() {
    var gridHeight = $(window).height() - 250;
    
    $("#sfdaGrid").jqGrid({
        url: "ajax/getSfdaApplications.php",
        postData: {
            action: 'getGridData',
            idclient: function() { return $('#sfda-clientid').val() || ''; }
        },
        datatype: "json",
        mtype: "POST",
        width: $("#sfdaGridContainer").width(),
        height: gridHeight,
        colModel: [
            { name: "id", index: "id", hidden: true, key: true },
            { name: "application_name", index: "application_name", label: "Application Name", width: 150, align: "left" },
            { name: "company_name", index: "company_name", label: "Company Name", width: 150, align: "left" },
            { name: "address", index: "address", label: "Address", width: 120, align: "left" },
            { 
                name: "commercial_registration_certificate", 
                index: "commercial_registration_certificate", 
                label: "Commercial Reg. Cert.", 
                width: 120,
                formatter: formatFileUploadColumn,
                cellattr: function (rowId, val, rawObject) {
                    return 'class="upload-area" data-field="commercial_registration_certificate"';
                }
            },
            { name: "commercial_registration_no", index: "commercial_registration_no", label: "Commercial Reg. No.", width: 120, align: "left" },
            { name: "vat_number", index: "vat_number", label: "VAT Number", width: 100, align: "left" },
            { name: "accreditation_certificates", index: "accreditation_certificates", label: "Accreditation Cert.", width: 120, align: "left" },
            { name: "number_of_production_lines", index: "number_of_production_lines", label: "Production Lines", width: 80, align: "center" },
            { name: "number_of_critical_points", index: "number_of_critical_points", label: "Critical Points", width: 80, align: "center" },
            { name: "number_of_full_time_employees", index: "number_of_full_time_employees", label: "Full Time Emp.", width: 80, align: "center" },
            { name: "number_of_shifts", index: "number_of_shifts", label: "Shifts", width: 60, align: "center" },
            { name: "number_of_shift_employees", index: "number_of_shift_employees", label: "Shift Emp.", width: 80, align: "center" },
            { name: "production_area_space_m2", index: "production_area_space_m2", label: "Area (m2)", width: 80, align: "center" },
            { name: "additional_branches_of_the_company", index: "additional_branches_of_the_company", label: "Additional Branches", width: 120, align: "left" },
            { 
                name: "upload_product_information", 
                index: "upload_product_information", 
                label: "Product Info", 
                width: 100,
                formatter: formatFileUploadColumn,
                cellattr: function (rowId, val, rawObject) {
                    return 'class="upload-area" data-field="upload_product_information"';
                }
            },
            { name: "validity_of_certificate_period", index: "validity_of_certificate_period", label: "Validity Period", width: 100, align: "left" },
            { 
                name: "invoice", 
                index: "invoice", 
                label: "Invoice", 
                width: 80,
                formatter: formatFileUploadColumn,
                cellattr: function (rowId, val, rawObject) {
                    return 'class="upload-area admin-only" data-field="invoice"';
                },
                hidden: !window.sfdaConfig.isAdmin
            },
            { 
                name: "proof_of_payment", 
                index: "proof_of_payment", 
                label: "Proof of Payment", 
                width: 100,
                formatter: formatFileUploadColumn,
                cellattr: function (rowId, val, rawObject) {
                    return 'class="upload-area" data-field="proof_of_payment"';
                }
            },
            { 
                name: "sfda_facility_certificate", 
                index: "sfda_facility_certificate", 
                label: "SFDA Facility Cert.", 
                width: 120,
                formatter: formatFileUploadColumn,
                cellattr: function (rowId, val, rawObject) {
                    return 'class="upload-area auditor-only" data-field="sfda_facility_certificate"';
                },
                hidden: !window.sfdaConfig.isAuditor
            },
            { name: "status", index: "status", label: "Status", width: 80, align: "center" },
            { name: "created_at", index: "created_at", label: "Created", width: 100, align: "left" }
        ],
        pager: "#sfdaGridPager",
        rowNum: 20,
        rowList: [10, 20, 30, 50],
        sortname: "created_at",
        sortorder: "desc",
        viewrecords: true,
        altRows: true,
        shrinkToFit: false,
        autowidth: false,
        gridComplete: function() {
            // Setup file upload handlers for grid cells
            setupGridFileUploads();
        },
        ondblClickRow: function(rowid) {
            editSfdaApplication();
        },
        loadError: function(xhr, status, error) {
            console.error("Grid load error:", error);
        }
    });
    
    // Grid navigation
    $("#sfdaGrid").jqGrid('navGrid', '#sfdaGridPager', {
        edit: false,
        add: false,
        del: false,
        search: false,
        refresh: true
    });
    
    // Resize grid on window resize
    $(window).resize(function() {
        $("#sfdaGrid").jqGrid('setGridWidth', $("#sfdaGridContainer").width());
    });
}

// Format file upload columns
function formatFileUploadColumn(cellvalue, options, rowObject) {
    if (!cellvalue) {
        return '<span class="no-files">No files</span>';
    }
    
    try {
        var files = JSON.parse(cellvalue);
        if (files && files.length > 0) {
            return '<span class="file-count">' + files.length + ' file(s)</span>';
        }
    } catch (e) {
        // If not JSON, assume it's a simple count or text
        return '<span class="file-info">' + cellvalue + '</span>';
    }
    
    return '<span class="no-files">No files</span>';
}

// Setup file upload handlers for grid cells
function setupGridFileUploads() {
    $('.upload-area').off('click').on('click', function(e) {
        e.stopPropagation();
        
        var rowId = $(this).closest('tr').attr('id');
        var field = $(this).data('field');
        
        if (!rowId || !field) {
            return;
        }
        
        // Check permissions
        if (field === 'invoice' && !window.sfdaConfig.isAdmin) {
            alert('Only administrators can upload invoices');
            return;
        }
        
        if (field === 'sfda_facility_certificate' && !window.sfdaConfig.isAuditor) {
            alert('Only auditors can upload SFDA facility certificates');
            return;
        }
        
        currentApplicationId = rowId;
        showFileUploadModal(field, rowId);
    });
}

// Show file upload modal
function showFileUploadModal(field, applicationId) {
    var fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    $('#uploadModalTitle').text('Upload ' + fieldLabel);
    
    // Setup file upload
    $('#fileupload').fileupload({
        url: 'fileupload/ProcessFiles.php',
        dataType: 'json',
        formData: {
            infoType: 'sfda_application',
            client: $('#sfda-clientid').val() || window.sfdaConfig.defaultClient,
            application: applicationId,
            field: field
        },
        done: function (e, data) {
            $('#uploadModal').modal('hide');
            
            if (data.result.status === 'success') {
                // Refresh the grid to show updated file count
                $("#sfdaGrid").trigger('reloadGrid');
                alert('File uploaded successfully');
            } else {
                alert('Upload failed: ' + (data.result.message || 'Unknown error'));
            }
        },
        fail: function (e, data) {
            alert('Upload failed');
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('.upload-progress').show();
            $('.progress-bar').css('width', progress + '%');
        }
    });
    
    $('#uploadModal').modal('show');
}

// Bind event handlers
function bindEventHandlers() {
    // Client dropdown change
    $('#sfda-clientid').on('change', function() {
        loadSfdaGrid();
    });
    
    // Add application button
    $('#btnNewSfdaApplication').on('click', function() {
        newSfdaApplication();
    });
    
    // Edit application button
    $('#btnEditSfdaApplication').on('click', function() {
        editSfdaApplication();
    });
    
    // Delete application button
    $('#btnDeleteSfdaApplication').on('click', function() {
        deleteSfdaApplication();
    });
    
    // Refresh grid button
    $('#btnRefreshSfdaGrid').on('click', function() {
        $("#sfdaGrid").trigger('reloadGrid');
    });
    
    // Save application button
    $('#btnSaveSfdaApplication').on('click', function() {
        saveSfdaApplication();
    });
    
    // Modal form handlers
    $('#sfdaModal').on('hidden.bs.modal', function() {
        clearSfdaForm();
    });
    
    // Accreditation "Other" checkbox
    $('#accreditation_other').on('change', function() {
        if ($(this).is(':checked')) {
            $('#accreditation_other_text').slideDown();
        } else {
            $('#accreditation_other_text').slideUp();
            $('#accreditation_certificates_other').val('');
        }
    });
    
    // Upload button handlers in modal
    $('.upload-btn').on('click', function() {
        var field = $(this).data('field');
        if (currentApplicationId) {
            showFileUploadModal(field, currentApplicationId);
        } else {
            alert('Please save the application first before uploading files');
        }
    });
    
    // Upload modal cleanup
    $('#uploadModal').on('hidden.bs.modal', function() {
        $('.upload-progress').hide();
        $('.progress-bar').css('width', '0%');
        $('#fileupload').val('');
    });
}

// Load grid data
function loadSfdaGrid() {
    $("#sfdaGrid").jqGrid('setGridParam', {
        postData: {
            action: 'getGridData',
            idclient: $('#sfda-clientid').val() || ''
        }
    }).trigger('reloadGrid');
}

// New SFDA Application
function newSfdaApplication() {
    var clientId = $('#sfda-clientid').val() || window.sfdaConfig.defaultClient;
    
    if (!clientId) {
        alert('Please select a client first');
        return;
    }
    
    clearSfdaForm();
    currentApplicationId = null;
    
    $('#sfdaModal-label').text('Add SFDA Application');
    $('#sfda-data-new').val('1');
    $('#sfda-clientid-hidden').val(clientId);
    
    // Hide upload buttons for new applications
    $('.upload-btn').hide();
    
    $('#sfdaModal').modal('show');
}

// Edit SFDA Application
function editSfdaApplication() {
    var rowId = $("#sfdaGrid").jqGrid('getGridParam', 'selrow');
    
    if (!rowId) {
        alert('Please select an application to edit');
        return;
    }
    
    currentApplicationId = rowId;
    
    // Get row data
    var rowData = $("#sfdaGrid").jqGrid('getRowData', rowId);
    
    clearSfdaForm();
    populateSfdaForm(rowData);
    
    $('#sfdaModal-label').text('Edit SFDA Application');
    $('#sfda-data-new').val('0');
    $('#sfda-id').val(rowId);
    
    // Show upload buttons for existing applications
    $('.upload-btn').show();
    
    // Load file lists for upload fields
    loadFileLists(rowId);
    
    $('#sfdaModal').modal('show');
}

// Populate form with data
function populateSfdaForm(data) {
    $('#application_name').val(data.application_name || '');
    $('#company_name').val(data.company_name || '');
    $('#address').val(data.address || '');
    $('#commercial_registration_no').val(data.commercial_registration_no || '');
    $('#vat_number').val(data.vat_number || '');
    $('#number_of_production_lines').val(data.number_of_production_lines || '');
    $('#number_of_critical_points').val(data.number_of_critical_points || '');
    $('#number_of_full_time_employees').val(data.number_of_full_time_employees || '');
    $('#number_of_shifts').val(data.number_of_shifts || '');
    $('#number_of_shift_employees').val(data.number_of_shift_employees || '');
    $('#production_area_space_m2').val(data.production_area_space_m2 || '');
    $('#additional_branches_of_the_company').val(data.additional_branches_of_the_company || '');
    $('#validity_of_certificate_period').val(data.validity_of_certificate_period || '');
    
    // Handle checkboxes for accreditation certificates
    $('input[name="accreditation_certificates[]"]').prop('checked', false);
    if (data.accreditation_certificates) {
        var certificates = data.accreditation_certificates.split(', ');
        certificates.forEach(function(cert) {
            $('input[name="accreditation_certificates[]"][value="' + cert + '"]').prop('checked', true);
        });
        
        if (certificates.includes('Other')) {
            $('#accreditation_other_text').show();
        }
    }
    
    // Handle "Other" text field (this would need to be loaded via AJAX)
    // For now, we'll leave it empty as it requires separate data loading
}

// Load file lists for upload fields
function loadFileLists(applicationId) {
    uploadFields.forEach(function(field) {
        $.ajax({
            url: 'ajax/getDocs.php',
            method: 'POST',
            data: {
                action: 'getFilesList',
                idapp: applicationId,
                category: 'sfda_' + field
            },
            success: function(response) {
                var files = response.data || [];
                var listId = '#ul_' + field;
                $(listId).empty();
                
                files.forEach(function(file) {
                    $(listId).append(
                        '<li class="uploaded-file">' +
                        '<span class="file-name">' + file.filename + '</span> ' +
                        '<button type="button" class="btn btn-xs btn-danger remove-file" data-file-id="' + file.id + '">' +
                        '<i class="fa fa-times"></i></button>' +
                        '</li>'
                    );
                });
            }
        });
    });
}

// Save SFDA Application
function saveSfdaApplication() {
    if (!validateSfdaForm()) {
        return;
    }
    
    var formData = $('#sfda-form').serialize();
    formData += '&action=' + ($('#sfda-data-new').val() === '1' ? 'addApplication' : 'updateApplication');
    
    $.ajax({
        url: 'ajax/getSfdaApplications.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#sfdaModal').modal('hide');
                $("#sfdaGrid").trigger('reloadGrid');
                
                // Set currentApplicationId for new applications
                if ($('#sfda-data-new').val() === '1' && response.data && response.data.id) {
                    currentApplicationId = response.data.id;
                }
                
                alert('Application saved successfully');
            } else {
                $('#sfda-errors').show().text('Error: ' + response.message);
            }
        },
        error: function() {
            $('#sfda-errors').show().text('Error saving application');
        }
    });
}

// Delete SFDA Application
function deleteSfdaApplication() {
    var rowId = $("#sfdaGrid").jqGrid('getGridParam', 'selrow');
    
    if (!rowId) {
        alert('Please select an application to delete');
        return;
    }
    
    if (!confirm('Are you sure you want to delete this application?')) {
        return;
    }
    
    $.ajax({
        url: 'ajax/getSfdaApplications.php',
        method: 'POST',
        data: {
            action: 'deleteApplication',
            id: rowId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $("#sfdaGrid").trigger('reloadGrid');
                alert('Application deleted successfully');
            } else {
                alert('Error deleting application: ' + response.message);
            }
        },
        error: function() {
            alert('Error deleting application');
        }
    });
}

// Validate form
function validateSfdaForm() {
    var isValid = true;
    
    if (!$('#application_name').val().trim()) {
        $('#sfda-errors').show().text('Application name is required');
        return false;
    }
    
    if (!$('#company_name').val().trim()) {
        $('#sfda-errors').show().text('Company name is required');
        return false;
    }
    
    if (!$('#address').val().trim()) {
        $('#sfda-errors').show().text('Address is required');
        return false;
    }
    
    $('#sfda-errors').hide();
    return isValid;
}

// Clear form
function clearSfdaForm() {
    $('#sfda-form')[0].reset();
    $('#sfda-errors').hide();
    $('.success-string').hide();
    $('#accreditation_other_text').hide();
    
    // Clear file lists
    uploadFields.forEach(function(field) {
        $('#ul_' + field).empty();
    });
    
    // Clear checkboxes
    $('input[name="accreditation_certificates[]"]').prop('checked', false);
}
