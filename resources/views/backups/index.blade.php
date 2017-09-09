@extends('common/skeleton')

@section('titre')BACKUP MANAGER
@stop

@section('css')
@parent
@stop

@section('contenu')
<div class="pixelheader"></div>
<a href="/" class="material-icons" id="go_home">home</a>

  <main id="main" role="main" class="mam pas margin35">
    <h1>Backups Manager</h1>
    <div class="grid" id="intro">
      <p>Guess what ? Manage the backups Recalboy has made while you were playing old good games.<br />Choose a type of backup to manage.
      </p>
    </div>
    <div class="grid-4 has-gutter" id="backups">

        <a href="backups/games/saves" class="mam choice material-icons" id="">videogame_asset</a>

    </div>

    <div class="mam">
    </div>

  </main>


@stop