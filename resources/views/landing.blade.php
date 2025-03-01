<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<link rel="icon" type="image/png"  href="images/favicon.png">



	<!-- SITE TITLE -->
	<title>Knuckleball - Sports TTM Autographs</title>

	<!-- STYLESHEET CSS FILES -->
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/animate.min.css') }}">
	<link rel="stylesheet" href="https://use.typekit.net/hfy1qol.css">
	<link rel="stylesheet" href="{{ asset('css/owl.theme.css') }}">
	<link rel="stylesheet" href="{{ asset('css/owl.carousel.css') }}">

	<link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('css/et-line-font.css') }}">

	<link rel="stylesheet" href="{{ asset('css/nivo-lightbox.css') }}">
	<link rel="stylesheet" href="{{ asset('css/nivo_themes/default/default.css') }}">

	<link rel="stylesheet" href="{{ asset('css/style.css') }}">

	<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,700' rel='stylesheet' type='text/css'>
<!-- MailerLite Universal -->
<script>
    (function(w,d,e,u,f,l,n){w[f]=w[f]||function(){(w[f].q=w[f].q||[])
    .push(arguments);},l=d.createElement(e),l.async=1,l.src=u,
    n=d.getElementsByTagName(e)[0],n.parentNode.insertBefore(l,n);})
    (window,document,'script','https://assets.mailerlite.com/js/universal.js','ml');
    ml('account', '1340045');
</script>
<!-- End MailerLite Universal -->
</head>
<body data-spy="scroll" data-target=".navbar-collapse" data-offset="50">

<!-- navigation section -->
<div class="navbar navbar-default navbar-fixed-top sticky-navigation" role="navigation">
	<div class="container">

		<div class="navbar-header">
			<button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon icon-bar"></span>
				<span class="icon icon-bar"></span>
				<span class="icon icon-bar"></span>
			</button>
			<a href="http://www.knuckleball.app" class="navbar-brand">KNUCKLEBALL</a>
		</div>

		<div class="collapse navbar-collapse">
			<ul class="nav navbar-nav navbar-right main-navigation">
				<li><a href="{{ route('landingPage') }}" class="smoothScroll">HOME</a></li>
				<li><a href="#feature" class="smoothScroll">FEATURES</a></li>
				<li><a href="{{ route('players.index') }}">TTM DATABASE</a></li>
				<li><a href="#contact" class="smoothScroll">CONTACT</a></li>
			</ul>
		</div>

	</div>
</div>

<!-- home section -->
<section id="home">
	<div class="container">
		<div class="row">

			<div class="col-md-12 col-sm-12">
				<h3 class="wow bounceIn">KNUCKLEBALL&nbsp;</h3>
				<h2>
                    <span class="bold">Fresh, Free, and Built for Today’s TTM Collector. </span>
                    <br>
                    <br>
                    <p>
                        <a class="buttonhero" href="https://knuckleball.fly.dev/players">Activate Your Invite</a>
                    </p>
                </h2>

				<img src="{{ asset('images/Card_Hero.png') }}" alt="Cards">
			</div>

		</div>
	</div>
</section>

