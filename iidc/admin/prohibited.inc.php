<?php
if (!defined("__HQC__")) {
    exit();
};
?>
<script>
    $("#page_title").html("Prohibited words");
</script>
<form action="prohibited_save.php" method="post" name="prohibited_words_form" id="prohibited_words_form" onsubmit="post_this_form(this)">
    <table style="width:400px;min-width:0px">
        <thead>
            <tr>
                <th>Prohibited words</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <textarea name="prohibited_words" id="prohibited_words" style="width: 100%; height: 300px;overflow:auto"><?php echo str_replace(",", "\r\n", get_option('prohibited_words')); ?></textarea>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <input type="submit" value="Save" /> <i>One word per line</i>
                </td>
            </tr>
    </table>
</form>