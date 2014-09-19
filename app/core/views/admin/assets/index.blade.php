


  <!-- Modal -->
  <div class="modal fade" id="assets-edit-modal" tabindex="-1" role="dialog" aria-labelledby="assets-edit-modal" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        

      </div>
    </div>
  </div>
  <!-- Modal -->

  <h1 class="page-header">Assets</h1>
  <div class="table-responsive">
    <table class="table table-striped">
    
      <thead>
        <tr>
          <th>#</th>
          <th>Filename</th>
          <th>Path</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        @foreach (Asset::all() as $asset)
          
          <tr>
            <td>{{ $asset->id }}</td>
            <td>{{ $asset->filename }}</td>
            <td>{{ $asset->path }}</td>
            <td>{{ link_to("assets/{$asset->id}/edit", 'Edit', ['class'=>'pull-right', 'data-toggle'=>'modal', 'data-target'=>'#assets-edit-modal', ]) }}</td>
          </tr>

        @endforeach
      </tbody>
    </table>
  </div>

  <div class="text-center">
    @include('slate::site.partials.form-errors')
  </div>

