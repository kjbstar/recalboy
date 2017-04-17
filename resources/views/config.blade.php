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
</style>
@stop

@section('js')
@parent
<script type="text/javascript">


</script>
@stop

@section('contenu')

  <main id="main" role="main" class="mam pas">
      <h1>Configuration</h1>

    <div class="grid has-gutter" id="formulaire">
      <form action="/config/recalboy" method="post">
      <textarea cols="40" id="id_content" name="config" rows="50">{{$config }}</textarea>
      <br />
      <center><input type="submit" value="Enregistrer" class="button"></center>
      
      </form>
    </div>


  </main>


@stop