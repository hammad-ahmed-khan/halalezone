<?php
if (!defined("__HQC__")) {
    exit();
}; ?>
<h3 class="content_title">Application process</h3>

<?php
if (!$stages_menu = json_decode(file_get_contents(user_path . "/applications/applications.menu.php"), true))
    return;
if (!isset($_GET['stage']))
    $_GET['stage'] = 0;
$stages = array();
foreach ($stages_menu as $stageKey => $stageValue) {
    if (strstr($stageKey, 'stage-')) {
        if ('stage-' . $_GET['stage'] == $stageKey)
            $stageActive = ' active-stage';
        else
            $stageActive = '';

        $stages[] = '<span class="stageIcon"><i class="' . (isset($stageValue['stage-icon']) ? $stageValue['stage-icon'] : $stageValue['icon']) . $stageActive . '"></i></span><span class="stageText">' . (isset($stageValue['stage-text']) ? $stageValue['stage-text'] : $stageValue['text']) . '</span>';
    }
};
?>

<style>
    #stageTools i {
        margin: 5px 10px 0px 0px;
    }

    #stageTools a.active .stageText {
        color: red;
        font-weight: bold;
    }

    #contactPersonDetails strong {
        display: inline-block;
        width: 90px;
        border-bottom: 1px dashed #bbb;
        line-height: 24px;
    }

    #stageTools ul li {
        float: left;
        padding: 10px 20px;
        text-align: center;
        border-bottom: 4px solid var(--color100);
        position: relative;

    }

    #stageTools ul li span {
        display: block;
        text-align: center;
        position: relative;
    }

    #stageTools ul li span.stageIcon {
        display: inline-block;
        background: var(--color50);
        border: 1px solid var(--color20);
        border-radius: 150px;
        padding: 20px;
        text-align: center;
        width: 90px;
        height: 90px;
    }

    #stageTools ul li.stageInProcess {
        border-bottom: 4px solid var(--color50);
    }

    #stageTools ul li.currentStage span.stageIcon {
        background: brown;
    }

    #stageTools ul li.doneStage span.stageIcon {
        background: darkgreen;
    }

    #stageTools ul li.stageInProcess span.stageIcon {
        background: var(--color20);
    }

    #stageTools ul li span.stageIcon:after {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 150px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 1px solid var(--color20);
    }

    #stageTools ul li i {
        font-size: 50px !important;
        margin: 0px !important;
        position: absolute;
        /* place it in the middle horizontal and vertical */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* align ul in the center */
    #stageTools>ul {
        display: flex;
        justify-content: center;
    }

    li.active:after {
        /*content awesome arrow up*/
        content: "\f0d8";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        font-size: 40px;
        position: absolute;
        color: var(--color100);
        bottom: -5px;
        left: 50%;
        transform: translate(-50%, 0%);
    }
</style>

<script>
    jQuery(document).ready(function() {
        jQuery(".content_title").append(' <span style="color: #900">(Stage <?php echo $_GET['stage']; ?>/6)</span>')
    })
</script>

<div style="margin-top: 20px;text-align:center;padding:5px" id="stageTools">
    <ul>
        <?php foreach ($stages as $stage => $stageIcon) {
            if (strstr($stageIcon, 'active-stage'))
                $stageActive = 'active';
            else
                $stageActive = '';
        ?>
            <?php if ($stage == $_GET['stage']) { ?>
                <li class="currentStage <?php echo $stageActive; ?>"><a href="?inc=applications-process&stage=<?php echo $stage; ?>" <?php echo $stageActive; ?>><?php echo str_replace('<i', '<i style="color:bisque"', $stageIcon); ?></a></li>
            <?php } else { ?>
                <li class=""><a href="?inc=applications-process&stage=<?php echo $stage; ?>" <?php echo $stageActive; ?>><?php echo str_replace('<i', '<i style="color:"', $stageIcon); ?></a></li>
        <?php };
        }; ?>
    </ul>
</div>



