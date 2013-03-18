<html>
  <head>
    <title>Truthiness! - More Real - Minneapolis Institute of Arts</title>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.firebase.com/v0/firebase.js"></script>
  </head>
  <style>
    #gallery figure { width: 20%; float: left }
    #gallery figure img { max-width: 100%; }
  </style>
  <body>
    <section id="gallery">
    </section>
    <script>
    function add_tabloid(snap) {
      if(snap.val() == null || snap.val().cover == undefined) { // don't show until the image has been uploaded
      } else {
        fig = $('<figure><img src="' + snap.val().cover + '"></figure>')
        $("#gallery").prepend(fig)
      }
    }

    var per_page = 20
    var tabloidRef = new Firebase('https://more-real.firebaseio.com/tabloids')
    tabloidRef.limit(per_page).on('child_added', add_tabloid)
    tabloidRef.limit(per_page).on('child_changed', add_tabloid)
    </script>
  </body>
</html>