<!-- feature section -->
<section id="feature">
	<div class="container">
		<div class="row">

			<div class="col-md-12 col-sm-12">
				<div class="section-title">
					<h1 class="heading bold">FEATURES</h1>
					<hr>
					<h2 style="color:#d83c40">Knuckleball is the modern, hassle-free way to find addresses, share wins, and grow your collection.</h2>
				</div>
			</div>

			<div class="col-lg-4 col-md-4 col-sm-6 wow fadeInUp" data-wow-delay="0.6s">
				<div class="col-md-12 col-sm-12">
					<h3>Success Tracker Dashboard</h3>
					<hr>
					<p>Knuckleball&rsquo;s dashboard lets you track your TTM requests, success rates, and response times with ease.</p>
					<h3></h3>
				</div>
				<div class="col-md-12 col-sm-12">
					<h3>Free to Use</h3>
					<hr>
					<p>Knuckleball offers all its features, including tracking, address databases, community spotlights, completely free of charge.</p>
				</div>
			</div>

			<div class="col-lg-4 col-md-4 col-sm-6 wow fadeInUp" data-wow-delay="0.9s">
				<img src="{{ asset('images/Al_Worthington.png') }}" class="img-responsive" alt="Autographed Card of Al Worthington - Bible in the Bullpen Player">
			</div>

			<div class="col-lg-4 col-md-4 col-sm-12 wow fadeInUp" data-wow-delay="1s">
				<div class="col-md-12 col-sm-6">
					<h3>Player Response Rate Data</h3>
					<hr>
					<p>Knuckleball’s free tool tracks response rates for each player, helping you optimize your collection strategy by identifying the most responsive players.</p>
				</div>
				<div class="col-md-12 col-sm-6">
					<h3>Social Feed</h3>
					<hr>
					<p>Stay connected with other collectors through Knuckleball’s real-time social feed. </p>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- video section -->
<section id="video" >
	<div class="container">
		<div class="row">

			<div class="col-md-6 col-sm-10 wow fadeInLeft" data-wow-delay="0.6s">
				<div class="section-title">
					<h1 class="heading bold">What is TTM?</h1>
					<hr>
					<p>TTM (Through The Mail) is a popular method for sports fans to collect autographs by mailing requests to athletes. It’s a great way to connect with your favorite players and build your autograph collection from home. <br><b>Start sending your requests today with <em>Knuckleball</em> and build your dream collection!</b>

					</p>
                    <a class="button" href="#">Get in the Game</a>
                </div>
			</div>

			<div class="col-md-6 col-sm-10 wow fadeInRight" data-wow-delay="0.9s">
				<center>
				<img src="{{ asset('images/Rollie-Fingers.png') }}" alt="Rollie Fingers - Autographed Baseball Card"></center>

				</div>
			</div>

	</div>
</section>

<!-- contact section -->
<section id="contact">
	<div class="container">
		<div class="row">

			<div class="col-md-12 col-sm-12">
				<div class="section-title">
					<h1 class="heading bold">Contact Us</h1>
					<p>Are you a player or agent? Reach out to us directly for profile verification and ownership requests. <br>We’re here to help ensure accurate representation.</p>
					<hr>
				</div>
			</div>

			<div class="col-md-offset-2 col-md-8 col-sm-12">
				<form action="contact.php" method="post" class="wow fadeInUp" data-wow-delay="0.6s">
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" placeholder="Name" name="name" required>
					</div>
					<div class="col-md-6 col-sm-6">
						<input type="email" class="form-control" placeholder="Email" name="email" required>
					</div>
					<div class="col-md-12 col-sm-12">
						<textarea class="form-control" placeholder="Message" rows="7" name"message" required></textarea>
					</div>
					<div class="col-md-offset-4 col-md-8 col-sm-offset-4 col-sm-8">
						<input type="submit" class="form-control" value="SEND MESSAGE">
					</div>
				</form>
			</div>

			</div>
<br>
		<br>
		</div>
	</div>
</section>

<!-- footer section -->
<footer>
	<div class="container">
		<div class="row">

			<div class="col-md-12 col-sm-12">
				<ul class="social-icon">
					<li><a href="https://www.facebook.com/profile.php?id=61570261658450" class="fa fa-facebook wow fadeIn" data-wow-delay="0.3s"></a></li>
					<li><a href="https://www.instagram.com/knuckleballapp/" class="fa fa-instagram wow fadeIn" data-wow-delay="0.9s"></a></li>
				</ul>
				<hr>
				<p><b>Copyright © 2025 Knuckleball  | All right Reserved | Made in the USA </p></b>
			</div>

		</div>
	</div>
</footer>

<!-- JAVASCRIPT JS FILES -->
<script src="{{ asset('js/jquery.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/jquery.nav.js') }}"></script>
<script src="{{ asset('js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/nivo-lightbox.min.js') }}"></script>
<script src="{{ asset('js/wow.min.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

</body>
</html>
