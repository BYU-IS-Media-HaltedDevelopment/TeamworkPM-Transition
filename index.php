<?PHP
require_once('.password');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Dashboard to TeamworkPM Migration Utility</title>
	<link href="css/teamworkpm._m.css?f=179176106" rel="stylesheet" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<style type="text/css">
	.tasktab {
		display: none;
	}
	.disabled {
		color: grey !important;
	}
	</style>
	<script type="text/javascript">
	//	<!--
	$(document).ready(function() {
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
		$.post("Ajax/portal.php", {
					method: "dashboard",
					id: "38839",
					action: "get_course_listing"},
				function(data){
					alert(data);
				});
	});
	//	-->
	</script>
</head>
<body>
	<div id="LayoutMockup">
		<div class="noSel" id="topNavBarNew"><div id="topRight">
			<ul class="noSel" id="topRightNav">		
				<li class="first" id="navquickadd"><a class="tipped disabled" href="javascript:;"><span>Quick Add</span></a></li>
				<li id="navswitchproject"><a class="tipped disabled" href="javascript:;"><span>Switch Project</span></a></li>
				<li id="navsettings"><a class="tipped disabled" href="javascript:;"><span>Settings</span></a></li>
			</ul>
		</div>
		<div id="topLeftDashTabsNew">
			<ul>
				<li id="tl_dashboard"><a class="ql" href="https://byuis.teamworkpm.net/dashboard">Dashboard</a></li><li>|</li>
				<li id="tl_everything"><a title="View tasks, milestones &amp; messages across all projects" href="https://byuis.teamworkpm.net/allitems">Everything</a></li><li>|</li>
				<li id="tl_projects"><a class="ql" href="https://byuis.teamworkpm.net/projects">Projects</a></li><li>|</li>
				<li id="tl_calendar"><a class="ql" href="https://byuis.teamworkpm.net/calendar">Calendar</a></li><li>|</li>
				<li id="tl_statuses"><a class="ql" href="https://byuis.teamworkpm.net/statuses">Statuses</a></li><li>|</li>
				<li id="tl_people"><a class="ql" href="https://byuis.teamworkpm.net/people">People</a></li>
			</ul>
		</div>
		<div style="height:2.5em;">
			&nbsp;
		</div>
		<div id="Tabs" class="sub">
			<ul id="MainTabs">
				<li class="first sel" id="tab_overview"><a class="ql" href="javascript:;">Specific User Tasks</a></li>
				<li class="last" id="tab_tasks"><a class="ql" href="projects/76256-bio-041-200-/tasks">Special Dashboard Query</a></li>
			</ul>
		</div>
	</div>
	<div id="mainContent" class="section" style="margin-left: 1em;">
		<div class="sectiontr"></div>
		<div class="sectiontl"></div>
		<div id="titleHolder">Title</div>
		<div>
			Hello world
		</div>
	</div>
	<div id="taskExample">
			<div id="task1138128" class="task hs" style="position: relative; "><div class="taskLHS"><img id="ti1138128" src="images/icons/checkBox.png" width="13" height="13" alt="" onclick="tw.CheckboxMarkTaskDone(1138128)" style="cursor:pointer;cursor:hand;margin-top:4px;"><div id="pOpt1138128" class="btnDrpDwnDiv huh"><a href="javascript:tw.ShowTaskDropDown(1138128)" class="btnDrpDwn"><span class="l"></span><span class="mid"></span><span class="l"></span></a></div><span class="tHl huh" id="tHl1138128"></span></div><div id="taskRHSH1138128" class="taskRHSH hs"><div id="taskRHS1138128" class="taskRHS"><a href="javascript:tw.ShowEditTask(1138128)" style="font-size:10px;text-decoration:none;" title="Ryan B., Maurianne D., Luke R., Heather B." class="taskBubble mine"><span class="l"></span><span class="r"></span><span class="n">You + 3 others</span></a><span class="estimate tipped" data-tipped-options="showDelay:1000" data-tipped="Estimated time"><a href="javascript:;" onclick="Lightbox.showBoxByAJAX( '?action=invoke.tasks.showLBTaskEstimates()&amp;taskId=1138128&amp;uid='+(new Date()).getTime(), 500, 250, true, null );">8&nbsp;hrs</a></span><a href="tasks/1138128" class="ql tipped" data-tipped-options="skin:'light',showDelay:1000,offset:{y:-5},hook:'topmiddle'" data-tipped="&lt;span style='font-size:9px'&gt;Created by Luke Rebarchik&lt;/span&gt;"><span id="taskName1138128" class="taskName">All courses that currently use BrightCove videos are broken - get response from Computer Ops on ETA for resolution</span></a> <span class="taskDue late" title="Thursday 23 August" reldate="230812">(13 days ago)</span><span class="time tipped" data-tipped="?action=invoke.tasks.getTip_showTimeOnTask()&amp;taskId=1138128&amp;projectId=73292&amp;uid=1346852792225" data-tipped-options="ajax:{cache:false},skin:'light',hideOn:'click-outside',showDelay:500" onclick="tw.GenericLoad('TimeReport.ShowLBLogTimeForTaskForm( 73292 , 1138128 )')"><img src="images/icons/timeItem.png" align="absmiddle" width="16" height="16" alt="" border="0"></span><a href="javascript:;" id="task1138128_commentCount" onclick="Tasks.OnClickBubble(1138128);" title="1 comment" class="taskComment1 tipped" data-tipped="?action=invoke.comments.getTip_objectLatestComment()&amp;projectId=73292&amp;objectType=task&amp;objectId=1138128" data-tipped-options="ajax:true,skin:'light',hideOn:'click-outside'">1</a></div></div></div>
		</div>
	</div>
	<div><input type="text" id="api_key" placeholder="API Key"/></div>
	<div>
		<select id="requestType">
			<option>-- Select the task you wish to perform --</option>
			<option value="copyTasks">Migrate user tasks</option>
			<option value="customQuery">Perform a custom query</option>
		</select>
	</div>
	<div class="tasktab" id="copyTasks">
		Hello world
	</div>
    
    <!--start of new interface -->
    <div id="db_project_selection">
    	<h2>Step 1: Please select dashboard course to migrate</h2>
       	<table id="dashboard_course_listing">
        </table>
    </div>
</body>
</html>
