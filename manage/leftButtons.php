<?session_start();
$managerSession = $_SESSION["managerSession"];
?>
<Div class="leftButtonPanel">
         <p style="padding:10px 10px 10px 10px;">
                <label class="ui-state-default ui-priority-secondary leftButton">Welcome <? echo $managerSession['username']?></label>
                <label class="ui-state-default ui-priority-secondary leftButton">USERS</label>
                    <a href="CreateUserForm.php" id="dialog_link" class="ui-state-default ui-corner-all leftButton leftChildButton">Create New User</a>
                    <a href="showUsers.php" id="dialog_link" class="ui-state-default ui-corner-all leftChildButton leftButton">View Saved Users</a>
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
</Div>