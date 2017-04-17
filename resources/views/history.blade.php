@extends('common/skeleton')

@section('titre')RECALBOY CONFIG HISTORY
@stop

@section('css')
@parent
<style type="text/css">
#intro {
  margin-bottom: 20px;
  font-size: 14px;
}
.code {
  padding: 0;
  padding-top: 0.2em;
  padding-bottom: 0.2em;
  margin: 0;
  font-size: 85%;
  background-color: rgba(27,31,35,0.05);
  border-radius: 3px;
  font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
}
.button{
  border:none;
  outline:none;
  background:none;
  cursor:pointer;
  background-color: rgba(27,31,35,0.05);
  padding:0;
  text-decoration:underline;
  font-family:inherit;
  font-size:12px;
}
</style>
@stop

@section('contenu')

  <main id="main" role="main" class="mam pas">
    <h1>History of configuration file</h1>
    <div class="grid" id="intro">
      <p>Backups of your <span class="code">.env</span> file.<br />
      Useful if you made weird things or if you lost your settings after an update.<br />
      <a href="/config/recalboy" class="code">Click here</a> to go back to Configuration page.
      </p>
    </div>
    <div class="grid has-gutter" id="formulaire">
      @if ($backups === true)
        <ul>
          @foreach ($files as $file)
          <form action="/config/recalboy" method="post"><li><a href="/storage/backups/{{ $file['filename'] }}.txt" target="_blank">{{ $file['filename'] }}</a> | <input type="hidden" name="rollback" value="{{ $file['filename'] }}.txt"><input type="submit" value="Rollback" class="button code"></li></form>
          @endforeach
        </ul>
      @else
        Hmm, it seems you never made any backups. Come back a bit later :)
      @endif
    </div>


  </main>


@stop