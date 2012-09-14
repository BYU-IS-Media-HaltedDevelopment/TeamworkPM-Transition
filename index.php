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
		this.userInfoCntrl; // The user info controller

		// Initially hide views besides the first one 
		$("#project_selection").hide();
	};


	/*
	Wires subcontrollers up to their views
	*/
	MigrationUtilCntrl.prototype.wireSubControllers = function(){
		this.userInfoCntrl = new UserInfoCntrl(this); // this refers to the controller
	}

	/*
		
	*/
	MigrationUtilCntrl.prototype.goToStep = function(stepId)
	{
		if(stepId == 2)
			$("#project_selection").slideToggle();
	};

	/*
	Stores the user's api key in the model
	*/
	MigrationUtilCntrl.prototype.storeUserApiKey = function(apiKey) {
		User.apiKey = apiKey;
	};

	/*
	Stores the user's dashboard id in the model 
	*/
	MigrationUtilCntrl.prototype.storeUserDashboardId = function(userDashboardId) {
		User.dashboardId = userDashboardId; 
	};

	/*
	Controller for when the user enters their information.
	*/
	var UserInfoCntrl = function(migrationUtilCntrl){
		 var migrationUtilCntrl = migrationUtilCntrl;

		// connect the controller to the view			
		$("#user_info_next_button").click(function(){	
			$.post("Ajax/portal.php", {
					method: "dashboard",
					action: "get_user_id",
					dashboard_username: "lewistg"
				},
				function(data) {
					migrationUtilCntrl.storeUserApiKey($


					IsLog.c(data);

					//alert(data);
					//$("#copyTasks").html(data)
					//var jsonResponse = JSON.parse(data);
					//alert(data.length + '\n' + JSON.stringify(jsonResponse.response.todo_lists.length));
				}
			);

			migrationUtilCntrl.goToStep(2);
		});
	};

	/*
	Controller for when the user selects a course to migrate.
	*/
	var ProjectSelectionCntrl = function(migrationUtilCntrl){
		// hide the initial view 	
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
		<tr><td>Dashboard username:</td><td><input type="text" /><td></tr>
		<tr><td>Teamwork API Key:</td><td><input type="text" /><td></tr>
	</table>
	<button id="user_info_next_button" type="button">Next</button>
    </div>

    <div id="project_selection">
    	<h2>Step 2: Please select dashboard course to migrate</h2>
       	<table id="dashboard_course_listing">
        </table>
    </div>
</body>
</html>
