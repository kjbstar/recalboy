@extends('common/skeleton')

@section('titre')RECALBOY CONFIG
@stop

@section('css')
@parent
<style type="text/css">
textarea {
  font-size: 12px;
  min-height: 50px;
  height: 100%;
  max-height: 100%;  
  max-width: 100%;  
  width: 100%;
  border-radius: 0;
  background-color: #fff;
  border-style: solid;
  border-width: 1px;
  border-color: #ccc;
  box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
  color: rgba(0,0,0,.75);
  display: block;
  margin: 0 0 1.14286rem;
  padding: .57143rem;
}
.button {
  margin: 0 0 30px 0;
}
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
</style>
@stop

@section('contenu')

  <main id="main" role="main" class="mam pas">
    <h1>Configuration</h1>
    <div class="grid" id="intro">
      <p>Edit you <span class="code">.env</span> file.<br />
      Don't worry: a backup is created every time you save, you can access it here: <a href="/config/recalboy/history" class="code">Config History</a><br />
      More informations about configuration are available on <a href="https://github.com/kjbstar/recalboy" target="_blank" class="code">Github's project page</a>.
      </p>
    </div>
    <div class="grid has-gutter" id="formulaire">
      <form action="/config/recalboy" method="post">
      <textarea cols="40" id="id_content" name="config" rows="50">{{$config }}</textarea>
      <br />
      <center><input type="submit" value="Enregistrer" class="button"></center>
      
      </form>
    </div>


  </main>


@stop