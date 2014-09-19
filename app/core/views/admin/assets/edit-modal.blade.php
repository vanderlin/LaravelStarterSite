
{{Form::open(array('url' => "assets/{$asset->id}", 'method'=>'DELETE', 'id'=>'delete-asset-form'))}}
{{Form::close()}}	


{{Form::open(array('url' => "assets/{$asset->id}", 'files' => true, 'method'=>'PUT', 'id'=>'edit-asset-form'))}}
	
	@if (Input::has('id') && Input::has('type'))
		<input type="hidden" name="id" value="{{Input::get('id')}}">
		<input type="hidden" name="type" value="{{Input::get('type')}}">
	@endif

	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		<h4 class="modal-title" id="myModalLabel">{{ $asset->getName() }}</h4>
	</div>

	<div class="modal-body">

				<fieldset>
				
					<div class="form-group">
						<label for="name">Name</label>
						<input class="form-control" tabindex="1" placeholder="Optional name" type="text" name="name" id="name" value="{{{ $asset->name }}}">
					</div>
					<div class="form-group text-center well">
						<img class="edit-asset-img" src="{{ $asset->url() }}">
					</div>
					<div class="form-group">
						<label for="file">File</label>
						<br>
						<div class="btn btn-default btn-file"> Replace <input type="file" name="file"> </div>
						<button type="submit" form="delete-asset-form" class="btn btn-danger">Delete</button>
					</div>

				</fieldset>

	</div>

{{Form::close()}}	

	<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		<button tabindex="3" type="submit" form="edit-asset-form" class="btn btn-primary">Save</button>
	</div>
	
