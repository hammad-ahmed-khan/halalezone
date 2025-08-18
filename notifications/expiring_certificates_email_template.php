<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
<div style="background-color: #f0f0f0; padding: 5px 10px 1px 10px">
    <table>
        <tr>
            <td><img src="cid:logo" style="width: 50px; height: 50px"></td>
            <td>
                <div  style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding-left: 20px; font-size: 18px">HALAL eZone</div>
             </td>
        </tr>
    </table>
</div>
<?php if(count($data['expired']) > 0 ): ?>
<div>
    <p>The following certificates</p>
    <p>expired:</p>
    <ul>
        <?php
            foreach($data['expired'] as $item) {
                echo "<li>".$item['filename']."</li>";
            }
        ?>
    </ul>
</div>
<?php endif;?>
<?php if(count($data['30_days']) > 0 ): ?>
    <div>
        <p>will expire in <b>30</b> days:</p>
        <ul>
            <?php
            foreach($data['30_days'] as $item) {
                echo "<li>".$item['filename']."</li>";
            }
            ?>
        </ul>
    </div>
<?php endif;?>
<?php if(count($data['60_days']) > 0 ): ?>
    <div>
        <p>will expire in <b>60</b> days:</p>
        <ul>
            <?php
            foreach($data['60_days'] as $item) {
                echo "<li>".$item['filename']."</li>";
            }
            ?>
        </ul>
    </div>
<?php endif;?>
<?php if(count($data['90_days']) > 0 ): ?>
    <div>
        <p>will expire in <b>90</b> days:</p>
        <ul>
            <?php
            foreach($data['90_days'] as $item) {
                echo "<li>".$item['filename']."</li>";
            }
            ?>
        </ul>
    </div>
<?php endif;?>
</body>
</html>
