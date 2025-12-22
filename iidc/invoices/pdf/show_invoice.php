<?php
foreach ($_GET as $key => $value) {
$$key = $value;
}

include "show_invoice_".$st.".inc.php";
?>