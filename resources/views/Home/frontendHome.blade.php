@extends('Common.frontendLayout'))

@section('title', 'Portal Home')

@section('content')
    <div class="jumbotron">
        <div class="container">
            <div class="content-header">
                Welcome to {{$site('name')}}.
                <p>Please find a selection of quick access tools, pay your invoices, order new services or ask for help.</p>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="customer-boxes">
                        <div class="col-md-3">
                            <a href="/auth/login"><div class="well pagination-centered">
                                    <i class="fa fa-cogs fa-6"></i><br>
                                    <h3>Manage Account</h3>
                                    Login to your {{$site('name')}} Account.
                                </div></a>
                        </div>
                        <div class="col-md-3">
                            <a href="/auth/register">
                                <div class="well pagination-centered">
                                    <i class="fa fa-user fa-6"></i><br>
                                    <h3>Register</h3>
                                    Don't have an account? Signup here.
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/support/faq">
                                <div class="well pagination-centered">
                                    <i class="fa fa-question-circle fa-6"></i><br>
                                    <h3>FAQ's</h3>
                                    Browse our FAQ's for an answer to your questions.
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/order">
                                <div class="well pagination-centered">
                                    <i class="fa fa-cart-arrow-down fa-6"></i><br>
                                    <h3>Order Services</h3>
                                    Browse the Products & Services we offer.
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="customer-boxes-second">
                        <div class="col-md-6">
                            <a href="/pay-invoice"><div class="well pagination-centered">
                                    <i class="fa fa-credit-card fa-6"></i><br>
                                    <h3>Pay an Invoice</h3>
                                    Need to pay an invoice? You can pay it here! Just login to your account and pay any outstanding invoices.
                                </div></a>
                        </div>
                        <div class="col-md-6">
                            <a href="/auth/login">
                                <div class="well pagination-centered">
                                    <i class="fa fa-life-ring fa-6"></i><br>
                                    <h3>Need Support?</h3>
                                    Having a problem and need some help? Well our support staff are here for you 24/7 to answer any of your questions.
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

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
