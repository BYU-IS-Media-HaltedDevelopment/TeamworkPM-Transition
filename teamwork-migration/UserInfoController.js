var UserInfoController = {
    /*
     * Called when the user clicks ok
     */
    ok: function()
    {
	console.log("Authenticating: " + UserInfoView.getUsername());	
	UserInfoFacade.authenticate(UserInfoView.getUsername(), 
	    UserInfoView.getApiKey(), 
	    function(){
		MigrationTaskTableController.userLoggedIn();
	    });
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

