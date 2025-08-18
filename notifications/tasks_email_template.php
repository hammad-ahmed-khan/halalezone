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
  <p>There are some tasks pending:</p>
  <table border="1" cellspacing="0">
    <thead>
      <th width="5">Date</th>
      <th width="5">RMC_ID</th>
      <th width="10">Name</th>
      <th width="10">Supplier</th>
      <th width="10">Producer</th>
      <th width="30">Deviation</th>
      <th width="30">Measure</th>
    </thead>
    <tbody>
      <?php
        foreach($data['tasks'] as $item) {
          echo "<tr><td>".date('j M Y', strtotime($item['created_at']))."</td>".
          "<td>RMC_".$item['idingredient']."</td>".
          "<td>".$item['name']."</td>".
          "<td>".$item['supplier']."</td>".
          "<td>".$item['producer']."</td>".
          "<td>".$item['deviation']."</td>".
          "<td>".$item['measure']."</td></tr>";
        }
        ?>
    </tbody>
  </table>
</div>
</body>
</html>
