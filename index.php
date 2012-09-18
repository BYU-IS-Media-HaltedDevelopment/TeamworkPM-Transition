<?PHP
require_once('.password');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Dashboard to TeamworkPM Migration Utility</title>
	<link rel="stylesheet" type="text/css" href="generic_style.css" />
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
	Controller for the entire app.  Only this controller will talk
	to the model.
	*/
	var MigrationUtilCntrl = {
		/*
		Puts the application into a legal starting state
		*/
		init: function() {
			UserInfoCntrl.init();
			ProjectSelectionCntrl.hideUi();
		},

		/*
		Moves the utility to the given step.
		*/
		goToStep: function(stepId) {
			if(stepId == 2) {
				$("#project_selection").slideToggle();
				ProjectSelectionCntrl.populateProjects(User.dashboardId);
			}
		},

		/*
		Stores the user's api key in the model
		*/
		storeUserApiKey: function(apiKey) {
			User.apiKey = apiKey;
		},

		/*
		Stores the user's dashboard id in the model 
		*/
		storeUserDashboardId: function(userDashboardId) {
			User.dashboardId = userDashboardId; 
		}
	};


	/*
	Controller for when the user enters their information.
	*/
	var UserInfoCntrl = {
		/*
		Initializes the user info controller
		*/
		init: function() {
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
						
						MigrationUtilCntrl.storeUserDashboardId(userPortalInfo.response[0].person_id);
						MigrationUtilCntrl.storeUserApiKey($("#api_key_input").val());

						MigrationUtilCntrl.goToStep(2);
					});

			});
		}
	};

	/*
	Controller for when the user selects a course to migrate.
	*/
	var ProjectSelectionCntrl = {
		/*
		Populates the view with rows of tasks 
		*/
		populateProjects: function(dashboardId) {
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

						rowView = $(ProjectSelectHtmlFactory.createTaskRow(userTaskData));
						$("#dashboard_course_listing").append(rowView);

						detailView = $(ProjectSelectHtmlFactory.createTaskDetailView(userTaskData));
						$("#dashboard_course_listing").append(detailView);
						detailView.hide();

						// wire the row view up to the detailed view
						rowView.click(function(event){
							taskId = $(this).attr("id").replace("row_for_task_", "");
							$("#detail_of_task_" + taskId).show();
						});

						IsLog.c(userTaskData);
					}
				});
		},

		/*
		Hides the user gui for project selection
		*/
		hideUi: function() {
			$("#project_selection").hide();
		},
	};
	
	/*
	Provides utility methods for creating elements used in the project selection
	view
	*/
	ProjectSelectHtmlFactory = {
		/*
		Creates a row for task
		*/
		createTaskRow: function(userTaskData) {
			rowHtml = "<tr id='row_for_task_" + userTaskData.external_id + "'>";		
			rowHtml += "<td>" + userTaskData.assignee_first_name + 
					" " + userTaskData.assignee_last_name + "</td>";
			rowHtml += "<td>" + userTaskData.assigner_first_name +
					" " + userTaskData.assigner_last_name + "</td>";
			rowHtml += "<td>" + userTaskData.description.substr(0, 100) + "... </td>";
			rowHtml += "</tr>";		

			IsLog.c(rowHtml);
			return rowHtml;
		},

		createTaskDetailView: function(userTaskData) {
			detailViewHtml = "<tr><td><div id='detail_of_task_" + userTaskData.external_id  + "'>";
			detailViewHtml += "<p>Assignee: " + userTaskData.assignee_first_name + 
					" " + userTaskData.assignee_last_name + "</p>";
			detailViewHtml += "<p>Assigner: " + userTaskData.assigner_first_name +
					" " + userTaskData.assigner_last_name + "</p>";
			detailViewHtml += "</div></td></tr>";
			return detailViewHtml; 
		}
	};
	
	//	<!--
	$(document).ready(function() {
		MigrationUtilCntrl.init();
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
    	<h2>Step 2: Please select dashboard tasks to migrate...</h2>
	<p>Click a task to see a detailed view.</p>
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
