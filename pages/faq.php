<?php
// pages/faq.php
@session_start();
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'includes/func.php';

// Check if user is logged in (optional - remove if FAQ should be public)
/*
$myuser = cuser::singleton();
if (!$myuser->isLoggedIn()) {
    header('Location: /login');
    exit;
}
*/

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    header('Location: /error');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <title>FAQ - Halal e-Zone</title>
  
    <!-- Font Awesome -->

    
    <style>
        .faq-search {
            margin-bottom: 30px;
        }
        
        .faq-category-tabs {
            margin-bottom: 20px;
        }
        
        .faq-accordion .panel {
            border-radius: 0;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            box-shadow: none;
        }
        
        .faq-accordion .panel-heading {
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .faq-accordion .panel-heading:hover {
            background-color: #e9ecef;
        }
        
        .faq-accordion .panel-title {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
        }
        
        .faq-accordion .panel-title a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            position: relative;
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
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
        }
        
        .faq-accordion .panel-title a.collapsed:after {
            transform: translateY(-50%) rotate(-90deg);
        }
        
        .faq-accordion .panel-body {
            padding: 20px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            line-height: 1.6;
        }
        
        .faq-search-input {
            position: relative;
        }
        
        .faq-search-input .form-control {
            padding-left: 40px;
            height: 45px;
            font-size: 16px;
        }
        
        .faq-search-input .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .no-results .fa {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 2px;
        }
        
        .faq-stats {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            color: #666;
        }
        
        .nav-pills > li.active > a {
            background-color: #337ab7;
        }
        
        .nav-pills > li > a {
            border-radius: 20px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .nav-pills > li {
                float: none;
                text-align: center;
            }
            
            .nav-pills > li > a {
                margin: 2px 0;
            }
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');?>
    <!-- Main Container -->
    <div class="main-container ace-save-state" id="main-container">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Page Header -->
                            <div class="page-header">
                                <h1>
                                    <i class="fa fa-question-circle"></i>
                                    Frequently Asked Questions
                                    <small>Find answers to common questions</small>
                                </h1>
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
                                        </a>
                                    </li>
                                    <!-- Dynamic categories will be loaded here -->
                                </ul>
                            </div>

                            <!-- FAQ Accordion -->
                            <div class="panel-group faq-accordion" id="faqAccordion">
                                <!-- Dynamic FAQ items will be loaded here -->
                            </div>

                            <!-- No Results Message -->
                            <div class="no-results" id="noResults" style="display: none;">
                                <i class="fa fa-search"></i>
                                <h3>No results found</h3>
                                <p>Try adjusting your search terms or browse through different categories.</p>
                            </div>

                            <!-- Loading Indicator -->
                            <div class="text-center" id="loadingIndicator">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p>Loading FAQs...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once('pages/footer.php'); ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            let allFaqs = [];
            let allCategories = [];
            let currentCategory = 'all';
            let searchTimeout;

            // Initialize FAQ page
            initializeFAQ();

            function initializeFAQ() {
                loadCategories();
                loadFAQs();
            }

            // Load categories from database
            function loadCategories() {
                $.ajax({
                    url: 'ajax/faqPublic.php',
                    type: 'GET',
                    data: { action: 'get_categories' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            allCategories = response.data;
                            renderCategories();
                        }
                    },
                    error: function() {
                        console.log('Error loading categories');
                    }
                });
            }

            // Load FAQs from database
            function loadFAQs() {
                $.ajax({
                    url: 'ajax/faqPublic.php',
                    type: 'GET',
                    data: { action: 'get_public_faqs' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            allFaqs = response.data;
                            renderFAQs();
                            updateStats();
                        }
                        $('#loadingIndicator').hide();
                    },
                    error: function() {
                        console.log('Error loading FAQs');
                        $('#loadingIndicator').hide();
                        showNoResults();
                    }
                });
            }

            // Render category tabs
            function renderCategories() {
                let categoryHtml = '<li class="active"><a href="#" data-category="all"><i class="fa fa-list"></i> All Categories</a></li>';
                
                allCategories.forEach(function(category) {
                    categoryHtml += `<li><a href="#" data-category="${category.id}"><i class="fa fa-folder"></i> ${category.name}</a></li>`;
                });
                
                $('#categoryTabs').html(categoryHtml);
            }

            // Render FAQ accordion
            function renderFAQs(faqs = null) {
                const faqsToRender = faqs || getFilteredFAQs();
                let accordionHtml = '';
                
                if (faqsToRender.length === 0) {
                    showNoResults();
                    return;
                }

                faqsToRender.forEach(function(faq, index) {
                    const panelId = `faq-${faq.id}`;
                    const searchTerm = $('#faqSearch').val().toLowerCase();
                    const highlightedQuestion = highlightText(faq.question, searchTerm);
                    const highlightedAnswer = highlightText(faq.answer, searchTerm);
                    
                    accordionHtml += `
                        <div class="panel panel-default" data-faq-id="${faq.id}" data-category="${faq.category_id || 'none'}">
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
                updateStats();
            }

            // Get filtered FAQs based on category and search
            function getFilteredFAQs() {
                let filtered = allFaqs.filter(faq => faq.is_active == 1);
                
                // Filter by category
                if (currentCategory !== 'all') {
                    filtered = filtered.filter(faq => faq.category_id == currentCategory);
                }
                
                // Filter by search
                const searchTerm = $('#faqSearch').val().toLowerCase();
                if (searchTerm) {
                    filtered = filtered.filter(faq => 
                        faq.question.toLowerCase().includes(searchTerm) || 
                        faq.answer.toLowerCase().includes(searchTerm)
                    );
                }
                
                return filtered;
            }

            // Highlight search terms
            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                return text.replace(regex, '<span class="highlight">$1</span>');
            }

            // Escape regex special characters
            function escapeRegex(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\        .faq-search-input .');
            }

            // Show no results message
            function showNoResults() {
                $('#faqAccordion').empty();
                $('#noResults').show();
                updateStats();
            }

            // Update statistics
            function updateStats() {
                const visibleCount = getFilteredFAQs().length;
                const totalCount = allFaqs.filter(faq => faq.is_active == 1).length;
                
                $('#visibleCount').text(visibleCount);
                $('#totalCount').text(totalCount);
            }

            // Event Handlers
            
            // Category tab click
            $(document).on('click', '#categoryTabs a', function(e) {
                e.preventDefault();
                
                // Update active tab
                $('#categoryTabs li').removeClass('active');
                $(this).parent().addClass('active');
                
                // Update current category
                currentCategory = $(this).data('category');
                
                // Re-render FAQs
                renderFAQs();
            });

            // Search input
            $('#faqSearch').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    renderFAQs();
                }, 300);
            });

            // Clear search on ESC key
            $('#faqSearch').on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC key
                    $(this).val('');
                    renderFAQs();
                }
            });

            // Accordion panel tracking (for analytics)
            $(document).on('shown.bs.collapse', '.panel-collapse', function() {
                const faqId = $(this).closest('.panel').data('faq-id');
                // Track FAQ view for analytics
                trackFAQView(faqId);
            });

            // Track FAQ views
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
        });
    </script>
</body>
</html>