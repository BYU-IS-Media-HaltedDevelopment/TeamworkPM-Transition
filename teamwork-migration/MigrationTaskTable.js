/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * View for the table
 */
var MigrationTaskTable = {
    /*
     * Adds a new row to the table
     */
    addRow: function(rowView)
    {
	
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


