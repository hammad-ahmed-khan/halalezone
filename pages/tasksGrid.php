<div class="row no-gutters mb6">
  <div class="col-sm-12 col-md-5">
    <input id='task-deviation' class="form-control" placeholder="Deviation" type="text"/>
  </div>
  <div class="col-sm-12 col-md-5">
    <input id='task-measure' class="form-control" placeholder="Measure" type="text"/>
  </div>
  <div class="col-sm-12 col-md-2">
    <div class="hidden" id="task-loader"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>
    <button type="button" id="task-add" class="btn btn-sm btn-primary pull-right" title="Add new task to the list" onclick="IP.onAddTask();"><i class="fa fa-plus fa-fw"></i>&nbsp;Add</button>
  </div>
  <div class="col-sm-12">
    <div class="alert-string"></div>
    <div class="success-string"></div>
  </div>
</div>
<div class="table-responsive">
	  <table id="table_tasks" class="table table-hover table-striped table-bordered">
		<thead>
		  <tr class="tableheader">
			<th>Status</th>
      <th>Deviation</th>
			<th>Measure</th>
			<th  style="width:95px;" class="no-sort"></th>
		  </tr>
		</thead>
		<tbody>
		</tbody>
	  </table>
	</div>