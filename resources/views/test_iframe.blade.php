<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
        <script src="https://v2.b-cdn.uk/plugins/jQuery/jQuery-2.1.4.min.js"></script>
        <script>
        $(document).ready(function () {
          $(".popup").hide();
          $(".openpop").click(function (e) {
              e.preventDefault();
              $("iframe").attr("src", $(this).attr('href'));
              $(".links").fadeOut('slow');
              $(".popup").fadeIn('slow');
          });

          $(".close").click(function () {
              $(this).parent().fadeOut("slow");
              $(".links").fadeIn("slow");
          });
        });
        </script>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">TEST</div>
                <div class="links">
                <a class="openpop" href="https://dev.bserv.dev/order/14/3">Order_1</a>
                  </div>
                  <div class="wrapper">
                      <div class="popup">
                          <iframe src="" width="1000px" height="1000px">
                              <p>Your browser does not support iframes.</p>
                          </iframe>
                          <a href="#" class="close">X</a>
                      </div>
                  </div>
            </div>
        </div>
    </body>
</html>
