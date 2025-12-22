<?php
if ($_SESSION['user_role'] == "super_admin") {
?>
    <script>
        $("#page_title").html("Test - Email address")
    </script>
    <?php
    if (!$email = get_option('test_email_address'))
        $email = 'info@' . $_SERVER['HTTP_HOST'];
    ?>
    <form action="test_email_save.php" method="post" name="invoice_defaults" id="invoice_defaults" onSubmit="return post_this_form(this)">
        <table class="alternate" style="margin-top:20px;min-width:auto">
            <tr>
                <th colspan="3" style="text-align:center">Test - Email address</th>
            </tr>
            <tr>
                <th>Email address:</th>
                <td><input type="text" name="test_email_address" value="<?php echo $email; ?>" /></td>
                <td><input type="submit" value="Save" /></td>
            </tr>
            <tr>
                <td colspan="3"><i>This email address will be used only for (hqc-test).<br />All emails going out from (hqc-test) will be sent to this email address. <br />No email goes out to a client.</i></td>
            </tr>
        </table>
    </form>
<?php }; ?>