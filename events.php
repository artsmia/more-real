<?php include('events_call.php'); ?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
	<title>More Real? Art in the Age of Truthiness at the Minneapolis Institute of Arts</title>
	<meta name="description" content="'More Real? Art in the Age of Truthiness' features work by 28 of today's most accomplished and promising international artists who explore our shifting experience of reality.">
	<meta name="author" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
	<!--FB-OG tags-->
	<meta property="og:type" content="article"/>
	<meta property="og:image" content="images/MR-title-green.png"/>
	<meta property="og:title" content="More Real? Art in the Age of Truthiness at the Minneapolis Institute of Arts"/>
	<meta property="og:url" content="http://www.artsmia.org/more-real"/>
	<meta property="og:description" content="'More Real? Art in the Age of Truthiness' features work by 28 of today's most accomplished and promising international artists who explore our shifting experience of reality."/>
	
	<link rel="stylesheet" href="css/reset.css">
	<!-- 1140px Grid styles for IE -->
	<!--[if lte IE 9]><link rel="stylesheet" href="css/ie.css" type="text/css" media="screen" /><![endif]-->
	<link rel="stylesheet" type="text/css" href="css/1140.css" />
	<link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox.css" />
	<link rel="stylesheet" type="text/css" href="css/morereal.css" />
	
	<script type="text/javascript" src="js/modernizr.custom.js"></script>
	<script type="text/javascript" src="js/css3-mediaqueries.js"></script>
	
	<!--typekit-->
	<script type="text/javascript" src="http://use.typekit.com/vft0zxk.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	
</head>
<body>
	<div id="MIA_header"><a href="/"><img width="373" height="15" src="images/trans.gif" alt="" /></a></div>
	<div class="main container">
		<div class="title row clearfix" id="nav">
			<div class="threecol">
				<ul class="navL">
					<li><a href="index.html#about">About the Exhibition</a></li>
                    <li><a href="press.html">In the Press</a></li>
					<li><a href="preview.html">Exhibition Preview</a></li>
				</ul>
			</div>
			<div class="sixcol">
				<img src="images/MR-title.png" alt="More Real? Art in the Age of Truthiness" />
				<span class="subhead">ART IN THE AGE OF TRUTHINESS</span>
				<span class="dates">Site Santa Fe July 8, 2012&#8212;January 6, 2013<br />Minneapolis Institute of Arts March 21&#8212;June 9, 2013</span>
			</div>
			<div class="threecol last">
				<ul class="navR">
			<li><a href="events.php">Tickets</a></li>
					<li><a href="events.php#events">Events</a><span style="color:#4caf45; font-style:italic;"> &amp; </span><a href="events.php#programs">Programs</a></li>
					<li><a href="index.html#catalogue">Catalogue</a></li>
				</ul>
			</div>
		</div>
        <div class="row trans_box white_gradient1 clearfix" id="tickets">
			<h5 class="section-title">Tickets</h5>
			<div class="eightcol">
				<div class="border" class="tix-img"><img src="images/2012_MoreReal_102.jpg" alt="Eve Sussman/Rufus Corporation, 89 seconds at Alazaar" /></div>
			</div>
			<div class="fourcol last">
				<h2 style="padding:0;">Tickets for <em>More Real? Art in the Age of Truthiness</em> at the Minneapolis Institute of Arts on sale now!</h2>
				<a class="button" href="https://tickets.artsmia.org/public/default.asp?use_more=true&cgCode=6&cgName=Exhibitions">Get Tickets</a>
				<p style="margin:1em 0;padding:0;">
					image:<br />
					<strong>Eve Sussman, Rufus Corporation</strong><br />
					<em>89 seconds at Alazaar</em>, 2004<br />
					Single channel looped video installation<br />
					Collection of Jeanne and Michael L. Klein
				</p>
			</div>	
		</div>
		<?php get_events(); ?>
	</div><!-- container -->
	<nav>
		<ul>
			<li><a class="top" href="#" title="Back to top">Top</a></li>
		</ul>
	</nav>
</body>

	<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.scrollTo.js"></script>
  	<script type="text/javascript" src="js/jquery.localScroll.js"></script>
  	<script type="text/javascript" src="js/waypoints.min.js"></script>
  	<script type="text/javascript" src="js/fancybox/jquery.fancybox.js"></script>	
	<script type="text/javascript">;
		$(document).ready(function(){
		
			$('#artistnav').localScroll({
				hash:true,
				offset:{top:-100,left:0}
			})
			
			$('#skip').localScroll({
				hash:true,
				offset:{top:-60,left:0}
			})
		
			$('.top').addClass('hidden');
			
			$.waypoints.settings.scrollThrottle = 30;
			
			$('body').waypoint(function(event, direction) {
				$('.top').toggleClass('hidden', direction === "up");
			}, {
				offset: '-100%'
			}).find('#artistnav').waypoint(function(event, direction) {
				$(this).parent().toggleClass('sticky', direction === "down");
				event.stopPropagation();
			});
			
			$('.hidenav').click(function(){
		    	$('#artistnav ul').hide();
		    	$('#shownav').show();
		    });
		    
		    $('#shownav').click(function(){
		    	$(this).hide();
		    	$('#artistnav ul').show();
		    });
		    
		    function testCSS(prop) {
    			return prop in document.documentElement.style;
			}

			if ((navigator.appVersion.indexOf("Win")!=-1) && testCSS('MozBoxSizing')){
				$("body").css({
					'text-rendering':'geometricPrecision',
					'font-family' : 'Times New Roman, serif',
					'font-weight' : '100'
					});
			}
			
			$(".fancybox").fancybox();
			
		});
	</script>
	<!--e-m-i-t-->
	<script src="http://e-m-i-t.org/lib/scripts/jquery-ui-1.7.2.spritely.custom.min.js" type="text/javascript"></script>
	<script src="http://e-m-i-t.org/lib/scripts/jquery.spritely-0.6.1.js"></script>
	<script src="http://e-m-i-t.org/lib/scripts/gravity.js"></script>
	<script src="http://e-m-i-t.org/meeting3/birdsClean/moreRealEngineSimple.js" type="text/javascript"></script>
	<!--/e-m-i-t--> 	
	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-32999675-1']);
	  _gaq.push(['_setDomainName', 'artsmia.org']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	
	</script>
</html>