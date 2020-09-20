<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>EngineHosting</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.5">
	<!-- Framework Css -->
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/lib/bootstrap.min.css"> -->
	<!-- Font Awesome / Icon Fonts -->
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/lib/font-awesome.min.css"> -->
	<!-- Owl Carousel / Carousel- Slider -->
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/lib/owl.carousel.min.css"> -->
	<!-- Animations -->
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/lib/animations.min.css"> -->
	<!-- Style Theme -->
	<!-- <link rel="stylesheet" type="text/css" href="assets/css/style.css"> -->
	<!-- Responsive Theme -->
    <!-- <link rel="stylesheet" type="text/css" href="assets/css/responsive.css"> -->
    

    {!! Html::style("assets/libs/bootstrap/css/bootstrap.min.css") !!}
    {!! Html::style("assets/libs/font-awesome/css/font-awesome.min.css") !!}
    {!! Html::style("assets/css/owl.carousel.min.css") !!}

    {!! Html::style("assets/css/animations.min.css") !!}

    {!! Html::style("assets/css/style1.css") !!}
    {!! Html::style("assets/css/responsive.css") !!}
</head>
<body>
<div class="wrapper">
	<!--===================== Header ========================-->
<header class="transparent">
	<div class="container">
		<div class="row">
			<div class="col-md-2">
				<div class="logo"><a href="index.html"><img src="assets/images/logo.svg" alt="logo"></a></div>
			</div>
			<div class="col-md-7">
				<ul class="menu">
					<li><a href="index.html">Home</a></li>
					<li><a href="about.html">About Us</a></li>
					<li class="children">
						<a href="#">Hosting</a>
						<ul class="sub-menu">
							<li><a href="service-page.html">Service page 1</a></li>
							<li><a href="service-page-light.html">Service page 2</a></li>
							<li><a href="service-page-dark.html">Service page 3</a></li>
							<li><a href="service-page-images.html">Service page 4</a></li>
						</ul><!--sub-menu-->
					</li><!--children-->
					<li class="children">
						<a href="user-interface.html">Pages</a>
						<ul class="sub-menu">
							<li><a href="404.html">404 Page</a></li>
							<li><a href="order.html">Order</a></li>
							<li><a href="user-interface.html">User Interface</a></li>
						</ul><!--sub-menu-->
					</li>
					<li><a href="blog-list.html">Blog</a></li>
					<li><a href="contact.html">Contact Us</a></li>
				</ul>
			</div>
			<div class="col-md-3">
				<div class="button-header">
					<a href="login" class="custom-btn login">Login</a>
					<a href="signup" class="custom-btn">Sign Up</a>
				</div>
			</div>
		</div>
	</div>
	<div class="mobile-block">
		<div class="logo-mobile"><a href="index.html"><img src="assets/images/logo.svg" alt="logo"></a></div>
		<a href="#" class="mobile-menu-btn"><span></span></a>
		<div class="mobile-menu">
			<div class="inside">
				<div class="logo">
					<a href="index.html"><img src="assets/images/logo.svg" alt="logo"></a>
				</div><!--logo-->
				<ul class="menu panel-group" id="accordion" aria-multiselectable="true">
					<li><a href="index.html">Home</a></li>
					<li><a href="about.html">About Us</a></li>
					<li class="children panel">
						<a href="#menu1" class="collapsed" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="menu1">Hosting</a>
						<ul class="sub-menu panel-collapse collapse" id="menu1">
							<li><a href="service-page.html">Service page 1</a></li>
							<li><a href="service-page-light.html">Service page 2</a></li>
							<li><a href="service-page-dark.html">Service page 3</a></li>
							<li><a href="service-page-images.html">Service page 4</a></li>
						</ul><!--sub-menu-->
					</li>
					<li class="children panel">
						<a href="#menu2" class="collapsed" data-toggle="collapse" data-parent="#accordion" aria-expanded="false" aria-controls="menu2">Pages</a>
						<ul class="sub-menu panel-collapse collapse" id="menu2">
							<li><a href="404.html">404 Page</a></li>
							<li><a href="order.html">Order</a></li>
							<li><a href="user-interface.html">User Interface</a></li>
						</ul><!--sub-menu-->
					</li>
					<li><a href="blog-list.html">Blog</a></li>
					<li><a href="contact.html">Contact Us</a></li>
				</ul><!--menu-->
				<div class="button-header">
					<a href="login.html" class="custom-btn login">Login</a>
					<a href="#" class="custom-btn">Sign Up</a>
				</div><!--button-header-->
			</div><!--inside-->
		</div><!--mobile-menu-->
	</div>