<style>
    .stageItem {
        min-height: 100px;
        background: beige
    }

    #formsList li,
    .stageItem li {
        padding: 10px;
        cursor: pointer;
        height: 30px;
        position: relative;
    }

    #StagesList ol {
        margin-bottom: 20px;
    }

    #StagesList i {
        font-size: 14px !important;
    }

    #StagesList strong {
        display: block;
        float: left;
        text-transform: uppercase;
        color: var(--color100);
    }

    #StagesList ol {
        clear: both;
        min-height: 40px;
        width: 95%;
        padding-right: 20px;
    }
</style>
<?php if (!$applicationStages = json_decode(get_hqc_options('applicationStages'), true))
    $applicationStages = array();
$selectedStages = array();
if (count($applicationStages) > 0) {
    foreach ($applicationStages as $stageK => $stageV) {
        foreach ($stageV as $sv) {
            $selectedStages[explode('-', $sv)[0]] = $stageK;
        }
    }
};
$stageIcons = '<span style="position:absolute;right:10px" class="StagesListIcons"><i class="fa fa-eye" onclick="viewForm(this)"></i><label><input type="checkbox" class="hqcOnly" id="form_[foid]" onclick="saveStages()" [checkedStatus]>HQC office only</label>  <i class="fa fa-trash-alt" onclick="deleteThisStageItem(this)" aria-hidden="true"></i></span>';
?>
<div style="float:left;width:50%">
    <h3>Application & forms</h3>
    <div>
        <?php
        function getStageItems($stag)
        {
            global $applicationStages, $forms, $stageIcons;
            if (isset($applicationStages[$stag]) and count($applicationStages[$stag]) > 0) {
                foreach ($applicationStages[$stag] as $foidValue) {
                    $foid = explode('-', $foidValue)[0];
                    $checkedStatus = explode('-', $foidValue)[1];
                    if ($checkedStatus == 'true')
                        $checkedStatus = 'checked';
                    else
                        $checkedStatus = '';
                    echo '<li data-foid="' . $foid . '" class="ui-sortable-handle"><strong>' . $forms[$foid]['form_id'] . '</strong> - ' . $forms[$foid]['form_name'] . str_replace(array('[checkedStatus]', '[foid]'), array($checkedStatus, $foid), $stageIcons) . '</li>';
                }
            }
        }
        $forms = array();
        if ($theForms = $hqcdb->get_results("SELECT * FROM hqc_forms where status!='example' and status!='deleted' order by form_id ASC ")) { ?>
            <ol class="alternateOn" id="formsList" style="padding-right:40px">
                <?php foreach ($theForms as $form) {
                    $forms[$form['foid']] = array('form_id' => $form['form_id'], 'form_name' => $form['form_name']);
                    if (!isset($selectedStages[$form['foid']])) {
                ?>
                        <li data-foid="<?php echo $form['foid']; ?>">
                            <span style="float:right" class="viewFormEye">
                                <i class="fa fa-edit" onclick="editThisForm(this)"></i>
                                <i class="fa fa-eye" onclick="viewForm(this)"></i></span>
                            <strong><?php echo $form['form_id']; ?></strong> - <?php echo $form['form_name']; ?>

                        </li>
                <?php
                    };
                }; ?>

            </ol>
        <?php }; ?>
    </div>
</div>
<?php
if (isset($stages_menu['stage-' . $_GET['stage']]['stage-text']))
    $stage_text = $stages_menu['stage-' . $_GET['stage']]['stage-text'];
else
    $stage_text = $stages_menu['stage-' . $_GET['stage']]['text'];
