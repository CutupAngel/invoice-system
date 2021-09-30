@extends ('Common.template')

@section('title', ' Marketing')

@section('page.title', 'Marketing')
@section('page.subtitle', 'Discounts Codes')

@section('breadcrumbs')
  <a href="/marketing">Marketing</a>
  <li class="breadcrumb-item active">Discounts Codes</li>
@stop

@section('content')
@if (count($errors) > 0)
  <div class="alert alert-dismissible alert-danger">
    <button type="button" class="close" data-dismiss="alert">×</button>
    @foreach ($errors->all() as $error)
      {{$error}}<br>
    @endforeach
  </div>
@endif
<div class="card">
  <div class="card-body table-responsive">
    <table id="discounts" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Code</th>
          <th>Discount Value</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Status</th>
          <th class="no-sort tools"></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<button class="btn btn-success float-right" id="add-discount"><i class="fa fa-plus"></i> Add Discount Code</button>
@stop

@section('javascript')

  <script type="text/x-handlebars-template" id="form-template">
    <div class="modal fade discount-form">
    <form method="post" id="discount-form">
      <div class="modal-dialog">
        <input type="hidden" name="id" value="@{{id}}">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">@{{title}} Discount Code</h4>
            <button type="button" class="close" data-dismiss="modal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="value">Code:</label>
              <input type="text" class="form-control" id="value" name="value" value="@{{code}}" required>
            </div>
            <div class="form-group">
              <label for="discount">Discount Value:</label>
              <div class="input-group">
                <input type="number" class="form-control" id="discount" name="discount" max="100" min="1" value="@{{discount}}" required>
                <span class="input-group-addon">%</span>
              </div>
            </div>
            <div class="form-group">
              <label for="start">Start Date:</label>
              <input type="date" class="form-control" id="start" name="start" value="@{{start}}" required>
            </div>
            <div class="form-group">
              <label for="end">End Date:</label>
              <div class="input-group">
                <input type="date" class="form-control" id="end" name="end" value="@{{end}}" required @{{#if indefinite}}disabled@{{/if}}>
                <span class="input-group-addon checkbox" style="padding: 6px 6px 0 6px; background: #e7e7e7; color: #333;">
                  <label>
                    <input type="checkbox" name="indefinite" value="y" @{{#if indefinite}}checked@{{/if}}>
                    Indefinite
                  </label>
                </span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default float-left" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Save</button>
          </div>
        </div>
      </div>
    </form>
  </script>

  <script>
    (function($) {
      var table = $('#discounts').DataTable({
        'ajax': '/marketing/discount-codes',
        'columns': [
          { 'data': 'code' },
          { 'data': 'discount' },
          { 'data': 'start' },
          { 'data': 'end' },
          { 'data': 'status' },
          { 'data': null, 'render': function() {
            return '<button class="edit"><i class="fas fa-pencil-alt"></i></button>' +
							'<button class="delete"><i class="fas fa-trash"></i></button>';
          }}
        ],
        'paging': true,
        'searching': true,
        'ordering': true
      });

      $('#add-discount').on('click', function() {
        $(Handlebars.compile($('#form-template').html())({
          'id': -1,
          'title': 'Add',
          'code': '',
          'discount': '0',
          'start': '',
          'end': '',
          'indefinite': ''
        })).modal('show');
      });

      $('body').on('hidden.bs.modal', '.discount-form', function () {
        $(this).remove();
      });

      $('body').on('change', '[name="indefinite"]', function() {
        if ($(this).is(':checked')) {
          $(this).parents('.input-group').find('[name="end"]').prop('disabled', true);
        } else {
          $(this).parents('.input-group').find('[name="end"]').prop('disabled', false);
        }
      });

      $('body').on('submit', '#discount-form', function() {
        $.ajax({
          url: '/marketing/discount-codes/' + $(this).find('[name="id"]').val(),
          type: 'PUT',
          data: $(this).serialize(),
        })
        .done(function(response) {
          if(isNaN(response))
          {
              alert(response);
              return;
          }
          table.ajax.reload();
          $('.modal.discount-form .close').click();
        });

        return false;
      });

      $('#discounts').on('click', '.edit', function() {
        var id = $(this).parents('tr').attr('id');
        $.get('/marketing/discount-codes/' + id, function(data) {
          data = JSON.parse(data);
          $(Handlebars.compile($('#form-template').html())({
            'id': data.id,
            'title': 'Edit',
            'code': data.value,
            'discount': data.discount,
            'start': data.start,
            'end': data.end === '0000-00-00' ? '' : data.end,
            'indefinite': data.end === '0000-00-00' ? true : false
          })).modal('show');
        });
      });

      $('#discounts').on('click', '.delete', function() {
        var id = $(this).parents('tr').attr('id');
        if (confirm("Are you sure you want to delete this discount?")) {
          $.ajax({
            url: '/marketing/discount-codes/' + id,
            type: 'DELETE',
          })
          .done(function() {
            table.ajax.reload();
          });
        }
      });
    }(jQuery));
  </script>
@stop