</header>
<!--===================== End of Header ========================-->
	<!--===================== Base Slider ========================-->
	<div class="base-slider owl-carousel owl-theme bg-gray">
		<div class="item">
			<img src="assets/images/bg-test.png" alt="slider">
			<div class="inside">
				<h2>Best Web Hosting For Your Website</h2>
				<p>get best speed for your website. dont loose more clients.</p>
				<a href="service-page.html" class="custom-btn">Get Started Now</a>
			</div><!--inside-->
		</div>
		<div class="item">
			<img src="assets/images/bg-test.png" alt="slider">
			<div class="inside">
				<h2>Best Web Hosting For Your Website</h2>
				<p>get best speed for your website. dont loose more clients.</p>
				<a href="service-page.html" class="custom-btn">Get Started Now</a>
			</div><!--inside-->
		</div>
	</div>
	<!--===================== End of Base Slider ========================-->
	<section class="bg-gray">
		<div class="container">
			<!--===================== Partner ========================-->
			<div class="partner animatedParent">
				<h5>Trusted by 150,000+ happy customers worldwide</h5>
				<div class="partner-slider owl-carousel owl-theme">
					<div class="item animated bounceInLeft delay-250"><a href="#"><img src="assets/images/brand.png" alt="partner"></a></div>
					<div class="item animated bounceInLeft delay-500"><a href="#"><img src="assets/images/brand.png" alt="partner"></a></div>
					<div class="item animated bounceInLeft delay-750"><a href="#"><img src="assets/images/brand.png" alt="partner"></a></div>
					<div class="item animated bounceInLeft delay-1000"><a href="#"><img src="assets/images/brand.png" alt="partner"></a></div>
					<div class="item animated bounceInLeft delay-1250"><a href="#"><img src="assets/images/brand.png" alt="partner"></a></div>
				</div>
			</div>
			<!--===================== End of Partner ========================-->
			<!--===================== Why Choose ========================-->
			<div class="why-choose animatedParent">
				<h2 class="title-head">Why you should choose us</h2>
				<div class="row">
					<div class="col-md-4 col-xs-12 animated bounceInUp delay-250">
						<div class="inside">
							<img src="assets/images/optimised.svg" alt="optimised">
							<a href="#">Totaly Optimised</a>
							<p>Our wordpress theme is totaly optimised for you needs. Very fast and responsive website convert your traffic to new customers.</p>
							<a href="#" class="read-more">Learn More <img src="assets/images/right.png" alt="right"></a>
						</div><!--inside-->
					</div>
					<div class="col-md-4 col-xs-12 animated bounceInUp delay-500">
						<div class="inside">
							<img src="assets/images/powerfull.svg" alt="powerfull">
							<a href="#">Powerful Features</a>
							<p>Our theme get everything you need to work with your hosting website. Its fresh, brandly new and with awesome customizing features. </p>
							<a href="#" class="read-more">Learn More <img src="assets/images/right.png" alt="right"></a>
						</div><!--inside-->
					</div>
					<div class="col-md-4 col-xs-12 animated bounceInUp delay-750">
						<div class="inside">
							<img src="assets/images/website.svg" alt="website">
							<a href="#">Worldwide Support</a>
							<p>We will do suppor to your theme almost 24/7. If you get some bugs or have some problems - just simply write to us. </p>
							<a href="#" class="read-more">Learn More <img src="assets/images/right.png" alt="right"></a>
						</div><!--inside-->
					</div>
				</div>
			</div>
			<!--===================== End of Why Choose ========================-->
			<!--===================== Hosting Software ========================-->
			<div class="hosting-software">
				<h2 class="title-head">Powerful Hosting Software</h2>
				<ul id="counter">
					<li><b class="count" data-count="244">0</b><span>servers</span></li>
					<li>1M+<span>TB total space</span></li>
					<li><b class="count" data-count="5">0</b><span>color schemes</span></li>
					<li><b class="count" data-count="10">0</b><span>faster</span></li>
				</ul>
			</div>
			<!--===================== End of Hosting Software ========================-->
		</div>
	</section>
	<!--===================== Pricing Table ========================-->
	<div class="pricing-table animatedParent">
		<div class="container">
			<h2 class="title-head">Linux Reseller Hosting</h2>
			<p>High Performance cPanel WHM Reseller Hosting in Europe</p>
			<ul class="pricing-list">
				<li class="animated bounceInLeft delay-250">
					<div class="images"><img src="assets/images/server.svg" alt="server"></div>
					<h5>Dedicated Server</h5>
					<p>There are many variations of passages of Lorem Ipsum available.</p>
					<span><b>From</b></span>
					<div class="price">80$<span>/month</span></div>
					<a href="service-page.html" class="custom-btn">Get Started Now</a>
				</li>
				<li class="animated bounceInLeft delay-500">
					<div class="images"><img src="assets/images/hosting.svg" alt="hosting"></div>
					<h5>Reseller Hosting</h5>
					<p>There are many variations of passages of Lorem Ipsum available.</p>
					<span><b>From</b></span>
					<div class="price">160$<span>/month</span></div>
					<a href="service-page.html" class="custom-btn">Get Started Now</a>
				</li>
				<li class="animated bounceInLeft delay-750">
					<div class="images"><img src="assets/images/hosting2.svg" alt="hosting2"></div>
					<h5>Shared Hosting</h5>
					<p>There are many variations of passages of Lorem Ipsum available.</p>
					<span><b>From</b></span>
					<div class="price">9$<span>/month</span></div>
					<a href="service-page.html" class="custom-btn">Get Started Now</a>
				</li>
				<li class="animated bounceInLeft delay-1000">
					<div class="images"><img src="assets/images/vps.svg" alt="vpn"></div>
					<h5>VPS Hosting</h5>
					<p>There are many variations of passages of Lorem Ipsum available.</p>
					<span><b>From</b></span>
					<div class="price">82$<span>/month</span></div>
					<a href="service-page.html" class="custom-btn">Get Started Now</a>
				</li>
			</ul><!--pricing-list-->
			<div class="info-pricing animated bounceInRight delay-250">
				<h4>Why you need a HOSTING?</h4>
				<ul>
					<li>
						<h6>Easy to Customize</h6>
						<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. </p>
					</li>
					<li class="right">
						<h6>Powerful Security</h6>
						<p>Letraset sheets containing Lorem Ipsum passages, and more recently with desktop.</p>
					</li>
				</ul>
				<ul class="right">
					<li>
						<h6>Market Performance</h6>
						<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered.</p>
					</li>
					<li class="button right">
						<a href="service-page.html" class="custom-btn">Get Started Now</a>
					</li>
				</ul>
			</div><!--info-pricing-->
		</div>
	</div>
	<!--===================== End of Pricing Table ========================-->
	<!--===================== User Slider ========================-->
