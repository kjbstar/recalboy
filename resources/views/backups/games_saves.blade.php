@extends('common/skeleton')

@section('titre')GAMES SAVES
@stop

@section('css')
@parent
<link rel="stylesheet" href="{{public_path('assets/css/filesmanager.css')}}" media="all">
@stop

@section('js')
@parent
<script src="{{public_path('assets/js/filesmanager.js')}}"></script>

<script type="text/javascript">
    function restoreFile(path, name) {

      if (confirm('Do you want to restore '+name+' to your Recalbox ?') == true ) {

        $.ajax({
            url : '{{ url("backups/games/saves/restore") }}',
            type: 'post',
            data: { path: path, type: 'game_save' },
            dataType: 'json',

            success: function(response) {
              //var obj = jQuery.parseJSON(response);
              alert(response.message);
            },

            complete: function(response) {
            }
         });     

      }


    };

  
</script>

@stop

@section('contenu')
<div class="pixelheader"></div>
<a href="{{ app('request')->headers->get('referer') }}" class="material-icons" id="go_back">backspace</a>


  <main id="main" role="main" class="mam pas margin35">
    <h1>Saves of your Games</h1>
    <div class="grid" id="intro">
      <p>Search and restore a single save file, or download all saves files in a Zip archive.
      </p>
    </div>
    <div class="grid has-gutter" id="formulaire">

      <div class="filemanager">


        <div class="search">
          <input type="search" placeholder="Find a game..." />
        </div>

        <div class="breadcrumbs"></div>

        <ul class="data"></ul>

        <div class="nothingfound">
          <div class="nofiles"></div>
          <span>No files found !</span>
        </div>

      </div>

    </div>

  </main>


@stop