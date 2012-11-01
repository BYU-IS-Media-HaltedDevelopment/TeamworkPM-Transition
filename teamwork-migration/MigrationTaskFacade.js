/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


var MigrationTaskFacade = {
    /*
     * Gets the tasks that need to be migrated
     */
    getUserTasksToMigrate: function(callback) {
	$.get("MigrationUtil.php?username=sg99", function(taskJson){
	   callback(taskJson);
	});
    }
}