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
<?php if(count($data['one_week']) > 0 ): ?>
<div>
    <p>The following ingredients will expire in 1 week:</p>
    <ul>
        <?php
            $expDate = new DateTime();
            foreach($data['one_week'] as $item) {
                $expDate->setTimestamp(strtotime($item['halalexp']));
                echo "<li>RMC_".$item['id'].", ".$item['name'].", ".$item['rmcode'].", ".$item['supplier'].", ".date_format($expDate,"d.m.Y")."</li>";
            }
        ?>
    </ul>
</div>
<?php endif;?>

<?php if(count($data['four_week']) > 0 ): ?>
<div>
    <p>The following ingredients will expire in 4 weeks:</p>
    <ul>
        <?php
        $expDate = new DateTime();
        foreach($data['four_week'] as $item) {
            $expDate->setTimestamp(strtotime($item['halalexp']));
            echo "<li>RMC_".$item['id'].", ".$item['name'].", ".$item['rmcode'].", ".$item['supplier'].", ".date_format($expDate,"d.m.Y")."</li>";
        }
        ?>
    </ul>
</div>
<?php endif;?>

<?php if(count($data['eight_week']) > 0 ): ?>
<div>
    <p>The following ingredients will expire in 8 weeks:</p>
    <ul>
        <?php
        $expDate = new DateTime();
        foreach($data['eight_week'] as $item) {
            $expDate->setTimestamp(strtotime($item['halalexp']));
            echo "<li>RMC_".$item['id'].", ".$item['name'].", ".$item['rmcode'].", ".$item['supplier'].", ".date_format($expDate,"d.m.Y")."</li>";
        }
        ?>
    </ul>
</div>
<?php endif;?>
</body>
</html>