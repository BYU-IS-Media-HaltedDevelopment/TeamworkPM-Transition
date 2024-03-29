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
	=======================Model=======================
	*/

	/*
	Model for the user	
	*/
	var User = {
		apiKey: "",
		dashboardId: ""
	};

	/*
	Collection for all the people in Teamwork
	*/
	var TeamworkPeople = {
		/*
		Array of people
		*/
		people: null,

		/*
		Loads the people from the teamwork website
		*/
		load: function(callBack) {
			if(typeof(callBack) === "undefined")
				callBack = function(){};

			assert(User.apiKey != "", "Can't have empty api key");

			$.post("Ajax/portal.php", {
					method: "teamwork",
					verb: "get",
					path: "people.json",
					api_key: User.apiKey 
				},
				function(jsonUserData) {
					userData = eval('(' + jsonUserData + ')');
					IsLog.c(userData);

					TeamworkPeople.people = new Array();
					for(var personIndex in userData.response.people) 
						TeamworkPeople.people.push(
							userData.response.people[personIndex]);	

					callBack();
				});
		}
	};

	/*
	Collection of Projects
	*/
	var TeamworkProjects = {
		projects: null,

		/*
		Loads the projects from the teamwork website
		*/
		load: function(apiKey, callBack) {
			if(typeof(callBack) === "undefined")
				callBack = function(){};

			$.post("Ajax/portal.php", {
					method: "teamwork",
					verb: "get",
					path: "projects.json",
					api_key: User.apiKey 
				},
				function(jsonProjectData) {
					projectData = eval("(" + jsonProjectData + ")");
					
					TeamworkProjects.projects = new Array();
					for(projectIndex in projectData.response.projects)
						TeamworkProjects.
							projects.push(
						projectData.response.projects[projectIndex]);

					callBack();
				});
		}
	};


	/*
	Collection of Dashboard Tasks for a user
	*/
	var DashUserTask = {
		/*
		Array of Dashboard tasks
		*/
		tasks: null,
		
		/*
		Loads the dashboard tasks from the portal

		dashboardId: The dashboard user name
		callBack: The call back that is called once the list is loaded
		*/
		load: function(dashboardId, callBack) {
			if(typeof(callBack) === "undefined")
				callBack = function(){};

			$.post("Ajax/portal.php", {
					method: "dashboard",
					action: "user_specific_tasks",
					user_id: "'" + dashboardId + "'" 
				},
				function(jsonTaskData) {
					var userPortalInfo = eval("(" + jsonTaskData + ")");

					DashUserTask.tasks = new Array();
					for(var taskIndex in userPortalInfo.response) 
						DashUserTask.tasks.push(userPortalInfo.response[taskIndex]);

					callBack();
				});
		}
	};
	
	/*
	Represents a task that can be migrated
	*/
	var MigrationTask = function() {
		this.dashboardTask = null;
		this.teamworkProject = null;
		this.ready = false;
	};

	/*
	Collection of migration tasks
	*/
	var MigrationTasks = {
		/*
		Array of Migration Tasks
		*/
		migrationTasks: null,

		/*
		Loads the collection of migration tasks
		*/
		load: function(dashboardTasks, teamworkProjects) {
			IsLog.c("Going to compare!");

			IsLog.c("Dashboard tasks");
			for(var i = 0; i < DashUserTask.tasks.length; i++) 
				IsLog.c(DashUserTask.tasks[i].external_id.match(/...-...-.../g));
				
			IsLog.c("Teamwork projects");
			for(var j = 0; j < TeamworkProjects.projects.length; j++) 
				IsLog.c(TeamworkProjects.projects[j].name.match(/...-...-.../g));

			// try to match the tasks
			var dashTasksToMatch = new Array();
			for(var i = 0; i < DashUserTask.tasks.length; i++) {	
				for(var j = 0; j < TeamworkProjects.projects.length; j++) {
					var dashTaskCourse = MigrationTasks.
							getCourseNum(DashUserTask.tasks[i].external_id);
					var teamWorkProjectCourse = MigrationTasks.
							getCourseNum(TeamworkProjects.projects[j].name);

					if(dashTaskCourse == teamWorkProjectCourse) {
						IsLog.c("Matched " + dashTaskCourse + " - " + 
								teamWorkProjectCourse);

						dashTasksToMatch.push(DashUserTask.tasks[i]);
						MigrationTasks.getTaskList(TeamworkProjects.projects[j], 
							function(teamWorkTasks) {
						MigrationTasks.matchDashTaskToTeamTask(
							dashTasksToMatch, teamWorkTasks);
								});
					}
				}
			}
			IsLog.c("Done comparing");
		},

		/*
		Utility function for extracting the course name from the string
		*/
		getCourseNum: function(courseName) {
			assert(courseName != null, "Course name can't be null");
			courseCodeMatches = courseName.match(/...-...-.../g);

			if(courseCodeMatches == null)
				return "";

			return courseCodeMatches[0];
		},

		/*
		Utility function for getting the tasks for a teamwork project
		*/
		getTaskList: function(teamWorkProject, callBack) {
			IsLog.c("Getting tasks for project with id: " + teamWorkProject.id);
			$.post("Ajax/portal.php", {
					method: "teamwork",
					verb: "get",
					path: "projects/" + teamWorkProject.id + "/todo_lists.json",
					api_key: User.apiKey 
				},
				function(jsonTaskListData) {
					IsLog.c(jsonTaskListData);
					taskListData = eval("(" + jsonTaskListData + ")");
					IsLog.c("Got the task data...");
					IsLog.c(taskListData);
					
					/*TeamworkProjects.projects = new Array();
					for(projectIndex in projectData.response.projects)
						TeamworkProjects.
							projects.push(
							projectData.response.projects[projectIndex]);*/

					callBack(taskListData.response);
				});
		},

		/*
		Utility function for matching a dashboard task to a teamwork task
		*/
		matchDashTaskToTeamTask: function(dashTask, teamWorkTasks) {
			// santi

			IsLog.c("Trying to match dash-task to teamwork-task");
			for(var i = 0; i < teamWorkTasks.length; i++)
				IsLog.c(teamWorkTasks[i]);
			IsLog.c(dashTask);
			IsLog.c(teamWorkTasks);
		}
	};

	/*
	=======================Controllers/View=======================
	*/

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
				IsLog.c("Getting the model information");
				// pull all of the dashboard projects
				DashUserTask.load(User.dashboardId, function(){
					IsLog.c("Loading the dashboard tasks");
					ProjectSelectionCntrl.loadDashTasks();

					// load all of the teamwork projects 
					TeamworkPeople.load(function() {
						TeamworkProjects.load("", function() {
							MigrationTasks.load();
						});
					});
				});
			}
		},
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
						
						User.dashboardId = userPortalInfo.response[0].person_id;
						User.apiKey = $("#api_key_input").val();

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
		loadDashTasks: function(dashboardId) {
			for(var i = 0; i < DashUserTask.tasks.length; i++){
				$("#dashboard_course_listing").append(
					ProjectSelectHtmlFactory.createTaskRow(DashUserTask.tasks[i])
						);
			}

			$("#project_selection").slideToggle();
		},

		/*
		Hides the user gui for project selection
		*/
		hideUi: function() {
			$("#project_selection").hide();
		}
	};
	
	/*
	Provides utility methods for creating elements used in the project selection
	view
	*/
	ProjectSelectHtmlFactory = {
		/*
		Creates a row for task
		*/
		createTaskRow: function(dashTask) {
			rowHtml = "<div class='task_row task_ready' id='row_for_task_" + dashTask.external_id + "'>";	
			rowHtml += "<p class='task_assignee'>" + dashTask.assignee_first_name + 
					" " + dashTask.assignee_last_name + "</p>";
			rowHtml += "<p class='task_assigner'>" + dashTask.assigner_first_name +
					" " + dashTask.assigner_last_name + "</p>";
			rowHtml += "<p class='task_description'>" + dashTask.description.substr(0, 100) + "... </p>";
			rowHtml += "</p>";
			rowHtml += "</div><hr />";

			IsLog.c(rowHtml);
			return rowHtml;
		},

		createTaskDetailView: function(userTaskData) {
			detailViewHtml = "<div class='detail_task_view' id='detail_of_task_" + userTaskData.external_id  + "'>";
			detailViewHtml += "<p>Assignee: " + userTaskData.assignee_first_name + 
					" " + userTaskData.assignee_last_name + "</p>";
			detailViewHtml += "<p>Assigner: " + userTaskData.assigner_first_name +
					" " + userTaskData.assigner_last_name + "</p>";
			detailViewHtml += "<p class='task_detail_complete_descrip'>Complete Description</p>";
			detailViewHtml += "<p>" + userTaskData.description + "</p>";
			detailViewHtml += "</div>";
			return detailViewHtml; 
		}
	};
	
	//	<!--
	$(document).ready(function() {
		MigrationUtilCntrl.init();
		TeamworkPeople.load();
		TeamworkProjects.load();
	});
	//	-->
	</script>
</head>
<body>
    <!--start of new interface -->
    <div id="user_info_view">
    	<h2>Step 1: Please enter user info...</h2>
    	<table>
		<tr><td>Dashboard username:</td><td><input id="dashboard_username_input" value="swg5" type="text" /><td></tr>
		<tr><td>Teamwork API Key:</td><td><input id="api_key_input" value="cut527march" type="text" /><td></tr>
	</table>
	<button id="user_info_next_button" type="button">Next</button>
    </div>

    <div id="project_selection">
    	<h2>Step 2: Please select dashboard tasks to migrate...</h2>
	<p>Click a task to see a detailed view.</p>
	<p>Color code: Red means there are un-automated steps that need to be performed before this task
	can be migrated.  Click here to find out what steps are. Green means they are ready to migrate.</p>
	<div id="dashboard_course_listing">
		<div id="task_table_headers">
			<p class="task_assignee">Assignee</p>
			<p class="task_assigner">Assigner</p>
			<p class="task_description">Description</p>
		</div>
		<hr />
	</div>
    </div>
</body>
</html>
