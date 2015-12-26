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