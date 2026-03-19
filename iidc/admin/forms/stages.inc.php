<?php
if (!defined("__HQC__")) {
    exit();
}; ?>
<h3 class="content_title">Application process</h3>
<style>
    #formsList li,
    .stageItem li {
        padding: 10px;
        cursor: pointer;
        height: 30px;
        position: relative;
    }

    #StagesList i {
        float: left;
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
?>
<div style="float:left;width:50%">
    <h3>Application & forms</h3>
    <div>
        <?php
        function getStageItems($stag)
        {
            global $applicationStages, $forms;
            if (isset($applicationStages[$stag]) and count($applicationStages[$stag]) > 0) {
                foreach ($applicationStages[$stag] as $foid) {
                    echo '<li data-foid="' . $foid . '" class="ui-sortable-handle"><strong>' . $forms[$foid]['form_id'] . '</strong> - ' . $forms[$foid]['form_name'] . '</li>';
                }
            }
        }
        $forms = array();
        if ($theForms = $hqcdb->get_results("SELECT * FROM hqc_forms where status!='example' and status!='deleted' order by form_id ASC ")) { ?>
            <ol class="alternateOn" id="formsList">
                <?php foreach ($theForms as $form) {
                    $forms[$form['foid']] = array('form_id' => $form['form_id'], 'form_name' => $form['form_name']);
                ?>
                    <li data-foid="<?php echo $form['foid']; ?>"><strong><?php echo $form['form_id']; ?></strong> - <?php echo $form['form_name']; ?></li>
                <?php }; ?>
            </ol>
        <?php }; ?>
    </div>
</div>
<div style="float:left;width:50%">
    <h3>Application process</h3>
    <div>
        <ul id="StagesList">
            <li data-stage="0"><i class="far fa-play-circle"></i><strong>New client</strong>
                <ol class="stageItem alternateOn">
                    <?php getStageItems(0); ?>
                </ol>
            </li>
            <li data-stage="1"><i class="fa fa-folder-open"></i><strong>Application</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(1); ?></ol>
            </li>
            <li data-stage="2"><i class="fa fa-file-signature"></i><strong>Financial offer &amp; contracting</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(2); ?></ol>
            </li>
            <li data-stage="3"><i class="fas fa-file-alt"></i><strong>Required documents</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(3); ?></ol>
            </li>
            <li data-stage="4"><i class="far fa-calendar-alt"></i><strong>Audit planing</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(4); ?></ol>
            </li>
            <li data-stage="5"><i class="fas fa-tasks"></i><strong>Audits</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(5); ?></ol>
            </li>
            <li data-stage="6"><i class="fa fa-gavel"></i><strong>Decision making</strong>
                <ol class="stageItem alternateOn"><?php getStageItems(6); ?></ol>
            </li>
        </ul>

    </div>
</div>

<script>
    jQuery(document).ready(function() {

        stageDeleteIcon = '<span style="position:absolute;right:10px"><i class="fa fa-trash-alt" onclick="deleteThisStageItem(this)" aria-hidden="true"></i></span>';

        $(".stageItem").each(function() {
            jQuery(this).find("li").each(function() {
                jQuery(this).append(stageDeleteIcon);
            });
        })

        $("#formsList li,#StagesList li").draggable({
            connectToSortable: ".stageItem",
            cursor: "move",
            revert: true
        });

        jQuery(".stageItem").sortable({
            revert: true
        });

    })

    function doSaveStages(stages) {
        jQuery.ajax({
            url: "/admin/forms/save-stages.php",
            type: "POST",
            data: {
                stages: stages
            },
            success: function(data) {
                console.log(data);
            }
        });

    }

    function saveStages() {
        $(".stageItem").on("sortstop", function(event, ui) {
            stages = [];
            jQuery.when($(".stageItem").each(function() {
                stage = jQuery(this).parent("li").data("stage");
                stages[stage] = [];

                jQuery(this).find("li").each(function() {
                    var foid = jQuery(this).data("foid");
                    stages[stage].push(foid);

                });
            })).then(doSaveStages(stages));
        });
    }

    function deleteThisStageItem(item) {
        jQuery(item).closest("li").remove();
        saveStages();
    }
</script>