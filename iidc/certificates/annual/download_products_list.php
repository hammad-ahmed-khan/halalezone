<?php
include "../../check_user.inc.php";
if (!isset($_SESSION['username']) or!isset($_REQUEST['clid']) or!isset($_REQUEST['crtNr']))
    exit();

include "$prog_path/config/connect.inc.php";
?>
<form method="post" action="products_list.excel.php" name="downloadExcel" target="fIframe">
    <input type="hidden" name="clid" value="<?php echo $_GET['clid']; ?>">
    <input type="hidden" name="crtNr" value="<?php echo $_GET['crtNr']; ?>">
    <table class="alternate">
        <tr>
            <th class="sub_title" colspan="2">
                <center>Download Certificate products list as Excel file</center>
            </th>
        </tr>
        <tr>
            <th>Products list order:</th>
            <td>
                <select name="order_by" size="1">
                    <option value="">As uploaded</option>
                    <option value="article_nr">Order by article Code</option>
                    <option value="product_name">Order by product Name</option>
                </select>
                <label><input type="checkbox" name="excel_url">Get file link</label>
            </td>
        </tr>
        <tr>
            <td class="sub_title" colspan="2">
                <center><input type="submit" value="Download" /></center>
            </td>
        </tr>
    </table>
</form>