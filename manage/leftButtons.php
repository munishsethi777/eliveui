<?
    $page = basename($_SERVER['PHP_SELF']);
    $dashboard = null;
    $showStations = null;
    $mainUsers = null;
    $createUser = null;
    $showUsers = null;
    $mainRules = null;
    $createRule = null;
    $showRules = null;
    $showOccurencies = null;
    $editMeta = null;
    $editParams = null;
    $mainSettings = null;
    $changePassword = null;
    $changeEmail = null;

    if($page == "managerTabs.php"){
        $dashboard = "active";
    }elseif($page == "showFolders.php"){
        $showStations = "active";
    }elseif($page == "createUserForm.php"){
        $mainUsers = "active";
        $createUser = "active";
    }elseif($page == "showUsers.php"){
        $mainUsers = "active";
        $showUsers = "active";
    }elseif($page == "createHighValueRuleForm.php"){
        $mainRules = "active";
        $createRule = "active";
    }elseif($page == "showHighValueRules.php"){
        $mainRules = "active";
        $showRules = "active";
    }elseif($page == "showHighValueOccurences.php"){
        $showOccurencies = "active";
    }elseif($page == "editMeta.php"){
        $editMeta = "active";
    }elseif($page == "editParameter.php"){
        $editParams = "active";
    }elseif($page == "changePassword.php"){
        $mainSettings = "active";
        $changePassword = "active";
    }elseif($page == "changeEmailId.php"){
        $mainSettings = "active";
        $changeEmail = "active";
    }


?>


<nav class="navbar-default navbar-static-side" role="navigation">
        <div class="sidebar-collapse">
            <ul class="nav metismenu" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element"> <span>
                            <img alt="image" class="img-circle" src="images/profile_small.jpg" />
                             </span>
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold"><? echo $managerSession['username']?></strong>
                             </span> <span class="text-muted text-xs block">Manager<b class="caret"></b></span> </span> </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a href="profile.html">Profile</a></li>
                            <li><a href="contacts.html">Contacts</a></li>
                            <li><a href="mailbox.html">Mailbox</a></li>
                            <li class="divider"></li>
                            <li><a href="index.php">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        ELIVE
                    </div>
                </li>
                <li class="<?=$dashboard?>">
                    <a href="managerTabs.php"><i class="fa fa-dashboard"></i> <span class="nav-label">Dashboards</span> <span class="fa arrow"></span></a>
                </li>
                <li class="<?=$showStations?>"><a href="showFolders.php"><i class="fa fa-desktop"></i> <span class="nav-label">Show Stations</span></a></li>
                <li class="<?=$mainUsers?>">
                    <a href="#"><i class="fa fa-group"></i> <span class="nav-label">Manage Users</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$createUser?>"><a href="createUserForm.php">Create new User</a></li>
                        <li class="<?=$showUsers?>"><a href="showUsers.php">Show Users</a></li>
                    </ul>
                </li>

                <li class="<?=$mainRules?>">
                    <a href="#"><i class="fa fa-plug"></i> <span class="nav-label">High Value Rules</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$createRule?>"><a href="createHighValueRuleForm.php">Create new Rule</a></li>
                        <li class="<?=$showRules?>"><a href="showHighValueRules.php">Show Rules</a></li>
                    </ul>
                </li>
                <li class="<?=$showOccurencies?>">
                    <a href="showHighValueOccurences.php"><i class="fa fa-bell-o"></i><span class="nav-label">High Value Occurences</span></a>
                </li>
                <li class="<?=$editMeta?>"><a href="editMeta.php"><i class="fa fa-building-o"></i> <span class="nav-label">Edit Meta Information</span></a></li>
                <li class="<?=$editParams?>">
                    <a href="editParameter.php"><i class="fa fa-cog"></i> <span class="nav-label">Edit Parameters</span></a>
                </li>
                <li class="<?=$mainSettings?>">
                    <a href="#"><i class="fa fa-cogs"></i> <span class="nav-label">Settings</span>  <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li class="<?=$changePassword?>"><a href="changePassword.php">Change Password</a></li>
                        <li class="<?=$changeEmail?>"><a href="changeEmailId.php">Change Email</a></li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>


<div id="page-wrapper" class="gray-bg">
<div class="row border-bottom">
<nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom: 0">
<div class="navbar-header">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
</div>
    <ul class="nav navbar-top-links navbar-right">
        <li>
            <span class="m-r-sm text-muted welcome-message">Welcome to EnvirotechLive - Realtime Air & Water Monitoring.</span>
        </li>
        <li>
            <a href="login.html">
                <i class="fa fa-sign-out"></i> Log out
            </a>
        </li>
    </ul>

</nav>
</div>