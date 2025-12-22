<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <title>FAQ Management - Halal e-Zone</title>
    <style>
        .blockUI h1 {
            font-size: 18px;
            margin: 10px auto;
        }
        td.changed {
            background:greenyellow;
        }
        tr.highlighted-conformed .fa-flag {
            display: none !important;
        }
        .variant-row {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        .priority-badge {
            font-weight: bold;
        }
        .weight-badge {
            font-size: 0.75em;
        }
        .status-active {
            color: #198754;
        }
        .status-inactive {
            color: #dc3545;
        }
        .mb-3 {
            margin-bottom:3px;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .table-actions {
            white-space: nowrap;
        }
/* Modern Tabs */
.management-tabs .nav-tabs {
    border-bottom: 2px solid #e5e7eb; /* lighter border */
    display: flex;
    gap: 1rem;
}

.management-tabs .nav-tabs li {
    margin-bottom: -2px; /* aligns with bottom border */
}

.management-tabs .nav-tabs a {
    font-weight: 500;
    color: #6b7280; /* gray-600 */
    padding: 10px 18px;
    border: none;
    border-bottom: 2px solid transparent;
    background: transparent;
    transition: all 0.25s ease;
    border-radius: 6px 6px 0 0;
}

.management-tabs .nav-tabs a:hover {
    color: #111827; /* gray-900 */
    background: #f9fafb; /* light hover */
}

.management-tabs .nav-tabs .active a,
.management-tabs .nav-tabs .active a:focus,
.management-tabs .nav-tabs .active a:hover {
    color: #2563eb; /* modern blue */
    font-weight: 600;
    border-bottom: 2px solid #2563eb;
    background: #f9fafb;
}

/* Modern Tab Content */
.tab-content {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0 0 8px 8px;
    padding: 20px;
    margin-top: -1px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
        .category-item, .tag-item {
            padding: 10px 15px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-details h5 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .item-details p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        .item-actions .btn {
            margin-left: 5px;
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');
try {
    $db = acsessDb :: singleton();
    $dbo =  $db->connect(); // Create database connection object
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}

$myuser = cuser::singleton();
$myuser->getUserData();
$isAdmin = $myuser->userdata['isclient'] == "0";
$isAuditor = $myuser->userdata['isclient'] == "2";
$isClient = $myuser->userdata['isclient'] == "1";
?>

<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12"> 
                        <!-- Header Actions -->
                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 1.5rem; margin-bottom: 1.5rem;">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#faqModal" onclick="openAddModal()">
                                    <i class="fas fa-plus"></i> Add FAQ
                                </button>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#categoryModal" onclick="openAddCategoryModal()">
                                    <i class="fas fa-folder-plus"></i> Add Category
                                </button>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#tagModal" onclick="openAddTagModal()">
                                    <i class="fas fa-tag"></i> Add Tag
                                </button>
                            </div>
                        </div>

                        <!-- Management Tabs -->
                        <div class="management-tabs">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#faqs-tab" aria-controls="faqs-tab" role="tab" data-toggle="tab">
                                        <i class="fas fa-question-circle"></i> FAQs
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#categories-tab" aria-controls="categories-tab" role="tab" data-toggle="tab">
                                        <i class="fas fa-folder"></i> Categories
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#tags-tab" aria-controls="tags-tab" role="tab" data-toggle="tab">
                                        <i class="fas fa-tags"></i> Tags
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- FAQs Tab -->
                            <div role="tabpanel" class="tab-pane active" id="faqs-tab">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">FAQs List</h5>
                                    </div>
                                    <div class="card-body">
                                        <table id="faqsTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Question</th>
                                                    <th>Category</th>
                                                    <th>Priority</th>
                                                    <th>Weight</th>
                                                    <th>Status</th>
                                                    <th>Tags</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories Tab -->
                            <div role="tabpanel" class="tab-pane" id="categories-tab">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Categories Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="categoriesList">
                                            <!-- Categories will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tags Tab -->
                            <div role="tabpanel" class="tab-pane" id="tags-tab">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Tags Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="tagsList">
                                            <!-- Tags will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" role="dialog" aria-labelledby="faqModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="faqModalLabel">Add FAQ</h4>
            </div>
            
            <form id="faqForm">
                <div class="modal-body">
                    <input type="hidden" id="faq_id" name="faq_id">
                    
                    <div class="row">
                        <!-- Left Column (Main Content) -->
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="question" class="control-label">Question *</label>
                                <textarea class="form-control" id="question" name="question" rows="3" required placeholder="Enter the frequently asked question"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="answer" class="control-label">Answer *</label>
                                <textarea class="form-control" id="answer" name="answer" rows="5" required placeholder="Provide the detailed answer"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="tags" class="control-label">Tags</label>
                                <select class="form-control" id="tags" name="tags[]" multiple>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                                <p class="help-block">Hold Ctrl (Cmd on Mac) to select multiple tags</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label">Question Variants</label>
                                <div id="variantsContainer">
                                    <!-- Variants will be added dynamically -->
                                </div>
                                <button type="button" class="btn btn-sm btn-success" onclick="addVariant()">
                                    <i class="fas fa-plus"></i> Add Variant
                                </button>
                                <p class="help-block">Add alternative ways to ask the same question</p>
                            </div>
                        </div>
                        
                        <!-- Right Column (Settings) -->
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="category_id" class="control-label">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="priority" class="control-label">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="1">Low</option>
                                    <option value="2">Normal</option>
                                    <option value="3" selected>High</option>
                                    <option value="4">Critical</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="training_weight" class="control-label">Training Weight</label>
                                <input type="number" class="form-control" id="training_weight" name="training_weight" value="1.00" min="0.1" max="2.0" step="0.1">
                                <p class="help-block">Importance for AI training (0.1-2.0)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="glyphicon glyphicon-remove"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="glyphicon glyphicon-floppy-disk"></i> Save FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="categoryModalLabel">Add Category</h4>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="category_id_edit" name="category_id">
                    <div class="form-group">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="category_description" class="form-label">Description</label>
                        <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tag Modal -->
<div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="tagModalLabel">Add Tag</h4>
            </div>
            <form id="tagForm">
                <div class="modal-body">
                    <input type="hidden" id="tag_id_edit" name="tag_id">
                    <div class="form-group">
                        <label for="tag_name" class="form-label">Tag Name *</label>
                        <input type="text" class="form-control" id="tag_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="tag_description" class="form-label">Description</label>
                        <textarea class="form-control" id="tag_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Save Tag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once('pages/footer.php');?>

<!-- JavaScript Libraries -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<script>
let faqsTable;
let isEditMode = false;
let isEditCategoryMode = false;
let isEditTagMode = false;
let variantCounter = 0;

$(document).ready(function() {
    // Initialize DataTable
    initDataTable();
    
    // Load initial data
    loadCategories();
    loadTags();
    loadCategoriesList();
    loadTagsList();
    
    // Form submissions
    $('#faqForm').on('submit', handleFaqSubmit);
    $('#categoryForm').on('submit', handleCategorySubmit);
    $('#tagForm').on('submit', handleTagSubmit);
    
    // Modal events
    $('#faqModal').on('hidden.bs.modal', resetFaqForm);
    $('#categoryModal').on('hidden.bs.modal', resetCategoryForm);
    $('#tagModal').on('hidden.bs.modal', resetTagForm);
    
    // Tab change events
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (e.target.getAttribute('href') === '#categories-tab') {
            loadCategoriesList();
        } else if (e.target.getAttribute('href') === '#tags-tab') {
            loadTagsList();
        }
    });
});

function initDataTable() {
    faqsTable = $('#faqsTable').DataTable({
        ajax: {
            url: 'ajax/faqManager.php?action=get_faqs',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id', visible: false },
            { 
                data: 'question',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return data.length > 50 ? 
                            '<span title="' + $('<div>').text(data).html() + '">' + 
                            $('<div>').text(data.substring(0, 50) + '...').html() + '</span>' : 
                            $('<div>').text(data).html();
                    }
                    return data;
                }
            },
            { 
                data: 'category_name',
                render: function(data, type, row) {
                    return data ? `<span class="label label-info">${data}</span>` : '<span class="text-muted">Uncategorized</span>';
                }
            },
            { 
                data: 'priority',
                render: function(data, type, row) {
                    const priorities = {1: 'Low', 2: 'Normal', 3: 'High', 4: 'Critical'};
                    const colors = {1: 'default', 2: 'primary', 3: 'warning', 4: 'danger'};
                    return `<span class="label label-${colors[data] || 'default'}">${priorities[data] || 'Normal'}</span>`;
                }
            },
            { 
                data: 'training_weight',
                render: function(data, type, row) {
                    return `<span class="badge badge-secondary">${parseFloat(data).toFixed(1)}</span>`;
                }
            },
            { 
                data: 'is_active',
                render: function(data, type, row) {
                    return data == 1 ? 
                        '<span class="label label-success">Active</span>' : 
                        '<span class="label label-default">Inactive</span>';
                }
            },
            { 
                data: 'tags',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-muted">No tags</span>';
                    const tags = data.split(', ');
                    return tags.map(tag => `<span class="label label-default">${tag}</span>`).join(' ');
                }
            },
            { 
                data: 'created_at',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm table-actions">
                            <button type="button" class="btn btn-outline-primary" onclick="editFaq(${row.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-${row.is_active == 1 ? 'warning' : 'success'}" 
                                    onclick="toggleStatus(${row.id})" title="${row.is_active == 1 ? 'Deactivate' : 'Activate'}">
                                <i class="fas fa-${row.is_active == 1 ? 'eye-slash' : 'eye'}"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteFaq(${row.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            emptyTable: "No FAQs found"
        }
    });
}

function loadCategories() {
    $.get('ajax/faqManager.php?action=get_categories')
        .done(function(response) {
            if (response.success) {
                const select = $('#category_id');
                select.find('option:not(:first)').remove();
                
                response.data.forEach(function(category) {
                    select.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }
        })
        .fail(function() {
            showAlert('Error loading categories', 'danger');
        });
}

function loadTags() {
    $.get('ajax/faqManager.php?action=get_tags')
        .done(function(response) {
            if (response.success) {
                const select = $('#tags');
                select.empty();
                
                response.data.forEach(function(tag) {
                    select.append(`<option value="${tag.id}">${tag.name}</option>`);
                });
            }
        })
        .fail(function() {
            showAlert('Error loading tags', 'danger');
        });
}

function loadCategoriesList() {
    $.get('ajax/faqManager.php?action=get_categories')
        .done(function(response) {
            if (response.success) {
                const container = $('#categoriesList');
                container.empty();
                
                if (response.data.length === 0) {
                    container.html('<p class="text-muted">No categories found.</p>');
                    return;
                }
                
                response.data.forEach(function(category) {
                    const categoryHtml = `
                        <div class="category-item">
                            <div class="item-details">
                                <h5>${category.name}</h5>
                                ${category.description ? `<p>${category.description}</p>` : ''}
                            </div>
                            <div class="item-actions">
                                <button type="button" class="btn btn-sm btn-primary" onclick="editCategory(${category.id}, '${category.name}', '${category.description || ''}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    `;
                    container.append(categoryHtml);
                });
            }
        })
        .fail(function() {
            showAlert('Error loading categories list', 'danger');
        });
}

function loadTagsList() {
    $.get('ajax/faqManager.php?action=get_tags')
        .done(function(response) {
            if (response.success) {
                const container = $('#tagsList');
                container.empty();
                
                if (response.data.length === 0) {
                    container.html('<p class="text-muted">No tags found.</p>');
                    return;
                }
                
                response.data.forEach(function(tag) {
                    const tagHtml = `
                        <div class="tag-item">
                            <div class="item-details">
                                <h5>${tag.name}</h5>
                                ${tag.description ? `<p>${tag.description}</p>` : ''}
                            </div>
                            <div class="item-actions">
                                <button type="button" class="btn btn-sm btn-info" onclick="editTag(${tag.id}, '${tag.name}', '${tag.description || ''}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteTag(${tag.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    `;
                    container.append(tagHtml);
                });
            }
        })
        .fail(function() {
            showAlert('Error loading tags list', 'danger');
        });
}

// FAQ Functions
function openAddModal() {
    isEditMode = false;
    $('#faqModalLabel').text('Add FAQ');
    resetFaqForm();
    addVariant();
}

function editFaq(id) {
    isEditMode = true;
    $('#faqModalLabel').text('Edit FAQ');
    
    $.get(`ajax/faqManager.php?action=get_faq&id=${id}`)
        .done(function(response) {
            if (response.success) {
                const faq = response.data;
                
                $('#faq_id').val(faq.id);
                $('#question').val(faq.question);
                $('#answer').val(faq.answer);
                $('#category_id').val(faq.category_id || '');
                $('#priority').val(faq.priority);
                $('#training_weight').val(faq.training_weight);
                
                const selectedTags = faq.selected_tags ? faq.selected_tags.map(tag => tag.id.toString()) : [];
                $('#tags').val(selectedTags);
                
                $('#variantsContainer').empty();
                variantCounter = 0;
                
                if (faq.variants && faq.variants.length > 0) {
                    faq.variants.forEach(function(variant) {
                        addVariant(variant.variant_question, variant.confidence_score);
                    });
                } else {
                    addVariant();
                }
                
                $('#faqModal').modal('show');
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Error loading FAQ details', 'danger');
        });
}

function deleteFaq(id) {
    if (confirm('Are you sure you want to delete this FAQ? This action cannot be undone.')) {
        $.post('ajax/faqManager.php', {
            action: 'delete_faq',
            id: id
        })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                faqsTable.ajax.reload();
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Error deleting FAQ', 'danger');
        });
    }
}

function toggleStatus(id) {
    $.post('ajax/faqManager.php', {
        action: 'toggle_status',
        id: id
    })
    .done(function(response) {
        if (response.success) {
            showAlert(response.message, 'success');
            faqsTable.ajax.reload();
        } else {
            showAlert(response.message, 'danger');
        }
    })
    .fail(function() {
        showAlert('Error updating FAQ status', 'danger');
    });
}

function handleFaqSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData($('#faqForm')[0]);
    formData.append('action', isEditMode ? 'update_faq' : 'create_faq');
    
    // Collect variants
    const variantQuestions = [];
    const variantConfidence = [];
    
    $('.variant-question').each(function() {
        if ($(this).val().trim()) {
            variantQuestions.push($(this).val().trim());
        }
    });
    
    $('.variant-confidence').each(function() {
        variantConfidence.push($(this).val());
    });
    
    // Add variants to form data
    variantQuestions.forEach((question, index) => {
        formData.append('variant_questions[]', question);
        formData.append('variant_confidence[]', variantConfidence[index] || 1.00);
    });
    
    $.ajax({
        url: 'ajax/faqManager.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#faqModal').modal('hide');
                faqsTable.ajax.reload();
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error saving FAQ', 'danger');
        }
    });
}

// Category Functions
function openAddCategoryModal() {
    isEditCategoryMode = false;
    $('#categoryModalLabel').text('Add Category');
    resetCategoryForm();
}

function editCategory(id, name, description) {
    isEditCategoryMode = true;
    $('#categoryModalLabel').text('Edit Category');
    $('#category_id_edit').val(id);
    $('#category_name').val(name);
    $('#category_description').val(description);
    $('#categoryModal').modal('show');
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
        $.post('ajax/faqManager.php', {
            action: 'delete_category',
            id: id
        })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadCategoriesList();
                loadCategories(); // Refresh dropdown
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Error deleting category', 'danger');
        });
    }
}

