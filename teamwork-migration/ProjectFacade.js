/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * Facade for the projects
 */
ProjectFacade = {
    
    projects: [],
    
    /*
     * Gets the projects
     */
    getProjects: function()
    {
	return projects;
    },
    
    /*
     *Loads the projects from the server
     */
    loadProjects: function(callback)
    {
	$.get("ProjectFacade.php?action=get_projects", function(data){
	    console.log(data);
	});
    },
    
    /*
     * Gets the task lists for a project
     */
    getTaskListsForProject: function()
    {
	
    },
    
    /*
     * Creates a task list for a project
     */
    createTaskListForProject: function()
    {
	
    }
}

