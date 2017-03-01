<!doctype html>
<html class="no-js" lang="fr">
  <head>
    <meta charset="UTF-8">
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@section('titre')@show</title>
    @section('css')
    <link rel="stylesheet" href="{{public_path('assets/css/knacss.css')}}" media="all">
    <link rel="stylesheet" href="{{public_path('assets/css/styles.css')}}" media="all">
    @show
  </head>
  <body>
    
  @yield('contenu')

    @section('js')
    <script src="{{public_path('assets/js/jquery-3.1.1.min.js')}}"></script>
    @show
  </body>
</html>