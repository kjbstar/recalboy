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
                var core = response.core;
                $('#game').removeClass('hide');
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


    // Gestion du mode démo
    function demogame() {
      // Cs 2 ifs permettent de gérer les cas des mobiles/tablettes qui passent en veille: il suffirait alors de rafraichir la page et de "relancer" le mode démo, qui reprendrait alors sa surveillance sans relancer de jeu. Comportement simulé en desktop, c'est ok, mais à tester :p
      if (localStorage.getItem('demo') === 'off' || localStorage.getItem('demo') === null) {
        $.ajax({
            url : '{{ url("game/demo/launch") }}',
            type: 'get',
            dataType: 'json',

            success: function(response) {
              if (response.demo === true) {
                goCheckGame();
                localStorage.setItem('demo', 'on');
              }               
            },

            complete: function(response) {
              manageDemo();
            }
         });    
      }
      if (localStorage.getItem('demo') === 'on') {
        goCheckGame();
        manageDemo();
      }  
    };


    function manageDemo() {
      var timeOnScreen = {{ $demo_duration }};

      // Rotation des jeux quand aucune interaction
      window.setInterval(function(){
        if (localStorage.getItem('demo') === 'on') {
          actionQuit();
          $.get("{{ url('/action') }}/QUIT");
          $.get("{{ url('game/demo/kill') }}");
          localStorage.setItem('demo', 'off');
          demogame();
        }
      }, timeOnScreen*1000);

      // Surveillance des interactions du joueur
      window.setInterval(function(){
        if (localStorage.getItem('demo') === 'on') {
          checkPlayer();
        }          
        if (localStorage.getItem('watchGamepad') === 'on') {
          $.get("{{ url('game/demo/player') }}", function(response){
            if (response.demo === 'gamepad_quit') {
              var withDemoOff = 'yes';
              quitGamepad(withDemoOff);
            } 
          });                
        }                
      }, 10000);      
    }


    // En mode démo on check régulièrement si le joueur a pris la main, pour arrêter le mode démo le cas échéant
    function checkPlayer() {
        $.ajax({
            url : '{{ url("game/demo/player") }}',
            type: 'get',
            dataType: 'json',

            success: function(response) {
              if (response.demo === false) {
                localStorage.setItem('demo', 'off');
                localStorage.setItem('watchGamepad', 'on');
              }
              if (response.demo === 'gamepad_skip') {
                actionQuit();
                $.get("{{ url('/action') }}/QUIT");
                $.get("{{ url('game/demo/kill') }}");
                demogame();
              }              
              if (response.demo === 'gamepad_quit') {
                var withDemoOff = 'yes';
                quitGamepad(withDemoOff);
              }                             
            }
         });
    }


    // Si pas déjà un jeu en cours, on va faire un check
    function goCheckGame() {
      if (localStorage.getItem("status") === 'off') {
        checkgame();
      }      
    }

    function quitGamepad(withDemoOff) {
      actionQuit(withDemoOff);
      $.get("{{ url('/action') }}/QUIT");
      localStorage.setItem('demo', 'off');
      localStorage.setItem('watchGamepad', 'off');      
    }

    // Actions quand on quitte un jeu
    function actionQuit(withDemoOff) {
      $('#actions').addClass('hide');
      $('#game').addClass('hide');
      $('#check').removeClass('hide');
      var status = 'off';
      localStorage.setItem("status", status);
      var demoStatus = localStorage.getItem('demo');
      var watchStatus = localStorage.getItem('watchGamepad');
      if (demoStatus == 'on' || watchStatus == 'on') {
          localStorage.setItem('demo', status);
          if (withDemoOff == 'yes') {
            $.get("{{ url('game/demo/kill') }}");
            $.get("{{ url('game/demo/off') }}");
            localStorage.setItem('watchGamepad', 'off');
          }
      }
            
    };


    // Refresh auto - Defaut : toutes les 10 secondes
    var refresh = {{ $refresh }};
    var refresh_delay = {{ $refresh_delay }};
    if ( refresh == '1' ) {
      window.setInterval(function(){
          goCheckGame();
      }, refresh_delay*1000);
    }

    // Boutons d'actions
    $(".action").click(function(){
      var id = $(this).attr('id');
      if (id === 'QUIT') {
        var demoStatus = localStorage.getItem('demo');
        var watchStatus = localStorage.getItem('watchGamepad');
        if (demoStatus == 'on') {
          var withDemoOff = 'yes';
        } else {
          var withDemoOff = 'no';
        }
        if (watchStatus == 'on') {
          var withDemoOff = 'yes';
        }        
        actionQuit(withDemoOff);
      }
      $.get("{{ url('/action') }}/"+id);
    });
        
    // Forcer le check de jeu
    $(".checkgame").click(function(){
        checkgame();
    });

    // Lancer une démo
    $("#demo_launch").click(function(){
        demogame();
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

    <div class="mam txtcenter" id="check">
      <a href="#" class="material-icons" id="demo_launch" style="position:fixed">ondemand_video</a>
      <img src="{{public_path('assets/img/recalboy.png')}}" class="checkgame">
    </div>

  </main>

<div class="animationload hide" id="loader">
  <div class="preloader"></div>
</div>

@stop