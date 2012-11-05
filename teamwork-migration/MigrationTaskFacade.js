/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var MigrationTaskFacade = {
    /*
     * Gets the tasks that need to be migrated
     */
    getUserTasksToMigrate: function(callback) {
	$.get("MigrationUtil.php?username="+UserInfoView.getUsername(), function(taskJson){
	   callback(taskJson);
	});
    }
}