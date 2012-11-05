/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var UserInfoFacade = {
    /*
     * Authenticates the user with the app on the server
     * @param dashUser The user's dashboard username
     * @param dashApiKey The user's api key
     * @param callback The call back to be called when the server
     * returns.
     */
    authenticate: function(dashUser, apiKey, callback){
	$.post("User.php", 
	    {dash_user: dashUser, api_key: apiKey}, 
	    function(data){
		callback();
	    });
    }
}