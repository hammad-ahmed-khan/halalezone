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
<style>
 
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
</style>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12"> 
                      <!--<h2> FAQ Management  </h2>-->
 <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 1.5rem; margin-bottom: 1.5rem;">

                    
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#faqModal" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Add FAQ
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#categoryModal">
                            <i class="fas fa-folder-plus"></i> Add Category
                        </button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#tagModal">
                            <i class="fas fa-tag"></i> Add Tag
                        </button>
                    </div>
                </div>
                
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
            </div>
        </div>
    </div>
</div>
<!-- FAQ Modal -->
 <div class="modal fade" id="faqModal" tabindex="-1" role="dialog" aria-labelledby="faqModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                                <div id="variantsContainer" style="margin-bottom: 10px;">
                                    <!-- Variants will be added here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-default" onclick="addVariant()">
                                    <i class="glyphicon glyphicon-plus"></i> Add Variant
                                </button>
                                <p class="help-block">Add alternative phrasings for the same question</p>
                            </div>
                        </div>
                        
                        <!-- Right Column (Metadata) -->
                        <div class="col-md-5">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="category_id" class="control-label">Category</label>
                                        <select class="form-control" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="priority" class="control-label">Priority</label>
                                        <input type="number" class="form-control" id="priority" name="priority" value="1" min="1" max="10">
                                        <p class="help-block">Higher numbers have higher priority (1-10)</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="training_weight" class="control-label">Training Weight</label>
                                        <input type="number" class="form-control" id="training_weight" name="training_weight" value="1.00" min="0.1" max="2.0" step="0.1">
                                        <p class="help-block">Importance for AI training (0.1-2.0)</p>
                                    </div>
                                </div>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    <h4 class="modal-title" id="categoryModalLabel">Add Category</h4>
                </div>
                <form id="categoryForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_description" class="form-label">Description</label>
                            <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    <h4 class="modal-title" id="tagModalLabel">Add Tag</h4>
                </div>
                <form id="tagForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tag_name" class="form-label">Tag Name *</label>
                            <input type="text" class="form-control" id="tag_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="tag_description" class="form-label">Description</label>
                            <textarea class="form-control" id="tag_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save"></i> Save Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include_once('pages/footer.php');?>
<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<!-- ace scripts -->
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
        // File: js/faq_management.js (embedded in index.php)
        
        let faqsTable;
        let isEditMode = false;
        let variantCounter = 0;
        
        $(document).ready(function() {
            // Initialize DataTable
            initDataTable();
            
            // Load categories and tags
            loadCategories();
            loadTags();
            
            // Form submissions
            $('#faqForm').on('submit', handleFaqSubmit);
            $('#categoryForm').on('submit', handleCategorySubmit);
            $('#tagForm').on('submit', handleTagSubmit);
            
            // Modal events
            $('#faqModal').on('hidden.bs.modal', resetFaqForm);
            $('#categoryModal').on('hidden.bs.modal', resetCategoryForm);
            $('#tagModal').on('hidden.bs.modal', resetTagForm);
        });
        
        function initDataTable() {
          return false;
            faqsTable = $('#faqsTable').DataTable({
                ajax: {
                    url: 'ajax/faqManager.php?action=get_faqs',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'id' },
                    { 
                        data: 'question',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return data.length > 50 ? data.substring(0, 50) + '...' : data;
                            }
                            return data;
                        }
                    },
                    { data: 'category_name', defaultContent: '<em>None</em>' },
                    { 
                        data: 'priority',
                        render: function(data, type, row) {
                            return `<span class="badge bg-info priority-badge">${data}</span>`;
                        }
                    },
                    { 
                        data: 'training_weight',
                        render: function(data, type, row) {
                            return `<span class="badge bg-secondary weight-badge">${data}</span>`;
                        }
                    },
                    { 
                        data: 'is_active',
                        render: function(data, type, row) {
                            const statusClass = data == 1 ? 'status-active' : 'status-inactive';
                            const statusText = data == 1 ? 'Active' : 'Inactive';
                            const icon = data == 1 ? 'fas fa-check-circle' : 'fas fa-times-circle';
                            return `<span class="${statusClass}"><i class="${icon}"></i> ${statusText}</span>`;
                        }
                    },
                    { 
                        data: 'tags',
                        render: function(data, type, row) {
                            if (!data) return '<em>None</em>';
                            const tags = data.split(', ');
                            return tags.map(tag => `<span class="badge bg-light text-dark">${tag}</span>`).join(' ');
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
                                <div class="btn-group btn-group-sm" role="group">
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
        
        function openAddModal() {
            isEditMode = false;
            $('#faqModalLabel').text('Add FAQ');
            resetFaqForm();
            addVariant(); // Add one empty variant by default
        }
        
        function editFaq(id) {
            isEditMode = true;
            $('#faqModalLabel').text('Edit FAQ');
            
            $.get(`ajax/faqManager.php?action=get_faq&id=${id}`)
                .done(function(response) {
                    if (response.success) {
                        const faq = response.data;
                        
                        // Fill form fields
                        $('#faq_id').val(faq.id);
                        $('#question').val(faq.question);
                        $('#answer').val(faq.answer);
                        $('#category_id').val(faq.category_id || '');
                        $('#priority').val(faq.priority);
                        $('#training_weight').val(faq.training_weight);
                        
                        // Select tags
                        const selectedTags = faq.selected_tags ? faq.selected_tags.map(tag => tag.id.toString()) : [];
                        $('#tags').val(selectedTags);
                        
                        // Load variants
                        $('#variantsContainer').empty();
                        variantCounter = 0;
                        
                        if (faq.variants && faq.variants.length > 0) {
                            faq.variants.forEach(function(variant) {
                                addVariant(variant.variant_question, variant.confidence_score);
                            });
                        } else {
                            addVariant(); // Add empty variant if none exist
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
        
        function addVariant(question = '', confidence = 1.00) {
            variantCounter++;
            const html = `
                <div class="variant-row" id="variant_${variantCounter}">
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="variant_questions[]" 
                                   placeholder="Alternative question phrasing" value="${question}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" class="form-control" name="variant_confidence[]" 
                                   placeholder="Confidence" min="0.1" max="1.0" step="0.1" value="${confidence}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariant(${variantCounter})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#variantsContainer').append(html);
        }
        
        function removeVariant(id) {
            $(`#variant_${id}`).remove();
        }
        
        function handleFaqSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = isEditMode ? 'update_faq' : 'create_faq';
            formData.append('action', action);
            
            $.ajax({
                url: 'ajax/faqManager.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#faqModal').modal('hide');
                    faqsTable.ajax.reload();
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Error saving FAQ', 'danger');
            });
        }
        
        function handleCategorySubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_category');
            
            $.ajax({
                url: 'ajax/faqManager.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#categoryModal').modal('hide');
                    loadCategories();
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Error creating category', 'danger');
            });
        }
        
        function handleTagSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create_tag');
            
            $.ajax({
                url: 'ajax/faqManager.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#tagModal').modal('hide');
                    loadTags();
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .fail(function() {
                showAlert('Error creating tag', 'danger');
            });
        }
        
        function resetFaqForm() {
            $('#faqForm')[0].reset();
            $('#faq_id').val('');
            $('#tags').val([]);
            $('#variantsContainer').empty();
            variantCounter = 0;
        }
        
        function resetCategoryForm() {
            $('#categoryForm')[0].reset();
        }
        
        function resetTagForm() {
            $('#tagForm')[0].reset();
        }
        
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert at the top of the container
            $('.container-fluid').prepend(alertHtml);
            
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