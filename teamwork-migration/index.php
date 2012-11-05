<?php
require_once(".password");
//require_once("MigrationUtil.php");

ini_set('display_errors', 'On');
error_reporting(E_ALL);

?>



<html>
<head>
	<script type="text/javascript" src="underscore-min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
	<script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
	<script src="UserInfoController.js" type="text/javascript"></script>
	<script src="MigrationTaskTable.js" type="text/javascript"></script>
	<script src="MigrationTaskFacade.js" type="text/javascript"></script>
	<script src="UserInfoFacade.js" type="text/javascript"></script>
	<script src="ProjectFacade.js" type="text/javascript"></script>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="table.css" />
	<script type="text/javascript">
	    
	    /*
	     * Class for the migration task table
	     */
	    var MigrationTaskTable = function()
	    {
		// init the button
		$("#migration-button").button();
		migrationTableObj = this;
		$("#migration-button").click(function(){
		    //migrationTableObj.loadRows();
		    // start migration
		});
	    }
	    
	    /*
	     * Loads the migration tasks
	     */
	    MigrationTaskTable.prototype.loadRows = function(username) 
	    {
		$.get("MigrationUtil.php?username=" + username, function(data){
		   var migrationTasks = eval("(" + data + ")");
		   
		   for(i = 0; i < migrationTasks.length; i++)
		   {
		       if(migrationTasks[i].taskId != "completed-task")
		       {
			    var newTaskRow = new TaskRow(migrationTasks[i]);
			    $("#migration-tasks-table").append(newTaskRow.toHtml());
		       }
		   }
		   
		    // hook up the dialog boxes to the rows
		    $(".unmatched-notes").click(function() {
		       $("#unmatched-task-dialog").dialog({modal: true});
		    });
		   
		}).error(function() {alert("Couldn't get tasks");});		
	    }
	    
	    /*
	     * Class for task row
	     */
	    var TaskRow = function(taskData) {
		this.taskData = taskData;
	    }
	    
	    TaskRow.prototype.toHtml = function() {
		html = "<tr class='" + this.taskData.taskId + "'>";
		html += '<td>' + this.taskData.deadlineDate + '</td>'; // deadline date
		html += '<td>' + this.taskData.assigner + '</td>'; // assigner
		html += '<td>' + this.taskData.assignee + '</td>'; // assignee
		html += '<td>' + this.taskData.description + '</td>'; //description
		html += '<td>' + this.getNotes() + '</td>'; // notes
		html += "</tr>";
		
		var rowElement = $(html);
		rowElement.click(function(){
		    if(!this.unselectedClass)
			this.unselectedClass = $(this).attr("class");
		    
		    if($(this).attr("class") == this.unselectedClass)
			$(this).attr("class", "selected-for-migration");
		    else
			$(this).attr("class", this.unselectedClass);
		});
		
		return rowElement;
	    }
	    
	    /*
	     * Dynamically generates the notes section of table
	     */
	    TaskRow.prototype.getNotes = function() {
		switch(this.taskData.taskId)
		{
		    case "to-migrate":
			return "Ready! Click to migrate.";
		    case "unmatchable-task":
			return "<a class='unmatched-notes'>More info</a>";
		}
	    }
	
	    /*
	     * Dynamically creates the date field 
	     */
	    TaskRow.prototype.getDateField = function() {
		
	    }
	    
	    $(document).ready(function(){
		migrationTaskTable = new MigrationTaskTable();
		
		// init the user info interface
		$("button").button();
		$("#get-tasks-button").click(function(){
		    migrationTaskTable.loadRows($("#dashboard-username-input").val()); 
		});
		
		
		$("#task-list").accordion();
		$(".begin-migration-button").button();
		
		// init views
		UserInfoView.init();
		MigrationTaskTableView.init();
	    });
	</script>
</head>
<body>
    <div id="loading-table-dialog" title="Loading Data">
	<p>Please wait while your tasks are loaded. This may take a minute...</p>
    </div>    
    
    <h1>Dashboard Migration Utility</h1>
    
    <div id="user_info_view">
        <table>
                <tr><td>Dashboard username:</td><td><input id="dashboard-username-input" value="mp239" type="text" /></td></tr>
                <!---
                	Scott: 
                    Suzy: sg99 cut527march
                    Marga: mp239 bluff861cod
                 --->
                <tr><td>Teamwork API Key:</td><td><input id="api-key-input" value="bluff861cod" type="text" /></td></tr>
        </table>
	<button id="user-info-ok-button" type="button">Ok</button>
    </div>   
    
    <div id="task-list">
	<h3>Unmigrated Task</h3>
	<div>
	    <p>Extneral Id: RELC-45-101</p>
	    <p>Assigner: Suzy Gerhart</p>
	    <p>Assignee: Suzy Gerhart</p>
	    <p>Description:</p>
	    <textarea cols="40" rows="5"></textarea>
	    <button class="begin-migration-button">Begin Migration</button>
	    <p>Teamwork Project: 
		<select class="team-proj-select">
		    <option value="RELC-34">RELC-34</option>
		    <option value="RELC-36">RELC-36</option>
		</select>
	    </p>
	    <p>Task List:
		<select class="team-proj-select">
		    <option value="RELC-34">RELC-34</option>
		    <option value="RELC-36">RELC-36</option>
		</select> <button class-="create-task-list-button">Create Task List</button>
	    </p>
	    <button class="task-list-selected">Ok</button>
	    <p>Add assignee to teamwork project!</p>
	    <button class="person-added">Ok</button>
	    <br />
	    <button class="migrate-button">Migrate</button>
	</div>
	<h3>Unmigrated Task</h3>
	<div>
	    <p>Some information</p>
	    <input type="text" />
	</div>	
    </div>
</body>
</html>