function handleCategorySubmit(e) {
    e.preventDefault();
    
    const formData = new FormData($('#categoryForm')[0]);
    formData.append('action', isEditCategoryMode ? 'update_category' : 'create_category');
    
    $.ajax({
        url: 'ajax/faqManager.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#categoryModal').modal('hide');
                loadCategoriesList();
                loadCategories(); // Refresh dropdown
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error saving category', 'danger');
        }
    });
}

// Tag Functions
function openAddTagModal() {
    isEditTagMode = false;
    $('#tagModalLabel').text('Add Tag');
    resetTagForm();
}

function editTag(id, name, description) {
    isEditTagMode = true;
    $('#tagModalLabel').text('Edit Tag');
    $('#tag_id_edit').val(id);
    $('#tag_name').val(name);
    $('#tag_description').val(description);
    $('#tagModal').modal('show');
}

function deleteTag(id) {
    if (confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
        $.post('ajax/faqManager.php', {
            action: 'delete_tag',
            id: id
        })
        .done(function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                loadTagsList();
                loadTags(); // Refresh dropdown
            } else {
                showAlert(response.message, 'danger');
            }
        })
        .fail(function() {
            showAlert('Error deleting tag', 'danger');
        });
    }
}

function handleTagSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData($('#tagForm')[0]);
    formData.append('action', isEditTagMode ? 'update_tag' : 'create_tag');
    
    $.ajax({
        url: 'ajax/faqManager.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                $('#tagModal').modal('hide');
                loadTagsList();
                loadTags(); // Refresh dropdown
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error saving tag', 'danger');
        }
    });
}

