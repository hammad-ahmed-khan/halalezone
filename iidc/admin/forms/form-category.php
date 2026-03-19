<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
?>
<style>
    .formCategoryHolder input {
        height: 22px;
        line-height: 11px;
    }
</style>
<script>
    function addNewCategory() {

        inputs = jQuery(".formCategoryHolder ol li").last().find('input').data("id") + 1;
        jQuery(".formCategoryHolder ol").append('<li><input type="text" data-id="' + inputs + '" name="cat[' + inputs + ']"/><i class="fa fa-trash-alt"></i></li>');
        deleteCategory()
    }

    var selectedCategory;

    function doDeleteCategory() {
        jQuery(selectedCategory).parent('li').remove();
    }

    function deleteCategory() {
        jQuery(".formCategoryHolder ol li .fa-trash-alt").each(function() {
            jQuery(this).on("click", function() {
                selectedCategory = this;
                alert_confirm('Delete category?', 'doDeleteCategory()')
            })
        })
    }
</script>
<div><strong> Application / form category</strong></div>
<div style="padding:10px" class="formCategoryHolder">
    <form method="post" action="<?php echo this_url(); ?>/form-category-save.php" onsubmit="return post_this_form(this);">
        <?php
            $categories = array(1 => 'Applications', 2 => 'Contractual Arrangements', 3 => 'E-Matrix');
        if ($categories = get_hqc_options('form_categories')) {
            if(is_array(json_decode($categories,true)))
            $categories = json_decode($categories,true);
        }
        ?> <ol>
            <?php foreach ($categories as $key => $value) { ?>
                <li style="padding: 2px;"><input type="text" data-id="<?php echo $key; ?>" name="cat[<?php echo $key; ?>]" value="<?php echo $value; ?>" /><i class="fa fa-trash-alt"></i></li>
            <?php }; ?>
        </ol>
        <div style="text-align:center">
            <input type="button" onclick="addNewCategory()" value="Add new" /><input type="submit" value="Save" />
        </div>
    </form>
</div>
<script>
    deleteCategory()
</script>