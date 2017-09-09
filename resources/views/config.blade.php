@extends('common/skeleton')

@section('titre')RECALBOY CONFIG
@stop

@section('css')
@parent
@stop

@section('js')
@parent
<script src="{{public_path('assets/js/filesmanager.js')}}"></script>
<script type="text/javascript">
    function clearCache() {

        $.ajax({
            url : '{{ url("config/cache/clear") }}',
            type: 'get',
            dataType: 'json',

            success: function(response) {
              alert(response.message);
            },

            complete: function(response) {
            }
         });     

    };

</script>
@stop

@section('contenu')
<div class="pixelheader"></div>
<a href="/" class="material-icons" id="go_home">home</a>


  <main id="main" role="main" class="mam pas margin35">
    <h1>Configuration</h1>
    <div class="grid" id="intro">
      <p>Edit Recalboy's configuration file (<span class="code">.env</span>).<br />
      More informations about settings are available on <a href="https://github.com/kjbstar/recalboy" target="_blank" class="code">Github's project page</a>.
      </p>
    </div>
    <div class="grid has-gutter" id="formulaire">
      <form action="/config" method="post">
      <textarea cols="40" id="id_content" name="config" rows="20">{{$config }}</textarea>
      <br />
      <center><input type="submit" value="Save" class="button"></center>
      </form>
    </div>
    <div id="outro">
      <p style="font-style:italic;"><u>In case of broken config</u>: a backup is created every time you save, you can access it here: <a href="/config/history" class="code">Config History</a>.<br/>Just click "Rollback" to load a backup.
      </p>
    </div>

    <h1>Clear Cache</h1>
    <div id="intro">
      <p>Useful if you have some issues to detect a game after you made a scrap on Recalbox.</p>
      <p><center><span id="clearcache" onclick="clearCache()">Click here</span></center></p>
        
    </div>

  </main>


@stop