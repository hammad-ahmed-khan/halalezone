<?php
if (!session_id()) {
  session_start();
}
include "../../checkuser.inc.php";
?>
<html>
<head>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
    var data_url = '/data/';
  </script>
  <link rel="stylesheet" href="/style.css">
  <?php if (!isset($_REQUEST['act'])) { ?>
    <link rel="stylesheet" href="/scripts/signature/css/signature-pad.css?ver=5">
  <?php }; ?>
  <style>
    .file,
    button,
    .button {
      font-size: 11px;
      padding: 2px 15px !important;
      margin: 0px;
    }

    div#signature_image {
      background-repeat: no-repeat !important;
      background-size: contain !important;
      background-position: center !important;
    }
  </style>
</head>
<?php
foreach (array('admin' => 'uid', 'office' => 'offid', 'client' => 'clid', 'auditor' => 'uid','committee'=>'comemid') as $key => $value) {
  if (isset($_REQUEST[$value])) {
    $user_au_nr = $value;
    $user_type = $key;
    $user_id = $_REQUEST[$value];
    break;
  }
}
?>
<?php if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'upload') { ?>
  <script src="/scripts/post-form.js"></script>
  <script src="/scripts/tools.js"></script>
  <script>
    function getImage(e) {
      $("#signature_image").css("background-image", "none");
      $("#use_signature").css("display", "none");
      if (jQuery("input[name=signature]").val() != '') {
        var reader = new FileReader();
        reader.onload = function(e) {
          $("#signature_image").css("background-image", 'url(' + e.target.result + ')');
        }
        reader.readAsDataURL(e.files[0]);
        $("#use_signature").css("display", "");
      }
    }
  </script>

  <body style="background-image:none; background-color: #efefef;overflow:hidden">
    <form action="signature_save.php" id="userBackground" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="act" value="upload_signature" />
      <input type="hidden" name="uid" value="<?php echo $user_id; ?>" />
      <input type="hidden" name="user_type" value="<?php echo $user_type; ?>" />
      <input type="hidden" name="signature_input" value="<?php echo str_replace('_holder_', '_', $_REQUEST['sg']); ?>" />
      <input type="hidden" name="signature_holder" value="<?php echo $_REQUEST['sg']; ?>" />
      <?php if (isset($_REQUEST['foid'])) { ?>
        <input type="hidden" name="foid" value="<?php echo $_REQUEST['foid']; ?>" />
      <?php }; ?>
      <div style="height:200px;background:white;margin:10px;position:relative" id="signature_image"></div>
      <div style="padding:0px 10px;text-align:center">
        <label><input type="checkbox" name="save_signature" value="yes"> Save the signature
          for future documents</label>
      </div>
      <div style="padding:0px 10px;text-align:center">

        <input type="file" name="signature" class="file" accept=".png, .jpg,.jpeg" onchange="getImage(this)" data-require="yes" style="width:100px;padding:0px !important;">
        <input type="submit" class="button" value="Use signature" id="use_signature" style="display: none;" />
        <button type="button" onclick="top.closePopup()">cancel</button>

      </div>
    </form>
  </body>

</html>
<?php return;
}; ?>

<body onselectstart="return false" id="signature-pad-body">
  <input type="hidden" name="uid" value="<?php echo $user_id; ?>" />
  <input type="hidden" name="user_type" value="<?php echo $user_type; ?>" />
  <input type="hidden" name="signature_input" value="<?php echo str_replace('_holder_', '_', $_REQUEST['sg']); ?>" />
  <input type="hidden" name="signature_holder" value="<?php echo $_REQUEST['sg']; ?>" />
  <?php if (isset($_REQUEST['foid'])) { ?>
    <input type="hidden" name="foid" value="<?php echo $_REQUEST['foid']; ?>" />
  <?php }; ?>
  <div id="signature-pad" class="signature-pad" style="height: 460px">
    <div class="signature-pad--body">
      <canvas></canvas>
    </div>
    <div class="signature-pad--footer">
      <div class="description"><span id="signatureError" style="position: absolute;right: 25px;color: red;width:300px"></span> Sign
        above</div>
      <div style="text-align: left;color:black"> <label><input type="checkbox" name="save_signature" id="save_signature" value="yes"> Save the signature
          for future documents</label></div>
      <div class="signature-pad--actions">
        <div>
          <button type="button" class="button clear" data-action="clear">Clear</button>
          <button type="button" class="button" data-action="change-color">Black pen</button>
          <button type="button" class="button" data-action="undo">Undo</button>

        </div>
        <div>
          <button type="button" class="button save" data-action="save-png">Download signature</button>
          <button type="button" class="button save" data-action="save-svg">Use signature</button>
          <button type="button" onclick="top.closePopup()">cancel</button>
        </div>
      </div>
    </div>
  </div>


  <script src="/scripts/signature/js/signature_pad.umd.js?ver=1"></script>
  <script src="/scripts/signature/js/app.js?ver=<?php echo time(); ?>"></script>
</body>

</html>