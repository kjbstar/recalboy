@extends('common/skeleton')

@section('titre')RECALBOY
@stop

@section('css')
@parent
<link rel="stylesheet" href="{{public_path('assets/css/'.getenv('THEME').'.css')}}" media="all">
@stop

@section('js')
@parent
<script type="text/javascript">

$(document).ready(function(){

    var status = 'off';
    localStorage.setItem("status", status);
    
    // Boutons d'actions
    $(".action").click(function(){
    	var id = $(this).attr('id');
      if (id === 'QUIT') {
        $('#check').removeClass('hide');
        $('#actions').addClass('hide');
        $('#game').addClass('hide');
        var status = 'off';
        localStorage.setItem("status", status);
      }
      $.get("{{ url('/action') }}/"+id);
    });

    // Ajax de recherche de jeu
    function checkgame() {
        $.ajax({
            url : '{{ url("game/check") }}',
            type: 'get',
            dataType: 'json',

            beforeSend: function() {
              $("#loader").fadeIn("slow", function() {
                  $(this).removeClass("hide");
              });              
            },

            success: function(response) {
              $("#loader").fadeOut("slow", function() {
                  $(this).addClass("hide");
              });
              if (response.status === 'on') {
                var status = 'on'; 
                localStorage.setItem("status", status);

                var htmlrender = response.html;
                var core = response.core
                $('#game').fadeIn('slow', function() {
                    $('#check').addClass('hide');
                    if (core === 'retroarch'){
                      $('#actions').removeClass('hide');        
                    }        
                    $(this).html(htmlrender);                          
                });  

                if ( response.extras != 'undefined' ) {
                  $('#extras').fadeIn('slow', function() {
                    $.each(response.extras, function(key, value){
                      $('#extras').append('<div class="grid has-gutter"><div class="mas"><a href="'+value+'" target="_blank"><img src="'+value+'"></a></div></div>');
                    });
                  });
                }

              } 
            },

            complete: function(response) {
            }
         });      
      };

    // Refresh auto - Defaut : toutes les 10 secondes
    var refresh = '{{ $refresh }}';
    var refresh_delay = '{{ $refresh_delay }}';
    if ( refresh == '1' ) {
      window.setInterval(function(){
        if (localStorage.getItem("status") === 'off') {
          checkgame();
        } 
      }, refresh_delay);
    }
        
    // Forcer le check de jeu
    $(".checkgame").click(function(){
        checkgame();
    });

});

</script>
@stop

@section('contenu')

  <main id="main" role="main" class="txtcenter mam pas">
    <div class="mam" id="game"></div>

    <div class="grid-2 has-gutter hide" id="actions">
      <a name="actions"></a>
      <div class="mam action material-icons" id="SAVE_STATE">save</div>
      <div class="mam action material-icons" id="LOAD_STATE">restore</div>
      <div class="mam action material-icons" id="SCREENSHOT">photo_camera</div>
      <div class="mam action material-icons" id="MENU_TOGGLE">menu</div>
      <div class="mam action material-icons" id="RESET">refresh</div>
      <div class="mam action material-icons" id="QUIT">exit_to_app</div>
    </div>

    <div class="mam" id="extras"></div>

    <div class="mam txtcenter" id="check"><img src="{{public_path('assets/img/recalboy.png')}}" class="checkgame"></div>

  </main>

<div class="animationload hide" id="loader">
  <div class="preloader"></div>
</div>

@stop