?>
<div style="float:left;width:50%">
    <h3><?php echo $stage_text; ?></h3>
    <div>
        <ol class="stageItem alternateOn">
            <?php getStageItems($_GET['stage']); ?>
        </ol>
    </div>
    <div style="margin-top:20px">
        <script>
            function addEditInitialMessage(act) {
                if (act == 'add') {
                    jQuery('#initialMessage').html('<textarea style="width:100%" id="initialMessageText" placeholder="Enter message here..."></textarea><div style="margin-top:10px"><button class="button button-primary" onclick="saveInitialMessage()">Save</button></div>');
                } else {
                    text = jQuery('#initialMessage').html().trim().replace(/<br>/g,"\n");
                    jQuery('#initialMessage').html('<textarea style="width:100%" id="initialMessageText" placeholder="Enter message here..." >' + text + '</textarea><div style="margin-top:10px"><button class="button button-primary" onclick="saveInitialMessage()">Save</button></div>');
                }
            }

            function saveInitialMessage() {
                var message = jQuery('#initialMessageText').val();
                jQuery.ajax({
                    url: "/admin/forms/applications-process-save.php",
                    type: "POST",
                    data: {
                        initialMessage: message,
                        stage: '<?php echo $_GET['stage']; ?>',
                        act: 'saveInitialMessage'
                    },
                    success: function(data) {
                        location.reload();
                    }
                });
            }
        </script>
        <?php
        if (!$applicationInitialMessages = decode_json(get_hqc_options('applicationInitialMessages'), true))
            $applicationInitialMessages = array();
        ?>
        <h4>
            <span style="float:right">
                <?php if (isset($applicationInitialMessages[$_GET['stage']])) { ?>
                    <a style="text-transform: capitalize;font-weight:normal" onclick="addEditInitialMessage('edit')">Edit message</a>
                <?php } else { ?>
                    <a style="text-transform: capitalize;font-weight:normal" onclick="addEditInitialMessage('add')">Add message</a>
                <?php }; ?></span>
            Initial message
        </h4>
        <div id="initialMessage">
            <?php
            if (isset($applicationInitialMessages[$_GET['stage']]))
                echo str_replace("\n","<br>",$applicationInitialMessages[$_GET['stage']]);
            else
                echo 'No message set for this stage';
            ?>
        </div>
    </div>
</div>

<script>
    function editThisForm(item) {
        foid = jQuery(item).closest("li").data('foid');
        url = '?inc=form-maker&foid=' + foid;
        window.open(url);
    }

    function viewForm(item) {
        foid = jQuery(item).closest("li").data('foid');
        loadPdf('<?php echo this_url(); ?>/view_form.php?clid=5510&foid=' + foid);
    }

    stageDeleteIcon = '<?php echo $stageIcons; ?>';
    var stage = '<?php echo $_GET['stage']; ?>';

    function appendTools() {
        $(".stageItem").each(function() {
            jQuery(this).find("li").each(function() {
                jQuery(this).find(".viewFormEye").remove();
                if (jQuery(this).find(".StagesListIcons").length == 0)
                    jQuery(this).append(stageDeleteIcon);
            });
        })
    }
    jQuery(document).ready(function() {
        appendTools();
        $("#formsList li,#StagesList li").draggable({
            connectToSortable: ".stageItem",
            cursor: "move",
            revert: true
        });

        jQuery(".stageItem").sortable({
            revert: true,
            stop: function(event, ui) {
                appendTools();
                saveStages();
            }
        });

    })

    function doSaveStages(stages) {
        //remove empty array item
        stages = stages.filter(function(el) {
            return el != null;
        });

        jQuery.ajax({
            url: "/admin/forms/applications-process-save.php",
            type: "POST",
            data: {
                stages: stages,
                stage: stage
            },
            success: function(data) {
                console.log(data);
            }
        });

    }

    function saveStages() {
        stages = [];
        console.log($(".stageItem li").length)
        jQuery.when($(".stageItem li").each(function() {
            var foid = jQuery(this).data("foid");
            //jquery check if checkbox is checked
            var hqc = jQuery(this).find(".hqcOnly")[0].checked;
            if (foid)
                stages.push(foid + '-' + hqc);

        })).then(doSaveStages(stages));
    }

    function deleteThisStageItem(item) {
        jQuery(item).closest("li").remove();
        saveStages();
        location.reload();
    }
</script>