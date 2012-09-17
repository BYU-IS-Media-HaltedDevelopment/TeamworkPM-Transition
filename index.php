<?PHP
require_once('.password');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Dashboard to TeamworkPM Migration Utility</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script src="../JavaScript-DebugTools/debug.lib.js" type="text/javascript"></script>
	<script type="text/javascript">

	/*
	Model for the user	
	*/
	var User = {
		apiKey: "",
		dashboardId: ""
	};

	/*
	Controller for the entire app
	*/
	var MigrationUtilCntrl = function(){
		this.userInfoCntrl; 		// The user info controller
		this.projectSelectionCntrl; 	// Project selection controller

		// Initially hide views besides the first one 
		$("#project_selection").hide();
	};


	/*
	Wires subcontrollers up to their views
	*/
	MigrationUtilCntrl.prototype.wireSubControllers = function(){
		// "this" refers to the MigrationUtilCntrl in this context
		this.userInfoCntrl = new UserInfoCntrl(this); 
		this.projectSelectionCntrl = new ProjectSelectionCntrl(this);
	}

	/*
		
	*/
	MigrationUtilCntrl.prototype.goToStep = function(stepId)
	{
		if(stepId == 2) {
			$("#project_selection").slideToggle();
			this.projectSelectionCntrl.populateProjects(User.dashboardId);
		}

	};

	/*
	Stores the user's api key in the model
	*/
	MigrationUtilCntrl.prototype.storeUserApiKey = function(apiKey) {
		User.apiKey = apiKey;
		IsLog.c("User key: " + User.apiKey);
	};

	/*
	Stores the user's dashboard id in the model 
	*/
	MigrationUtilCntrl.prototype.storeUserDashboardId = function(userDashboardId) {
		User.dashboardId = userDashboardId; 
		IsLog.c("Dashboard user id: " + User.dashboardId);
	};

	/*
	Controller for when the user enters their information.
	*/
	var UserInfoCntrl = function(migrationUtilCntrl){
		 var migrationUtilCntrl = migrationUtilCntrl;

		// connect the controller to the view			
		$("#user_info_next_button").click(function(){	
			var dashboardUsername = $("#dashboard_username_input").val();
			IsLog.c("Username: " + dashboardUsername); 
			$.post("Ajax/portal.php", {
					method: "dashboard",
					action: "get_user_id",
					dashboard_username: dashboardUsername 
				},
				function(jsonUserData) {
					IsLog.c(jsonUserData);
					var userPortalInfo = eval("(" + jsonUserData + ")");
					migrationUtilCntrl.storeUserDashboardId(userPortalInfo.response[0].person_id);
					migrationUtilCntrl.storeUserApiKey($("#api_key_input").val());
				migrationUtilCntrl.goToStep(2);
				});

		});
	};

	/*
	Controller for when the user selects a course to migrate.
	*/
	var ProjectSelectionCntrl = function(migrationUtilCntrl){
		 var migrationUtilCntrl = migrationUtilCntrl;
		// hide the initial view 	
	};

	/*
	Creates an html row containing the task data for the user.
	*/
	ProjectSelectionCntrl.prototype.createTableRow = function(userTaskData) {
		rowHtml = "<tr>";		
		rowHtml += "<td>" + userTaskData.asignee_first_name + " " + userTaskData.asignee_last_name + "</td>";
		rowHtml += "<td>" + userTaskData.asigner_first_name + " " + userTaskData.asigner_last_name + "</td>";
		rowHtml += "</tr>";		
	};

	/*
	Gets the populates the list of tasks to migrate 
	*/
	ProjectSelectionCntrl.prototype.populateProjects = function(dashboardId) { 
		IsLog.c("Getting the projects for: " + dashboardId);	
		$.post("Ajax/portal.php", {
				method: "dashboard",
				action: "user_specific_tasks",
				user_id: "'" + dashboardId + "'" 
			},
			function(jsonTaskData) {
				var userPortalInfo = eval("(" + jsonTaskData + ")");
				for(var taskIndex in userPortalInfo.response) {
					userTaskData = userPortalInfo.response[taskIndex]

					rowHtml = "<tr>";		
					rowHtml += "<td>" + userTaskData.assignee_first_name 
							+ " " + userTaskData.assignee_last_name + "</td>";
					rowHtml += "<td>" + userTaskData.assigner_first_name 
							+ " " + userTaskData.assigner_last_name + "</td>";
					rowHtml += "<td>" +userTaskData.description.substr(0, 100) + "..." + "</td>";
					rowHtml += "</tr>";		

					//$("#dashboard_course_listing").append("<tr><td>Hello there!</td></tr>");
					$("#dashboard_course_listing").append(rowHtml);

					IsLog.c(userTaskData);
				}
				/*migrationUtilCntrl.storeUserDashboardId(userPortalInfo.response[0].person_id);
				migrationUtilCntrl.storeUserApiKey($("#api_key_input").val());
			migrationUtilCntrl.goToStep(2);*/
			});
	};


	//	<!--
	$(document).ready(function() {
		var migrationUtilCntrl = new MigrationUtilCntrl();
		migrationUtilCntrl.wireSubControllers();


		/*$("#requestType").change(function(e) {
			$(".tasktab").css("display","none");
			$("#"+e.target.options[e.target.selectedIndex].value).css("display","block");
			//$("#"+).css("display","none");
			$.post("Ajax/portal.php", {
					api_key: $("#api_key").value,
					id: "38839",
					method: "GET",
					action: "projects"
				},
				function(data) {
					//alert(data);
					$("#copyTasks").html(data)
					var jsonResponse = JSON.parse(data);
					alert(data.length + '\n' + JSON.stringify(jsonResponse.response.todo_lists.length));
				}
			);
		});*/
		
		// Load all the courses that can be migrated from dashboard
		/*$.post("Ajax/portal.php", {
					method: "dashboard",
					id: "38839",
					action: "get_course_listing"},
				function(data){
					alert(data);
				});*/
	});
	//	-->
	</script>
</head>
<body>
    <!--start of new interface -->
    <div id="user_info_view">
    	<h2>Step 1: Please enter user info...</h2>
    	<table>
		<tr><td>Dashboard username:</td><td><input id="dashboard_username_input" type="text" /><td></tr>
		<tr><td>Teamwork API Key:</td><td><input id="api_key_input" type="text" /><td></tr>
	</table>
	<button id="user_info_next_button" type="button">Next</button>
    </div>

    <div id="project_selection">
    	<h2>Step 2: Please select dashboard tasks to migrate</h2>
	<p>You can click a row to see more</p>
       	<table id="dashboard_course_listing">
	<tr>
		<th>Assignee</th>
		<th>Assigner</th>
		<th>Description</th>
	</tr>
        </table>
    </div>
</body>
</html>
