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
                    <a href="managerTabs.php"><i class="fa fa-users"></i> <span class="nav-label">Dashboards</span> <span class="fa arrow"></span></a>
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
                    <a href="#"><i class="fa fa-globe"></i> <span class="nav-label">Meta Information</span><span class="fa arrow"></span></a>
                    <ul class="nav nav-second-level collapse">
                        <li><a href="editMeta.php">Edit Meta</a></li>
                        <li><a href="editParameter.php">Edit Parameters</a></li>
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
            <span class="m-r-sm text-muted welcome-message">Welcome to INSPINIA+ Admin Theme.</span>
        </li>
        <li class="dropdown">
            <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                <i class="fa fa-envelope"></i>  <span class="label label-warning">16</span>
            </a>
            <ul class="dropdown-menu dropdown-messages">
                <li>
                    <div class="dropdown-messages-box">
                        <a href="profile.html" class="pull-left">
                            <img alt="image" class="img-circle" src="img/a7.jpg">
                        </a>
                        <div>
                            <small class="pull-right">46h ago</small>
                            <strong>Mike Loreipsum</strong> started following <strong>Monica Smith</strong>. <br>
                            <small class="text-muted">3 days ago at 7:58 pm - 10.06.2014</small>
                        </div>
                    </div>
                </li>
                <li class="divider"></li>
                <li>
                    <div class="dropdown-messages-box">
                        <a href="profile.html" class="pull-left">
                            <img alt="image" class="img-circle" src="img/a4.jpg">
                        </a>
                        <div>
                            <small class="pull-right text-navy">5h ago</small>
                            <strong>Chris Johnatan Overtunk</strong> started following <strong>Monica Smith</strong>. <br>
                            <small class="text-muted">Yesterday 1:21 pm - 11.06.2014</small>
                        </div>
                    </div>
                </li>
                <li class="divider"></li>
                <li>
                    <div class="dropdown-messages-box">
                        <a href="profile.html" class="pull-left">
                            <img alt="image" class="img-circle" src="img/profile.jpg">
                        </a>
                        <div>
                            <small class="pull-right">23h ago</small>
                            <strong>Monica Smith</strong> love <strong>Kim Smith</strong>. <br>
                            <small class="text-muted">2 days ago at 2:30 am - 11.06.2014</small>
                        </div>
                    </div>
                </li>
                <li class="divider"></li>
                <li>
                    <div class="text-center link-block">
                        <a href="mailbox.html">
                            <i class="fa fa-envelope"></i> <strong>Read All Messages</strong>
                        </a>
                    </div>
                </li>
            </ul>
        </li>
        <li class="dropdown">
            <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                <i class="fa fa-bell"></i>  <span class="label label-primary">8</span>
            </a>
            <ul class="dropdown-menu dropdown-alerts">
                <li>
                    <a href="mailbox.html">
                        <div>
                            <i class="fa fa-envelope fa-fw"></i> You have 16 messages
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="profile.html">
                        <div>
                            <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                            <span class="pull-right text-muted small">12 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <a href="grid_options.html">
                        <div>
                            <i class="fa fa-upload fa-fw"></i> Server Rebooted
                            <span class="pull-right text-muted small">4 minutes ago</span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
                <li>
                    <div class="text-center link-block">
                        <a href="notifications.html">
                            <strong>See All Alerts</strong>
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </div>
                </li>
            </ul>
        </li>


        <li>
            <a href="login.html">
                <i class="fa fa-sign-out"></i> Log out
            </a>
        </li>
        <li>
            <a class="right-sidebar-toggle">
                <i class="fa fa-tasks"></i>
            </a>
        </li>
    </ul>

</nav>
</div>