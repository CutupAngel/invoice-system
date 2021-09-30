<input type="hidden" name="optionId" value="{{ $option ? $option->id : '' }}">
<div class="form-group">
  <label>Internal Name:</label>
  <input type="text" class="form-control" name="internal" value="{{ $option ? $option->internal_name : '' }}">
</div>
<div class="form-group">
  <label>Display Name</label>
  <input type="text" class="form-control" name="display" value="{{ $option ? $option->display_name : '' }}">
</div>
<div class="form-group">
  <label>Field Type</label>
  <select name="option_type" class="form-control">
    @foreach ($types as $key => $type)
      <option{{ $option && $option->type == $key ? ' selected' : '' }} value="{{ $key }}">{{ $type }}</option>
    @endforeach
  </select>
</div>
<div class="form-group">
  <span class="input-group-addon checkbox" style="padding: 6px 6px 0 6px; background: #e7e7e7; color: #333;">
    <label>
      <input type="checkbox" class="required" name="required"{{ $option && $option->required ? ' checked' : '' }} value="1">
      Required
    </label>
  </span>
</div>
