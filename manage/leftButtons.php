<?session_start(); 
$managerSession = $_SESSION["managerSession"];?>
<!--Div class="leftButtonPanel">
         <p style="padding:10px 10px 10px 10px;">
                <label class="ui-state-default ui-priority-secondary leftButton">Welcome <? echo $managerSession['username']?></label>
                <label class="ui-state-default ui-priority-secondary leftButton">USERS</label>
                    <a href="CreateUserForm.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Create New User</a>
                    <a href="showUsers.php" id="dialog_link" class="ui-state-default ui-corner-all leftChildButton leftButton">View Saved Users</a>
                <label class="ui-state-default ui-priority-secondary leftButton">FOLDERS</label>
                    <a href="showFolders.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Folders</a>
                <label class="ui-state-default ui-priority-secondary leftButton">ADMIN CONFIGURATION</label>
                    <a href="ChangePassword.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Change password</a>
                    <a href="changeEmailId.php" id="dialog_link" class="ui-state-default ui-corner-all leftChildButton leftButton">Change Email</a>
                 <label class="ui-state-default ui-priority-secondary leftButton">HIGH VALUE RULES</label>
                    <a href="CreateHighValueRuleForm.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Create High Value Rule</a>
                    <a href="showHighValueRules.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">View High Value Rule</a>
                    <a href="showHighValueOccurences.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">View High Value Occurences</a>
                    <label class="ui-state-default ui-priority-secondary leftButton">META INFORMATION</label>
                    <a href="editMeta.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Edit Metas</a>
                    <a href="editParameter.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Edit Parameter</a>
          </p>
</Div-->


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
                            <li><a href="login.html">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        IN+
                    </div>
                </li>
                <li class="active">
                    <a href="index.html"><i class="fa fa-users"></i> <span class="nav-label">Dashboards</span> <span class="fa arrow"></span></a>
                </li>
                <li>
                    <a href="#"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Users</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="CreateUserForm.php">Create New</a></li>
                        <li><a href="showUsers.php">Users</a></li>
                    </ul>
                </li>
                
               
                <li>
                <a href="showFolders.php"><i class="fa fa-folders"></i> <span class="nav-label">Folders</span></a>
                </li>
                <li>
                    <a href="#"><i class="fa fa-desktop"></i> <span class="nav-label">Settings</span>  <span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="ChangePassword.php">Change Password</a></li>
                        <li><a href="changeEmailId.php">Change Email</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-files-o"></i> <span class="nav-label">High Value Rules</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="CreateHighValueRuleForm.php">Create New</a></li>
                        <li><a href="showHighValueRules.php">Rules</a></li>
                        <li><a href="showHighValueOccurences.php">Occurences</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#"><i class="fa fa-globe"></i> <span class="nav-label">Meta Information</span><<span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="editMeta.php">Edit Meta</a></li>
                        <li><a href="editParameter.php">Edit Parameters</a></li>
                    </ul>
                </li>
                
            </ul>

        </div>
    </nav>