<?php if (!defined("_HQC_")) {
    exit();
};

?>
<style>
    th {
        max-width: none;
    }
</style>
<script>
    function addNewExpenseType() {
        time = new Date().getTime();
        var srNr = jQuery("#expensesList tr").length + 1;
        var newRow = '<tr><th><i class="fas fa-arrows-alt"></i></th><td><input type="text" name="expense[' + time + '][code]" value="EXP-' + srNr + '" required /></td><td><input type="text" name="expense[' + time + '][description]" style="width:100%" value="" required /></td><td style="text-align:center;"><i class="fa fa-trash-alt" onclick="deleteExpenseType(this)"></i></td></tr>';
        jQuery("#expensesList").append(newRow);
        doSortable();
    }

    function deleteExpenseType(el) {
        alert_confirm("Are you sure you want to delete this expense type?");
        jQuery("button#alertYesBtn").click(function() {
            close_alert();
            jQuery(el).closest("tr").remove();
        })

    }
    jQuery(document).ready(function() {
        doSortable();
    });

    function doSortable() {
        jQuery("i.fas.fa-arrows-alt").on("mousedown", function(e) {
            jQuery("#expensesList").sortable({
                stop: function(event, ui) {
                    // Destroy sortable after drop
                    $(this).sortable('destroy');
                }
            })
        });
    }
</script>
<h2 style="text-align:center">Predefined Expenses</h2>
<form action="expenses_save.php" method="post" id="expensesForm" onsubmit="return post_this_form(this);">
    <input type="hidden" name="act" value="save_expense" />
    <table class="table table-striped table-bordered" style="min-width:auto">
        <thead>
            <tr>
                <th style="width:20px">::</th>
                <th>Item code</th>
                <th style="width:500px">Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="expensesList">
            <?php
            if ($expense_type = json_decode(get_option('expense_type'), true)) {
                $srNr = 0;
                $time = time();
                foreach ($expense_type as $key => $type) {
                    $srNr++;
                    if (isset($type['name']))
                        $type['description'] = $type['name']; ?>
                    <tr>
                        <th><i class="fas fa-arrows-alt"></i></th>
                        <td><input type="text" name="expense[<?php echo $key; ?>][code]" value="<?php echo isset($type['code']) ? $type['code'] : 'EXP-' . $srNr; ?>" required <?php echo (isset($type['code']) && $type['code'] == 'KM') ? 'readonly' : ''; ?> /></td>
                        <td><input type="text" name="expense[<?php echo $key; ?>][description]" style="width:100%" value="<?php echo isset($type['description']) ? $type['description'] : ''; ?>" required /></td>
                        <td style="text-align:center;">
                            <i class="fa fa-trash-alt" onclick="deleteExpenseType(this)"></i>
                        </td>
                    </tr>
            <?php
                }
            } ?>
        </tbody>
    </table>
    <div style="text-align:center">
        <button type="button" onclick="addNewExpenseType()">Add new expense type</button>
        <input type="submit" name="save" value="Save" />
    </div>
</form>