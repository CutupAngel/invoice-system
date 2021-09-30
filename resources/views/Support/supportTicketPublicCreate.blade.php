@extends('Common.frontendLayout'))

@section('title', 'Support Ticket Form')

@section('content')
    <div class="jumbotron">
        <div class="container">
            <div class="content-header">
                Need Support?
                <p>Having a problem and need some help? Well our support staff are here for you 24/7 to answer any of your questions.</p>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="customer-boxes">
                        @if (count($errors) > 0)
                            <div class="alert alert-dismissible alert-danger">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                @foreach ($errors->all() as $error)
                                    {{$error}}<br>
                                @endforeach
                            </div>
                        @endif
                        @if(session('status'))
                                <div class="alert alert-dismissible alert-success">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    {{ session('status') }}
                                </div>
                        @endif
                        <form action="/home/create-ticket" method="post">
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Email Address</label>
                                <div class="col-sm-10">
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Subject</label>
                                <div class="col-sm-10">
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Message</label>
                                <div class="col-sm-10">
                                    <textarea name="message" class="form-control" required></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary float-right">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('css')
    <style>
        .customer-boxes {padding-top: 40px;}
        .fa-6 {font-size:5.6em;}
        .pagination-centered{text-align:center;}
        .customer-boxes .well{background-color:#fff}
        .customer-boxes a {color: #656565;text-decoration: none;}
        .customer-boxes .well:hover{background-color:#f5f5f5}
        .customer-boxes-second .well{background-color:#fff}
        .customer-boxes-second a {color: #656565;text-decoration: none;}
        .customer-boxes-second .well:hover{background-color:#f5f5f5}
    </style>
@stop