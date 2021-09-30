@extends ('Common.template')

@section('title', ' Settings')

@section('page.title', 'Settings')
@section('page.subtitle', 'Payment Gateways')

@section('breadcrumbs')
  <a href="/settings/paymentgateways">Payment Gateways</a>
  <li class="breadcrumb-item active">Merchant Focus</li>
@stop

@section('content')
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Merchant Focus Sign Up</h3>
      </div>
      <div class="card-body">
        @if (count($errors) > 0)
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{$error}}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <h4>Step 1: Customer Information</h4>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="firstName">Fist Name:</label>
              <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name" maxlength="30" required value="{{old('firstName')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="lastName">Last Name:</label>
              <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" maxlength="30" required value="{{old('lastName')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" maxlength="40" required value="{{old('email')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="phone">Phone:</label>
              <input type="tel" class="form-control" id="phone" name="phone" maxlength="10" placeholder="Phone" value="{{old('phone')}}" placeholder="1112223333">
            </div>
          </section>
        </div>

        <h4>Step 2: Company Information</h4>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyName">Company Name:</label>
              <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Company Name" maxlength="30" required value="{{old('companyName')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyAddress">Address:</label>
              <input type="text" class="form-control" id="companyAddress" name="companyAddress" placeholder="Address" maxlength="40" required value="{{old('companyAddress')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyCity">City:</label>
              <input type="text" class="form-control" id="companyCity" name="companyCity" placeholder="City" maxlength="20" required value="{{old('companyCity')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyState">State:</label>
              <input type="text" class="form-control" id="companyState" name="companyState" placeholder="State" maxlength="2" required value="{{old('companyState')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyZip">Zipcode:</label>
              <input type="text" class="form-control" id="companyZip" name="companyZip" placeholder="Zipcode" maxlength="10" required value="{{old('companyZip')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="companyGoods">Goods Sold:</label>
              <input type="text" class="form-control" id="companyGoods" name="companyGoods" placeholder="Types of Goods Sold" maxlength="255" required value="{{old('companyGoods')}}">
            </div>
          </section>
        </div>

        <h4>Step 3: Company Owner Information</h4>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerFirstName">First Name:</label>
              <input type="text" class="form-control" id="ownerFirstName" name="ownerFirstName" placeholder="First Name" maxlength="30" required value="{{old('ownerFirstName')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerLastName">Last Name:</label>
              <input type="text" class="form-control" name="ownerLastName" placeholder="Last Name" maxlength="30" required value="{{old('ownerLastName')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerOwnershipType">Ownership Type:</label>
              <select id="ownerOwnershipType" name="ownerOwnershipType" class="form-control" required value="{{old('ownerOwnershipType')}}">
                <option value="SP">Sole Proprietor</option>
                <option value="LLC">Limited Liability Corp</option>
                <option value="NPO">Non Profit Organization</option>
                <option value="CORP">Corporation</option>
                <option value="PA">Partnership (Canada Only)</option>
                <option value="GOV">Government (Candda Only)</option>
              </select>
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerStreetNumber">Street Number:</label>
              <input type="text" class="form-control" id="ownerStreetNumber" name="ownerStreetNumber" placeholder="Street Number" maxlength="15" required value="{{old('ownerStreetNumber')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerStreetName">Street Name:</label>
              <input type="text" class="form-control" id="ownerStreetName" name="ownerStreetName" placeholder="Street Name" maxlength="50" required value="{{old('ownerStreetName')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerState">State:</label>
              <input type="text" class="form-control" name="ownerState" id="ownerState" placeholder="State" maxlength="2" required value="{{old('ownerState')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerZip">Zipcode:</label>
              <input type="text" class="form-control" name="ownerZip" id="ownerZip" placeholder="Zipcode" maxlength="10" required value="{{old('ownerZip')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerPhone">Phone:</label>
              <input type="tel" class="form-control" name="ownerPhone" id="ownerPhone" placeholder="Phone" maxlength="10" required value="{{old('ownerPhone')}}" placeholder="1112223333">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerSSN">SSN:</label>
              <input type="text" class="form-control" name="ownerSSN" id="ownerSSN" placeholder="SSN" maxlength="10" required value="{{old('ownerSSN')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="ownerBirthday">Birthday:</label>
              <input type="date" class="form-control" name="ownerBirthday" id="ownerBirthday" placeholder="Birthday" required value="{{old('ownerBirthday')}}">
            </div>
          </section>
        </div>

        <h4>Step 4: Tax Information</h4>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="federalTaxId">Federal Tax ID (or SSN):</label>
              <input type="text" class="form-control" name="federalTaxId" id="federalTaxId" placeholder="Federal Tax ID (or SSN)" maxlength="10" required value="{{old('federalTaxId')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="openDate">Business Open Date:</label>
              <input type="date" class="form-control" name="openDate" id="openDate" placeholder="Business Open Date" value="{{old('openDate')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="monthlySales">Monthly Sales:</label>
              <input type="number" class="form-control" name="monthlySales" id="monthlySales" placeholder="Monthly Sales" step="1" required value="{{old('monthlySales')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="averageTicket">Average Sale:</label>
              <input type="number" class="form-control" name="averageTicket" id="averageTicket" placeholder="Average Sale" step="1" required value="{{old('averageTicket')}}">
            </div>
          </section>
        </div>
        <div class="form-group">
          <label for="maximumTicket">Maximum Sale Amount per Credit Card:</label>
          <input type="number" class="form-control" name="maximumTicket" id="maximumTicket" placeholder="Maximum Sale Amount per Credit Card" step="1" required value="{{old('maximumTicket')}}">
        </div>

        <h4>Step 5: Banking Information</h4>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="referenceName">Bank Name:</label>
              <input type="text" class="form-control" name="referenceName" id="referenceName" placeholder="Bank Name" maxlength="50" required value="{{old('referenceName')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="routingNumber">Routing Number:</label>
              <input type="text" class="form-control" name="routingNumber" id="routingNumber" placeholder="Routing Number" maxlength="9" required value="{{old('routingNumber')}}">
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="checkingAccountNumber">Account Number:</label>
              <input type="text" class="form-control" name="checkingAccountNumber" id="checkingAccountNumber" placeholder="Account Number" maxlength="17" required value="{{old('checkingAccountNumber')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="americanExpressStatus">American Express:</label>
              <select name="americanExpressStatus" id="americanExpressStatus" id="americanExpressStatus" class="form-control" value="{{old('americanExpressStatus')}}">
                <option value="enroll">Please Enroll</option>
                <option value="processing">Processing Now</option>
                <option value="no">Not Interested</option>
              </select>
            </div>
          </section>
        </div>
        <div class="row">
          <section class="col-lg-6">
            <div class="form-group">
              <label for="americanExpressNumber">American Express Number:</label>
              <input type="text" class="form-control" name="americanExpressNumber" id="americanExpressNumber" placeholder="American Express Number" maxlength="10" required value="{{old('americanExpressNumber')}}">
            </div>
          </section>
          <section class="col-lg-6">
            <div class="form-group">
              <label for="discoverCardNumber">Discover Card Number:</label>
              <input type="text" class="form-control" name="discoverCardNumber" id="discoverCardNumber" placeholder="Discover Card Number" maxlength="15" required value="{{old('discoverCardNumber')}}">
            </div>
          </section>
        </div>
        <h4>Step 6: Upload Documents:</h4>
        <section class="col-lg-6">
          <div class="form-group">
            Upload a Void Check: <br>
            <label class="control-label" id="check">Select File</label>
            <input type="file" class="file" name="check" id="check" data-show-preview="false" required value="{{old('check')}}">
          </div>
        </section>
        <section class="col-lg-6">
          <div class="form-group">
            Upload your Drivers License: <br>
            <label class="control-label" id="license">Select File</label>
            <input type="file" class="file" name="license" id="license" data-show-preview="false" required value="{{old('license')}}">
          </div>
        </section>
      </div>
      <div class="card-footer">
        <a href="/settings/paymentgateways"><button type="button" class="btn btn-default"> <i class="fa fa-arrow-circle-o-left"></i> Return</button></a>
        <button type="submit" class="btn btn-success float-right"><i class="fa fa-plus"> Signup</i></button>
      </div>
    </div>
  </form>
@stop

@section('javascript')
  <script>
    (function($) {
      $('#americanExpressStatus').on('change', function() {
        if ($(this).val() === 'no') {
          $('#americanExpressNumber').prop('required', false);
        } else {
          $('#americanExpressNumber').prop('required', true);
        }
      });
    })(jQuery);
  </script>
@stop
