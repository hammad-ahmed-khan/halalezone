<div class=title>
    <form action="index.php" method=get name="adrsform">
        <input type="hidden" name="inc" value="clients" />
        <b>Search for:</b>
        <input type=text size=8 name="srch_wht" size="40px" value="<?php echo (isset($srch_wht)) ? $srch_wht : ""; ?>">
        <select size="1" name="srch">
            <option value="company_name">Company</option>
            <option value="Contact">Contact Person</option>
            <option value="country">Country</option>
            <option value="clid">ID</option>
        </select>
        <input type="submit" value="Search">
    </form>123
</div>