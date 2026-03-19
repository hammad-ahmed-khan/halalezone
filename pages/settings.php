<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('pages/header.php');
 include_once ('includes/func.php');?>
<title>Administration - Halal Digital</title>
<style>
#word-search {
    border-radius: 4px 0 0 4px;
}

#search-results {
    min-height: 16px;
    font-style: italic;
}

#bannedwords {
    resize: vertical;
    min-height: 200px;
}

.input-group-addon {
    background-color: #f5f5f5;
    border-color: #ccc;
}
</style>
</head>

<body>
<?php include_once('pages/navigation.php');?>
<?php
  $myuser = cuser::singleton();
  $myuser->getUserData();
  $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
  $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
  $isAdmin = (!$isClient && !isAuditor); 


	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT name, value '.
		'from tsettings where 1 = 1 order by name';
	$stmt = $dbo->prepare($sql);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$rows = $stmt->fetchAll();

	$settings = array();
	foreach ($rows as $row) {
		$settings[$row['name']] = $row['value']; 
	}
?>
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div class="row no-gutters">
          <div class="col-md-12">
              <h1>Settings</h1>
              <div class="col-md-6 col-md-offset-3">
                <form id="settings-form" class="col-md-12 form-horizontal">
                  <div class="row form-group">
  <label class="col-xs-12 col-md-4"><strong>Forbidden Words</strong> <!--<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Enter forbidden words separated by comma. Raw materials and products containing forbidden words will not be possible when entering the name."></sup>--></label>
  <div class='col-xs-12 col-md-8'>
    <!-- Search input -->
     <?php if ($isAuditor): ?>
    <div class="input-group" style="margin-bottom: 10px;">
      <span class="input-group-addon"><i class="fa fa-search"></i></span>
      <input type="text" class="form-control" id="word-search" placeholder="Search forbidden words..." autocomplete="off">
      <span class="input-group-btn">
        <button type="button" class="btn btn-default" id="clear-search" title="Clear search">
          <i class="fa fa-times"></i>
        </button>
      </span>
    </div>
    <!-- Search results info -->
    <div id="search-results" style="margin-bottom: 5px; font-size: 12px; color: #666;"></div>
    <?php endif; ?>

    
    
    <!-- Forbidden words textarea -->
    <textarea type="text" class="form-control" name="bannedwords" id="bannedwords" style="height:200px;" <?php if ($isAuditor): ?>readonly<?php endif;?>><?php echo $settings['bannedwords']; ?></textarea>
    <div class="alert-string"></div>
  </div>
</div>
                <?php if ($myuser->userdata['canadmin']): ?>
                  <div class="text-right">
                    <button type="button" class="btn btn-primary" id="btn-save" >Save changes</button>
                  </div>
                  <?php endif;  ?>
                </form>
             </div>
          </div>
        </div>
        <!-- PAGE CONTENT ENDS --> 
      </div>
      <!-- /.col --> 
    </div>
    <!-- /.row --> 
  </div>
  <!-- /.page-content --> 
</div>
</div>
<!-- /.main-content -->
</div>
<!-- /.main-container --> 
<!-- Admin Modal -->

<?php include_once('pages/footer.php');?>
<!-- page specific plugin scripts --> 
<script src="js/bootstrap-datepicker.min.js"></script> 
<script src="js/jquery.jqGrid.min.js"></script> 
<script src="js/grid.locale-en.js"></script> 
<!-- ace scripts --> 
<script src="js/ace-elements.min.js"></script> 
<script src="js/ace.min.js"></script> 
<script src="js/vendor/jquery.ui.widget.js"></script> 
<script src="js/jquery.iframe-transport.js"></script> 
<script src="js/jquery.fileupload.js"></script> 
<script src="js/sha512.js"></script> 
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script> 

<!-- Menu Toggle Script --> 
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
  	$(document).ready(function(e) {
		$('[data-toggle="tooltip"]').tooltip();
		$("#btn-save").click(function () {
		  $.ajax({
			type: "POST",
			url: "ajax/saveSettings.php",
			cache: false,
			data: $("#settings-form").serialize(),
			success: function (data) {
			  var response = JSON.parse(data);
			  alert(response.message);
			},
		  });
       });
	});
</script>
<script>
$(document).ready(function(e) {
    // Existing code...
    $('[data-toggle="tooltip"]').tooltip();
    $("#btn-save").click(function () {
        $.ajax({
            type: "POST",
            url: "ajax/saveSettings.php",
            cache: false,
            data: $("#settings-form").serialize(),
            success: function (data) {
                var response = JSON.parse(data);
                alert(response.message);
            },
        });
    });
    
    // NEW: Forbidden Words Search Functionality
    var originalWords = $('#bannedwords').val();
    var isFiltering = false;
    
    $('#word-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        var textarea = $('#bannedwords');
        
        if (searchTerm === '') {
            // Reset to original content
            textarea.val(originalWords);
            $('#search-results').text('');
            isFiltering = false;
            return;
        }
        
        var words = originalWords.split('\n').filter(function(word) {
            return word.trim() !== '';
        });
        
        var matchingWords = words.filter(function(word) {
            return word.toLowerCase().indexOf(searchTerm) !== -1;
        });
        
        if (matchingWords.length > 0) {
            textarea.val(matchingWords.join('\n'));
            $('#search-results').text('Found ' + matchingWords.length + ' word(s) matching "' + searchTerm + '"');
            isFiltering = true;
        } else {
            textarea.val('');
            $('#search-results').text('No words found matching "' + searchTerm + '"');
            isFiltering = true;
        }
    });
    
    $('#clear-search').click(function() {
        $('#word-search').val('');
        $('#bannedwords').val(originalWords);
        $('#search-results').text('');
        isFiltering = false;
    });
    
    // Warning when trying to save while filtering
    $("#btn-save").click(function (e) {
        if (isFiltering && $('#word-search').val().trim() !== '') {
            if (!confirm('You are currently filtering the forbidden words. This will save only the filtered results. Clear the search first to save all words. Continue?')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Update original words when textarea is manually modified (and not filtering)
    $('#bannedwords').on('input', function() {
        if (!isFiltering) {
            originalWords = $(this).val();
        }
    });
});
</script>

</body>
</html>