<div class="user-slider">
	<div class="container">
		<div class="slider owl-carousel owl-theme">
			<div class="item">
				<div class="inside">
					<img src="assets/images/icon.svg" class="icon" alt="icon">
					<img src="assets/images/brand.png" alt="logo-tesla">
					<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters.</p>
					<div class="user">
						<a href="#">
							<img src="assets/images/user.png" alt="user">
							Oliver Mitchell
							<span>Manager at Lorem Ipsum</span>
						</a>
					</div><!--user-->
				</div><!--inside-->
			</div>
			<div class="item">
				<div class="inside">
					<img src="assets/images/icon.svg" class="icon" alt="icon">
					<img src="assets/images/brand.png" alt="logo-tesla">
					<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters.</p>
					<div class="user">
						<a href="#">
							<img src="assets/images/user.png" alt="user">
							Oliver Mitchell
							<span>Manager at Lorem Ipsum</span>
						</a>
					</div><!--user-->
				</div><!--inside-->
			</div>
		</div><!--slider-->
	</div>
</div>
<!--===================== End of User Slider ========================-->
	<div class="pre-footer"><img src="assets/images/line-prefoter.svg" alt="bg-prefooter"></div> 
<!--===================== Search Domain ========================-->
<div class="search-domain animatedParent">
	<div class="container animated fadeInUpShort">
		<div class="row">
			<div class="col-md-4 col-xs-12">
				<h3>Search Your Domain</h3>
			</div>
			<div class="col-md-8 col-xs-12">
				<form>
					<div class="form-group">
						<input type="text" placeholder="Domain Name">
						<select>
							<option value=".com">.com</option>
							<option value=".ua">.ua</option>
							<option value=".nu">.nu</option>
						</select>
					</div>
					<button class="custom-btn green">Search</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!--===================== End of Search Domain ========================-->
	<!--===================== Footer ========================-->
