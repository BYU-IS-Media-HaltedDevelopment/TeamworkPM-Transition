var UserInfoController = {
    /*
     * Called when the user clicks ok
     */
    ok: function()
    {
	/*$.get("MigrationUtil.php?username=" + UserInfoView.getUsername(), function(data){
	    alert("Got data");
	   /*var migrationTasks = eval("(" + data + ")");

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

	})
	.error(function() {alert("Couldn't get tasks");});*/
	UserInfoFacade.authenticate(UserInfoView.getUsername(), 
	    UserInfoView.getApiKey(), 
	    function(){});
	
	console.log(MigrationTaskFacade);
	
	// 
	MigrationTaskFacade.getUserTasksToMigrate(function(taskJson){
	    console.log(taskJson);
	});
	
	ProjectFacade.loadProjects(function(){});
    }
}

var UserInfoView = {
    /*
     * Initializes the user info view
     */
    init: function() 
    {
	// hook controller up to view to the controller
	$("#user-info-ok-button").click(function(){
	    UserInfoController.ok();
	});
    },
    
    /*
     * Getter for the username
     */
    getUsername: function()
    {
	return $("#dashboard-username-input").val();
    },
    
    /*
     * Getter for the api key
     */
    getApiKey: function()
    {
	return $("#api-key-input").val();
    }
}

