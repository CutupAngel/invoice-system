@extends('Common.frontendLayout')
@section('title', 'Company - Portal Home')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form class="customer-boxes" action="" method="post" autocomplete="off">
                <div class="col-lg-6">
                    <label>Name:</label>
                    <input type="text" name="name" class="form-control" id="usr">
                </div>
                <div class="col-lg-6">
                    <label>Email Address:</label>
                    <input type="email" name="email" class="form-control" id="pwd">
                </div><br>
                <div class="col-lg-12">
                    <label>Subject:</label>
                    <input type="text" name="subject" class="form-control" id="pwd">
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Select Department:</label>
                        <select name="department" class="form-control" id="sel1">
                            <option>Sales</option>
                            <option>Support</option>
                            <option>Billing</option>
                            <option>NOC</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="sel1">Select Priority:</label>
                        <select class="form-control" id="sel1">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="comment">Message:</label>
                        <textarea class="form-control" name="message" rows="5" id="comment"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    .customer-boxes {padding-top: 20px;}
    .fa-6 {font-size:5.6em;}
    .pagination-centered{text-align:center;}
    .customer-boxes .well{background-color:#fff}
    .customer-boxes a {color: #656565;text-decoration: none;}
    .customer-boxes .well:hover{background-color:#f5f5f5}
    .customer-boxes-second .well{background-color:#fff}
    .customer-boxes-second a {color: #656565;text-decoration: none;}
    .customer-boxes-second .well:hover{background-color:#f5f5f5}
    .customer-boxes label {display: inline-block;max-width: 100%;margin-bottom: 5px;font-weight: 700;padding-top: 10px;}
</style>
@stop