// Variant Functions
function addVariant(question = '', confidence = 1.00) {
    variantCounter++;
    const variantHtml = `
        <div class="variant-row" id="variant_${variantCounter}">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control variant-question" 
                           placeholder="Alternative question phrasing" 
                           value="${question}">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control variant-confidence" 
                           min="0.1" max="1.0" step="0.1" 
                           value="${confidence}" 
                           title="Confidence Score">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="removeVariant(${variantCounter})" 
                            title="Remove Variant">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#variantsContainer').append(variantHtml);
}

function removeVariant(id) {
    $(`#variant_${id}`).remove();
}

// Reset Functions
function resetFaqForm() {
    $('#faqForm')[0].reset();
    $('#faq_id').val('');
    $('#tags').val([]);
    $('#variantsContainer').empty();
    variantCounter = 0;
}

function resetCategoryForm() {
    $('#categoryForm')[0].reset();
    $('#category_id_edit').val('');
}

function resetTagForm() {
    $('#tagForm')[0].reset();
    $('#tag_id_edit').val('');
}

// Alert Function
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            ${message}
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of the container
    $('.page-content').prepend(alertHtml);
    
    // Auto-hide success alerts after 3 seconds
    if (type === 'success') {
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 3000);
    }
}
</script>

</body>
</html>