<footer>
	<div class="container">
		<div class="widget-footer">
			<h4>Product</h4>
			<ul>
				<li><a href="service-page.html">Web Hosting</a></li>
				<li><a href="service-page.html">Shared Hosting</a></li>
				<li><a href="service-page.html">Dedicated Server</a></li>
				<li><a href="service-page.html">Private Cloud</a></li>
			</ul>
		</div><!--widget-footer-->
		<div class="widget-footer">
			<h4>Company</h4>
			<ul>
				<li><a href="about.html">About Us</a></li>
				<li><a href="service-page.html">Careers</a></li>
				<li><a href="blog-list.html">Blog</a></li>
				<li><a href="contact.html">Contact</a></li>
			</ul>
		</div><!--widget-footer-->
		<div class="widget-footer">
			<h4>Support</h4>
			<ul>
				<li><a href="service-page.html">FAQ</a></li>
				<li><a href="contact.html">Contact Us</a></li>
			</ul>
		</div><!--widget-footer-->
		<div class="widget-footer">
			<h4>Contact Us</h4>
			<ul>
				<li><a href="#">+123-333-123</a></li>
				<li><a href="#">support@enginehosting.com</a></li>
			</ul>
		</div><!--widget-footer-->
		<div class="widget-footer last">
			<a href="index.html"><img src="assets/images/logo.svg" alt="logo"></a>
			<p>There are many variations of passages of Lorem Ipsum </p>
			<ul class="social-icon">
				<li><a href="#"><i class="fa fa-facebook"></i></a></li>
				<li><a href="#"><i class="fa fa-instagram"></i></a></li>
				<li><a href="#"><i class="fa fa-youtube"></i></a></li>
			</ul>
		</div><!--widget-footer-->
		<div class="copyright">
			<p>&copy; Copyright 2017 Hosting, All Rights Reserved</p>
		</div><!--copyright-->
	</div>
</footer>
<!--===================== End of Footer ========================-->
</div><!--wrapper-->
{!! Html::script("assets/js/lib/jquery.js")!!}
{!! Html::script("assets/js/lib/bootstrap.min.js")!!}
{!! Html::script("assets/js/lib/owl.carousel.min.js")!!}
{!! Html::script("assets/js/lib/css3-animate-it.js")!!}
{!! Html::script("assets/js/lib/counter.js")!!}
{!! Html::script("assets/js/main.js")!!}

<!-- {!! Html::script("assets/libs/handlebars/handlebars.runtime.min.js")!!}
{!! Html::script("assets/js/form-elements-page.js")!!} -->
</body>
</html>