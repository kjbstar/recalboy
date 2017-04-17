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
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <style type="text/css">
    @font-face {
      font-family: 'Material Icons';
      font-style: normal;
      font-weight: 400;
      src: url({{public_path('assets/fonts/MaterialIcons-Regular.eot')}}); /* For IE6-8 */
      src: local('Material Icons'),
           local('MaterialIcons-Regular'),
           url({{public_path('assets/fonts/MaterialIcons-Regular.woff2')}}) format('woff2'),
           url({{public_path('assets/fonts/MaterialIcons-Regular.woff')}}) format('woff'),
           url({{public_path('assets/fonts/MaterialIcons-Regular.ttf')}}) format('truetype');
    }  
    </style>
    @show
  </head>
  <body>
    
  @yield('contenu')

    @section('js')
    <script src="{{public_path('assets/js/jquery-3.1.1.min.js')}}"></script>
    @show
  </body>
</html>