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
<?php if(count($data['data']) > 0 ): ?>
<div>
    <p>There are new certificates uploaded:</p>
    <ul>
        <?php
            foreach($data['data'] as $item) {
                echo "<li><a href='".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos( $_SERVER['REQUEST_URI'], '/'))."/../".$item['url']."'>".$item['filename']."</li>";
            }
        ?>
    </ul>
</div>
<?php endif;?>
</body>
</html>