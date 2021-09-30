<input type="hidden" name="optionId" value="{{ $option->id }}">
<input type="hidden" name="valueId" value="{{ $value ? $value->id : '' }}">
<div class="form-group">
  <label>Display Name</label>
  <input type="text" class="form-control" name="display" value="{{ $value ? $value->display_name : '' }}">
</div>
<div class="form-group">
  <label>Price</label>
  <input type="text" class="form-control" name="price" value="{{ $value ? $value->price : '' }}">
  <select class="form-control" name="cycle">
    @foreach ($types as $key => $type)
        <option value="{{ $key }}"{{ $value ? $value->cycle_type == $key ? ' selected' : '' : '' }}>{{ $type }}</option>
    @endforeach
  </select>
</div>
<div class="form-group">
  <label>Setup Fee</label>
  <input type="text" class="form-control" name="fee" value="{{ $value ? $value->fee : '' }}">
</div>
