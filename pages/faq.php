<?php
// pages/faq.php
@session_start();
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'includes/func.php';

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    header('Location: /error');
    exit;
}

// Get user data for permissions
$myuser = cuser::singleton();
$myuser->getUserData();
$isAdmin = $myuser->userdata['isclient'] == "0";
$isAuditor = $myuser->userdata['isclient'] == "2";
$canAskQuestions = ($isAdmin || $isAuditor);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <title>FAQ - Halal e-Zone</title>
    
    <style>
        .faq-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
            padding: 30px;
        }
        
        .faq-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .faq-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .faq-search {
            margin-bottom: 30px;
        }
        
        .faq-search-input {
            position: relative;
        }
        
        .faq-search-input .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 2;
        }
        
        .faq-search-input .form-control {
            padding-left: 40px;
            padding-right: 40px;
            border-radius: 25px;
            border: 2px solid #e0e0e0;
            font-size: 16px;
            height: 50px;
            transition: all 0.3s ease;
        }
        
        .faq-search-input .form-control:focus {
            border-color: #337ab7;
            box-shadow: 0 0 0 3px rgba(51, 122, 183, 0.1);
        }
        
        .faq-search-input .clear-search {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            display: none;
        }
        
        .faq-stats {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .faq-category-tabs {
            margin-bottom: 30px;
        }
        
        .faq-category-tabs .nav-pills > li > a {
            border-radius: 20px;
            margin: 2px;
            transition: all 0.3s ease;
        }
        
        .faq-accordion .panel {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            margin-bottom: 10px;
            box-shadow: none;
            overflow: hidden;
        }
        
        .faq-accordion .panel-heading {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .faq-accordion .panel-heading:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }
        
        .faq-accordion .panel-title {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
        }
        
        .faq-accordion .panel-title a {
            display: block;
            padding: 20px 25px;
            text-decoration: none;
            color: #333;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .faq-accordion .panel-title a:hover,
        .faq-accordion .panel-title a:focus {
            text-decoration: none;
            color: #337ab7;
        }
        
        .faq-accordion .panel-title a:after {
            content: "\f078";
            font-family: FontAwesome;
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
            color: #337ab7;
        }
        
        .faq-accordion .panel-title a.collapsed:after {
            transform: translateY(-50%) rotate(-90deg);
        }
        
        .faq-accordion .panel-body {
            padding: 25px;
            background-color: #fdfdfd;
            border-top: 1px solid #f0f0f0;
            color: #555;
            line-height: 1.6;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 1px 3px;
            border-radius: 3px;
            font-weight: 500;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            display: none;
        }
        
        .no-results i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .category-count {
            background: #337ab7;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            margin-left: 5px;
        }
        
        /* My Questions Section */
        .my-questions-section {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        
        .question-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-answered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        @media (max-width: 768px) {
            .faq-container {
                margin: 10px;
                padding: 20px;
            }
            
            .faq-category-tabs .nav-pills {
                flex-wrap: wrap;
            }
            
            .faq-category-tabs .nav-pills > li {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body class="no-skin">
    <?php include_once('pages/navigation.php');?>
    
    <div class="main-container ace-save-state" id="main-container">
         
        <div class="main-content">
            <div class="main-content-inner">
                <div class="page-content">
                    <div class="container-fluid">
                        <div class="faq-container">
                            <!-- FAQ Header -->
                            <div class="faq-header">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h1>
                                            <i class="fa fa-question-circle text-primary"></i>
                                            Frequently Asked Questions
                                            <small>Find answers to common questions</small>
                                        </h1>
                                    </div>
                                    <?php if ($canAskQuestions): ?>
                                    <div class="col-md-4" style="text-align: right; padding-top: 15px;">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#askQuestionModal">
                                            <i class="fa fa-question-circle"></i> Ask a Question
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Search Section -->
                            <div class="faq-search">
                                <div class="row">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="faq-search-input">
                                            <i class="fa fa-search search-icon"></i>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="faqSearch" 
                                                   placeholder="Search FAQs..."
                                                   autocomplete="off">
                                            <button type="button" class="clear-search" id="clearSearch">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Stats -->
                            <div class="faq-stats" id="faqStats">
                                <span id="visibleCount">0</span> of <span id="totalCount">0</span> questions shown
                            </div>

                            <!-- Category Navigation -->
                            <div class="faq-category-tabs">
                                <ul class="nav nav-pills nav-justified" id="categoryTabs">
                                    <li class="active">
                                        <a href="#" data-category="all">
                                            <i class="fa fa-list"></i> All Categories
                                            <span class="category-count" id="count-all">0</span>
                                        </a>
                                    </li>
                                    <!-- Dynamic categories will be loaded here -->
                                </ul>
                            </div>

                            <!-- Loading Indicator -->
                            <div class="loading" id="loadingIndicator">
                                <i class="fa fa-spinner fa-spin"></i>
                                <p>Loading FAQs...</p>
                            </div>

                            <!-- FAQ Accordion -->
                            <div class="panel-group faq-accordion" id="faqAccordion">
                                <!-- Dynamic FAQ items will be loaded here -->
                            </div>

                            <!-- No Results Message -->
                            <div class="no-results" id="noResults">
                                <i class="fa fa-search"></i>
                                <h3>No Results Found</h3>
                                <p>We couldn't find any FAQs matching your search criteria.</p>
                                <p>Try using different keywords or browse all categories.</p>
                            </div>
                            
                            <?php if ($canAskQuestions): ?>
                            <!-- My Questions Section -->
                            <div class="my-questions-section" id="myQuestionsSection" style="display: none;">
                                <h3><i class="fa fa-user"></i> My Questions</h3>
                                <p class="text-muted">Questions you've submitted</p>
                                <div id="myQuestionsList">
                                    <!-- User's questions will be loaded here -->
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($canAskQuestions): ?>
    <!-- Ask Question Modal -->
    <div class="modal fade" id="askQuestionModal" tabindex="-1" role="dialog" aria-labelledby="askQuestionModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="askQuestionModalLabel">Ask a Question</h4>
                </div>
                <form id="askQuestionForm">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            Submit your question, and our team will review it. You’ll receive an email notification once a response is available.
                        </div>
                        
                        <div class="form-group">
                            <label for="questionTitle" class="control-label">Question *</label>
                            <textarea class="form-control" id="questionTitle" name="question" rows="3" required 
                                    placeholder="Enter your question clearly and concisely"></textarea>
                            <p class="help-block">Be as specific as possible to help us provide a better answer</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="questionCategory" class="control-label">Category</label>
                            <select class="form-control" id="questionCategory" name="category_id">
                                <option value="">Select a category (optional)</option>
                                <!-- Categories will be loaded dynamically -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="questionContext" class="control-label">Additional Context</label>
                            <textarea class="form-control" id="questionContext" name="context" rows="2" 
                                    placeholder="Provide any additional context that might help us answer your question"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Submit Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php include_once('pages/footer.php');?>

    <!-- Scripts -->
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791'></script>

    <script>
        $(document).ready(function() {
            let currentCategory = 'all';
            let currentSearch = '';
            let searchTimeout;
            let isLoading = false;

            // Initialize the FAQ page
            init();

            function init() {
                loadCategories();
                loadFAQs();
                <?php if ($canAskQuestions): ?>
                loadMyQuestions();
                <?php endif; ?>
                bindEvents();
            }

            function bindEvents() {
                // Category tab click
                $(document).on('click', '#categoryTabs a', function(e) {
                    e.preventDefault();
                    
                    if (isLoading) return;
                    
                    // Update active tab
                    $('#categoryTabs li').removeClass('active');
                    $(this).parent().addClass('active');
                    
                    // Update current category
                    currentCategory = $(this).data('category');
                    
                    // Load FAQs for selected category
                    loadFAQs();
                });

                // Search input with debouncing
                $('#faqSearch').on('input', function() {
                    const searchValue = $(this).val().trim();
                    
                    // Show/hide clear button
                    if (searchValue.length > 0) {
                        $('#clearSearch').show();
                    } else {
                        $('#clearSearch').hide();
                    }
                    
                    // Debounce search requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        currentSearch = searchValue;
                        loadFAQs();
                    }, 300);
                });

                // Clear search button
                $('#clearSearch').on('click', function() {
                    $('#faqSearch').val('');
                    $(this).hide();
                    currentSearch = '';
                    loadFAQs();
                });

                // Clear search on ESC key
                $('#faqSearch').on('keydown', function(e) {
                    if (e.keyCode === 27) { // ESC key
                        $(this).val('');
                        $('#clearSearch').hide();
                        currentSearch = '';
                        loadFAQs();
                    }
                });

                // Track FAQ views when accordion panels are opened
                $(document).on('shown.bs.collapse', '.panel-collapse', function() {
                    const faqId = $(this).closest('.panel').data('faq-id');
                    if (faqId) {
                        trackFAQView(faqId);
                    }
                });

                <?php if ($canAskQuestions): ?>
                // Ask question form submission
                $('#askQuestionForm').on('submit', function(e) {
                    e.preventDefault();
                    submitQuestion();
                });

                // Reset form when modal is closed
                $('#askQuestionModal').on('hidden.bs.modal', function() {
                    $('#askQuestionForm')[0].reset();
                    $('.alert-danger').remove();
                });
                <?php endif; ?>
            }

            // Load categories from server
            function loadCategories() {
                $.ajax({
                    url: 'ajax/faqPublic.php',
                    type: 'GET',
                    data: { action: 'get_categories' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            renderCategories(response.data);
                            <?php if ($canAskQuestions): ?>
                            populateCategoryDropdown(response.data);
                            <?php endif; ?>
                        }
                    },
                    error: function() {
                        console.error('Error loading categories');
                    }
                });
            }

            // Render category tabs
            function renderCategories(categories) {
                let categoryHtml = `
                    <li class="active">
                        <a href="#" data-category="all">
                            <i class="fa fa-list"></i> All Categories
                            <span class="category-count" id="count-all">0</span>
                        </a>
                    </li>
                `;

                categories.forEach(function(category) {
                    categoryHtml += `
                        <li>
                            <a href="#" data-category="${category.id}">
                                <i class="fa fa-folder"></i> ${category.name}
                                <span class="category-count" id="count-${category.id}">0</span>
                            </a>
                        </li>
                    `;
                });

                $('#categoryTabs').html(categoryHtml);
            }

            <?php if ($canAskQuestions): ?>
            // Populate category dropdown in ask question modal
            function populateCategoryDropdown(categories) {
                const $select = $('#questionCategory');
                $select.find('option:not(:first)').remove();
                
                categories.forEach(function(category) {
                    $select.append(`<option value="${category.id}">${category.name}</option>`);
                });
            }

            // Submit question
            function submitQuestion() {
                const formData = $('#askQuestionForm').serialize();
                
                $.post('ajax/faqPublic.php', formData + '&action=submit_question')
                    .done(function(response) {
                        if (response.success) {
                            $('#askQuestionModal').modal('hide');
                            showAlert('Your question has been submitted successfully. You will be notified by email when it is answered.', 'success');
                            loadMyQuestions(); // Reload user's questions
                        } else {
                            showAlert(response.message || 'Error submitting question', 'danger');
                        }
                    })
                    .fail(function() {
                        showAlert('Error submitting question. Please try again.', 'danger');
                    });
            }

            // Load user's questions
            function loadMyQuestions() {
                $.get('ajax/faqPublic.php?action=get_my_questions')
                    .done(function(response) {
                        if (response.success && response.data && response.data.length > 0) {
                            $('#myQuestionsSection').show();
                            renderMyQuestions(response.data);
                        }
                    });
            }

            // Render user's questions
            function renderMyQuestions(questions) {
                let html = '';
                questions.forEach(function(question) {
                    const statusClass = question.status === 'answered' ? 'status-answered' : 'status-pending';
                    const statusText = question.status === 'answered' ? 'Answered' : 'Pending';
                    
                    html += `
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="panel-title">${question.question}</h4>
                                        <div style="margin-top: 8px;">
                                            <span class="question-status ${statusClass}">${statusText}</span>
                                            <small class="text-muted" style="margin-left: 10px;">
                                                Asked on ${new Date(question.created_at).toLocaleDateString()}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ${question.status === 'answered' && question.answer ? `
                                <div class="panel-body">
                                    <strong>Answer:</strong><br>
                                    <p>${question.answer}</p>
                                    <small class="text-muted">
                                        Answered on ${new Date(question.updated_at).toLocaleDateString()}
                                    </small>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                
                $('#myQuestionsList').html(html);
            }
            <?php endif; ?>

            // Load FAQs from server with current filters
            function loadFAQs() {
                if (isLoading) return;
                
                isLoading = true;
                showLoading();

                const requestData = {
                    action: 'get_filtered_faqs',
                    category_id: currentCategory,
                    search: currentSearch
                };

                $.ajax({
                    url: 'ajax/faqPublic.php',
                    type: 'GET',
                    data: requestData,
                    dataType: 'json',
                    success: function(response) {
                        isLoading = false;
                        hideLoading();
                        
                        if (response.success) {
                            renderFAQs(response.data.faqs || []);
                            updateStats(response.data.stats || {});
                            updateCategoryCounts(response.data.category_counts || {});
                        } else {
                            showError('Error loading FAQs: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        isLoading = false;
                        hideLoading();
                        showError('Failed to load FAQs. Please try again.');
                        console.error('AJAX Error:', error);
                    }
                });
            }

            // Render FAQ accordion
            function renderFAQs(faqs) {
                if (!faqs || faqs.length === 0) {
                    showNoResults();
                    return;
                }

                let accordionHtml = '';
                faqs.forEach(function(faq, index) {
                    const panelId = `faq-panel-${faq.id}`;
                    const highlightedQuestion = highlightText(faq.question, currentSearch);
                    const highlightedAnswer = highlightText(faq.answer, currentSearch);
                    
                    accordionHtml += `
                        <div class="panel panel-default" data-faq-id="${faq.id}">
                            <div class="panel-heading" role="tab" id="heading-${panelId}">
                                <h4 class="panel-title">
                                    <a role="button" 
                                       data-toggle="collapse" 
                                       data-parent="#faqAccordion" 
                                       href="#${panelId}" 
                                       aria-expanded="false" 
                                       aria-controls="${panelId}"
                                       class="collapsed">
                                        ${highlightedQuestion}
                                    </a>
                                </h4>
                            </div>
                            <div id="${panelId}" 
                                 class="panel-collapse collapse" 
                                 role="tabpanel" 
                                 aria-labelledby="heading-${panelId}">
                                <div class="panel-body">
                                    ${highlightedAnswer}
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#faqAccordion').html(accordionHtml);
                $('#noResults').hide();
            }

            // Update statistics display
            function updateStats(stats) {
                $('#visibleCount').text(stats.visible_count || 0);
                $('#totalCount').text(stats.total_count || 0);
            }

            // Update category counts
            function updateCategoryCounts(counts) {
                // Update "All Categories" count
                $('#count-all').text(counts.all || 0);
                
                // Update individual category counts
                Object.keys(counts).forEach(function(categoryId) {
                    if (categoryId !== 'all') {
                        $(`#count-${categoryId}`).text(counts[categoryId] || 0);
                    }
                });
            }

            // Highlight search terms in text
            function highlightText(text, searchTerm) {
                if (!searchTerm || searchTerm.length === 0) {
                    return text;
                }
                
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            // Escape regex special characters
            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            // Show loading indicator
            function showLoading() {
                $('#loadingIndicator').show();
                $('#faqAccordion').hide();
                $('#noResults').hide();
            }

            // Hide loading indicator
            function hideLoading() {
                $('#loadingIndicator').hide();
                $('#faqAccordion').show();
            }

            // Show no results message
            function showNoResults() {
                $('#faqAccordion').hide();
                $('#noResults').show();
            }

            // Show error message
            function showError(message) {
                $('#faqAccordion').html(`
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        ${message}
                    </div>
                `);
                $('#noResults').hide();
            }

            // Track FAQ view for analytics
            function trackFAQView(faqId) {
                $.ajax({
                    url: 'ajax/faqPublic.php',
                    type: 'POST',
                    data: { 
                        action: 'track_faq_view',
                        faq_id: faqId
                    },
                    dataType: 'json'
                });
            }

            // Show alert messages
            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        ${message}
                    </div>
                `;
                
                // Remove existing alerts
                $('.alert').remove();
                
                // Add new alert at the top of the container
                $('.faq-container').prepend(alertHtml);
                
                // Auto-hide success alerts after 5 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        $('.alert-success').fadeOut();
                    }, 5000);
                }
            }
        });
    </script>
</body>
</html>
