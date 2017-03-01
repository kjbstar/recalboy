@extends('common/skeleton')

@section('titre')RECALBOY
@stop

@section('css')
@parent
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
.hide {
  display: none;
}
.show {
  display: block!important;
}
</style>
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

                var name = response.name;
                var image = '/storage/'+response.image_path;
                var system = response.system;
                $("#game").fadeIn("slow", function() {
                    $('#check').addClass('hide');
                    $('#actions').removeClass('hide');                
                    // C'est moche. TODO : générer dans le controller
                    $(this).html('<div class="grid-2 has-gutter"><div class="mas"><img src="'+image+'"></div><div class="mas"><table><tr><td>GAME</td><td>'+name+'</td></tr><tr><td>SYSTEM</td><td>'+system+'</td></tr></table></div></div>');                          
                });     
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
      <div class="mam action material-icons" id="SAVE_STATE">save</div>
      <div class="mam action material-icons" id="LOAD_STATE">restore</div>
      <div class="mam action material-icons" id="SCREENSHOT">photo_camera</div>
      <div class="mam action material-icons" id="MENU_TOGGLE">menu</div>
      <div class="mam action material-icons" id="RESET">refresh</div>
      <div class="mam action material-icons" id="QUIT">exit_to_app</div>
    </div>

    <div class="mam txtcenter" id="check"><img src="{{public_path('assets/img/recalboy.png')}}" class="checkgame"></div>

  </main>

<div class="animationload hide" id="loader">
  <div class="preloader"></div>
</div>

@stop