<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tasks</title>
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
<div>
  <p>There are confimred actions:</p>
  <table border="1" cellspacing="0">
    <thead>
      <th width="20">Date</th>
      <th width="20">Item code</th>
      <th width="20">Name</th>
      <th width="40">Action</th>
    </thead>
    <tbody>
      <?php
        foreach($data['actions'] as $item) {
          $code = ($item['itemtype'] == 'ingredients' ? 'RMC_' : "HCP_" ) . $item['itemid'];
          echo "<tr><td>".date('j M Y', strtotime($item['created_at']))."</td>".
          '<td>'.$code.'</td>'.
          '<td>'.$item['itemname'].'</td>'.
          "<td>".$item['action']."</td></tr>";
        }
        ?>
    </tbody>
  </table>
</div>
</body>
</html>
