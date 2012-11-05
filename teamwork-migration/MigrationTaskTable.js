/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Controller for table
 */
var MigrationTaskTableController = {
    /**
     * Called when the user logs in
     */
    userLoggedIn: function()
    {
	MigrationTaskTableView.openLoadingDialog();
	MigrationTaskFacade.getMigrationTasks(this.gotTasks);
    },
    
    /*
     * Called when the tasks are loaded
     */
    gotTasks: function(taskData)
    {
	MigrationTaskFacade.getMigrationTasks(function(){
	   MigrationTaskTableView.closeLoadingDialog(); 
	});
    }
}

/*
 * View for the table
 */
var MigrationTaskTableView = {
    /**
     * Initializes the view
     */
    init: function()
    {
	$("#loading-table-dialog").
	    dialog({autoOpen: false, modal: true});
    },
    
    /*
     * Adds a new row to the table
     */
    addRow: function(rowView)
    {
	
    },
    
    /*
     * Opens the loading dialog
     */
    openLoadingDialog: function()
    {
	$("#loading-table-dialog").dialog("open");
    },
    
    /*
     * Closes the loading dialog
     */
    closeLoadingDialog: function()
    {
	$("#loading-table-dialog").dialog("close");
    }    
}

/*
 * Controller for a single row
 */
var RowController = function()
{
    
}

/*
 * View for a single row in the table
 */
var RowView = function(rowController, rowData)
{
    this.rowController = rowController;
    
    // the actual model data for this row
    this.tag = rowData; 
}


