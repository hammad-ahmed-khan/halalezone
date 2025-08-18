<style>
.user-icon {
    width: 30px; /* Adjust size as needed */
    height: 30px; /* Adjust size as needed */
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image fits well within the circle */
}
</style>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
        <a class="navbar-brand" href="http://halal-e.zone/"><img src="img/logo.png" height="45"></a>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <?php
    $myuser = cuser::singleton();
        $myuser->getUserData();
    ?>
    <div id="navbar" class="navbar-collapse collapse">
       <ul class="nav navbar-nav" id="mainMenu">
			<?php // if($myuser->userdata['dashboard']):?>
            <li id="dashItem"><a href=""><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
            <?php // endif;?>
            <?php if($myuser->userdata['application']):?>
            <li id="appItem"><a href="application"><i class="fa fa-handshake-o fa-fw"></i> Applications</a></li> 
            <?php endif;?>
            <?php // if($myuser->userdata['calendar']):?>
            <li id="calendarItem"><a href="calendar"><i class="fa fa-calendar-o fa-fw"></i> Calendar</a></li>
            <?php // endif;?>
            <?php if($myuser->userdata['products']):?>
            <li id="prodItem"><a href="products"><i class="fa fa-wrench fa-fw" ></i>Products</a></li>            
            <?php endif;?>
            <?php if($myuser->userdata['ingredients']):?>
            <li id="ingredItem"><a href="ingredients"><i class="fa fa-flask  fa-fw"></i>Ingredients</a></li>
            <?php endif;?>
            <?php if($myuser->userdata['documents']):?>
            <li id="qmItem"><a href="qm"><i class="ace-icon fa fa-file-text  fa-fw"></i>QM Documents</a></li>
            <?php endif;?>
            <?php if ($myuser->userdata['isclient'] == "1" && $myuser->userdata['company_id'] != "" && $myuser->userdata['company_admin'] == "1"): ?>
               <!-- <li><a href="branches"><i class="fa fa-wrench fa-fw" ></i>Branches</a></li> -->
            <?php endif;?> 
            <?php // if($myuser->userdata['canadmin'] || $myuser->userdata['isclient'] == "1"):?>
                <li id="helpDesk"><a href="tickets"><i class="fa fa-bug fa-fw"></i>Bug Report</a> 
            </li>

            <li id="customerService"><a href="customer_service"><i class="fa fa-envelope"></i>
             Support</a>
            </li>
            <?php // endif;?> 
            <?php if ($myuser->userdata['isclient'] == "2"):
                $db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
                $sql = 'SELECT COUNT(id) AS count FROM ttasks AS t WHERE t.status = 1 AND (t.user_id = :auditorId1 OR t.idauditor = :auditorId2)';

// Prepare the query
$rows = $dbo->prepare($sql);

// Bind the auditor ID parameter
$rows->bindParam(':auditorId1', $myuser->userdata['id'], PDO::PARAM_INT);
$rows->bindParam(':auditorId2', $myuser->userdata['id'], PDO::PARAM_INT);

// Execute the query
$rows->execute();

// Fetch the result
$totalRows = $rows->fetch(PDO::FETCH_ASSOC);

// Access the count of opened tasks
$openedTasksCount = $totalRows['count'];
 
                ?>
                <li><a href="tasks"><i class="fa fa-tasks fa-fw"></i> My Tasks (<?php echo $openedTasksCount; ?>)</a></li>
            <?php endif;?>

            <?php if($myuser->userdata['canadmin']):?>
                <li><a href="tasks"><i class="fa fa-tasks fa-fw"></i> Tasks</a></li>
            <?php endif;?>

            <?php if ($myuser->userdata['isclient'] != "1"): ?>
                <li id="training"><a href="training"><i class="fa fa-file-text fa-fw"></i> Activity Records</a></li>
            <?php endif;?>

            <?php
             if ($myuser->userdata['canadmin']):?>
            <li id="adminItem"><a class="dropdown-toggle" id="administration" data-toggle="dropdown" ><i class="fa fa-wrench fa-fw" ></i>Administration&nbsp;<i class="fa fa-caret-down"></i></a>
				<?php if($myuser->userdata['canadmin']):?>
                <ul class="dropdown-menu dropdown-admin">
                <?php if($myuser->userdata['canadmin'] == "1"):?>
                    <li><a href="administration"><?php if($myuser->userdata['superadmin'] == "1"):?>Clients<?php else: ?>Auditors<?php endif; ?></a></li>
                <?php endif;?>
                <li><a href="process_status">Process Status</a></li>
                    <li><a href="tasks">Assign Tasks</a></li>
                    <li><a href="paingreds">Pre-Approved Ingredients</a></li>
                    <li><a href="settings">Settings</a></li>
                </ul>
                <?php endif;?>
            </li>
            <?php endif;?>
           <?php if ($myuser->userdata['isclient'] == "1" && $myuser->userdata['parent_id'] == ""):?>
            <li id="adminItem"><a class="dropdown-toggle" id="administration" data-toggle="dropdown" ><i class="fa fa-cog fa-fw" ></i>Facility Settings&nbsp;<i class="fa fa-caret-down"></i></a>
			
                <ul class="dropdown-menu dropdown-admin">
                <li><a href="facilities">Facility Management</a></li>
                <li><a href="preferences">Facility Data Sharing Preferences</a></li>
                </ul>

            </li>
            <?php endif;?>            
            </ul>
            <ul class="nav navbar-nav navbar-right">
    <li>
        <a class="dropdown-toggle" id="usermenu" data-toggle="dropdown" href="#">
            <!-- User Icon -->
            <img src="/img/user-3296.png" alt="User Icon" class="user-icon">
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-user">
            <!-- User Name in Dropdown -->
            <li><div class="pointer" title=""><i class="fa fa-user fa-fw"></i> <span id="navUserName"><?php echo $_SESSION['halal']['user'];?></span></div></li>            
            <li><div class="pointer" id="logout" title="Log out from the system"><i class="fa fa-sign-out fa-fw"></i> Logout</div></li>
        </ul>
    </li>
</ul>

    </div>
</nav>