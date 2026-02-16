@extends('frontend::layouts.pinellas')

@section('title')
    {{ __('Home') }}
@endsection

@section('meta_keywords')
    {{ trim(setting('meta_keywords','meta')) }}
@endsection

@section('meta_description')
    {{ trim(setting('meta_description','meta')) }}
@endsection

@section('content')

<!-- HB -->
<style type="text/css">
	.hb_bg{width: 320px; height: 60px /*220px*/; background: rgba(255,255,255,.75); border-radius: 0 0 5px 5px; -webkit-box-shadow: 0 0 5px 0 rgba(0,0,0,.5); box-shadow: 0 0 5px 0 rgba(0,0,0,.5); position: absolute; top: 136px; right: 50%; margin-right: -585px; z-index: 98; overflow: hidden; -webkit-transition:  all .5s; transition:  all .5s;}
	.hb{width: 100%; height: 220px; border-radius: 0 0 5px 5px; -webkit-box-shadow: inset 0 5px 5px 0 rgba(0,0,0,.2); box-shadow: inset 0 5px 5px 0 rgba(0,0,0,.2); padding: 20px 10px; text-align:center;}
	.hb_bg{ width: 296px;height: 220px;}
	.hb{padding: 34px 20px 20px 20px;height: 220px;}
	.hbbttn {display: none;}
	.hbtitle{font-family: Montserrat, sans-serif; font-weight: 700; font-size: 20px; color: #00539b}
	.hb input{width: 180px; height: 34px;  border-radius: 5px; font-size: 14px; text-align: center; margin-top: 10px;}
	.hb input[type=text]{border: solid 1px #838387; color: #838387; margin-top: 15px; }
	.hb input[type=submit]{border: solid 1px #FFF; font-weight: 600; color: #FFF; text-transform: uppercase;-webkit-box-shadow: none; box-shadow: none; padding: 0; background: #da291c; cursor: pointer; }
    .hb input[type=submit]:hover { background: #00539b; }
	.hblinks {font-size: 14px;margin-top: 10px;}
	.hblinks a, .hblinks avisited {color: #333; text-decoration: underline;}
	.hblinks a:hover, .hblinks a:focus {color: #da291c; text-decoration: underline;}
	.hb label {display: none;}
	#hbbttn{cursor: pointer; }
	.rotated .far {
	  transform: rotate(180deg);
	  -ms-transform: rotate(180deg); /* IE 9 */
	  -moz-transform: rotate(180deg); /* Firefox */
	  -webkit-transform: rotate(180deg); /* Safari and Chrome */
	  -o-transform: rotate(180deg); /* Opera */
	}
	@media only screen and (max-width: 1200px) {
		.hb_bg{right: 30px; margin-right: 0; }
	}
	@media only screen and (max-width: 1000px) {
		.hb_bg{right: 0; width: 264px;border-radius: 0 0 0 5px; top: 160px;}
		.hb{width: 264px;border-radius: 0 0 0 5px;padding: 20px 10px;}
		.hbbttn { font-size: 18px; }
	}
	@media only screen and (max-width: 800px) {
		.hb_bg{ width: 100%; background: none white; border-radius: 0; position: relative; top: 0; height:  160px;}
		.hb{ width: 100%;border-radius: 0; -webkit-box-shadow: none; box-shadow: none; padding: 20px;}
		.hbbttn span.far {display: none;}
	}
	@media only screen and (max-width: 430px) {
		.hb_bg{height: 190px;}
	}
</style>

<div class="hb_bg"><div class="hb" role="form" aria-labelledby="hbtitle">
	<div class="hbtitle" id="hbtitle">Online Account Access</div>
	<button class="hbbttn" id="hbbttn">Online Account Access <span class="far fa-chevron-down"></span></button>

		<form method="GET" action="{{ route('login') }}">
			<label for="Username">Username</label>
			<input type="text" id="Username" name="email" spellcheck="off" autocorrect="off"
			autocapitalize="off" required placeholder="Username">
			
			<input type="submit" value="Sign in">
		</form>
		<p class="hblinks"><a href="{{ route('register') }}">Enroll</a>
</div></div>

<!-- SLIDESHOW -->
<style type="text/css">
	a.Bskipper, a.Bskipper:visited { font-size: 0; line-height: 0; position: absolute; top: 400px; left: 50%}
	a.Bskipper:focus, a.Bskipper:visited:focus {border: solid 2px white; color: black; background-color: yellow; line-height: 1; font-size: 18px; z-index: 1000;}
	section.slick-slider {width: 100%;  max-width: initial;  height: 540px; margin: 0 auto; padding: 0;  position: relative; display: block; z-index: 10; border: solid 0px lime;}
	.slick-slide{display: none; float: left; transition: all ease-in-out .3s; opacity: .2;width: 100%; position: relative;  height: 540px;background-size: cover; background-repeat: no-repeat;background-position: center bottom}
	.thetextholder{height: 540px;  width: 100%;  position: absolute; bottom: 0px; left: 0; background: url(https://www.pinellasfcu.org/templates/pinellas/images/wave.png) no-repeat center bottom;  display: flex;  justify-content: center; align-items: center; border: solid 0px orange}
	.thetext {width: 1170px; padding: 0 386px 0 60px;;  color: white; font-size: 18px; text-align: left; font-weight: 300; line-height:  26px; color: white;}
	.slick-dots{position: absolute; bottom: 85px; display: block; width: 100%; padding: 0; margin: 0; list-style: none; text-align: center;}
	.slick-dots li{ position: relative; display: inline-block; width: 18px; height: 18px; margin: 0 5px; cursor: pointer; border: solid 2px white; border-radius: 9px; text-align: center}
	.slick-dots li:hover, .slick-dots li:focus{webkit-box-shadow: 0 0 5px 0 rgba(255,255,255,1.0); box-shadow: 0 0 5px 0 rgba(255,255,255,1.0); margin: 0 5px}
	.slick-dots li button{width: 14px; padding: 0; height: 14px;  border-radius: 7px; background-color: white; font-size: 0; line-height: 0; border: none; position: absolute; top: 0px; right: 0px;  }
	.slick-dots li button:hover, .slick-dots li button:focus{background-color: #009eda;}
	.slick-dots li.slick-active button{background-color: #da291c;}
    @media only screen and (max-width: 1200px) {
		.thetextholder{ justify-content: flex-start; }
		.thetext {width: 480px; padding: 0 0 0 40px; font-size: 16px; line-height:  24px;}
	}
	@media only screen and (max-width: 800px) {
		.thetextholder{background-image: url(https://www.pinellasfcu.org/templates/pinellas/images/waveBlue800.png); background-position: center top; height: 300px; padding: 0;  background-size: 133% auto;position: absolute; bottom: 0px; right: 0px;}
		section.slick-slider { height: 0; padding-bottom: calc(50% + 300px);}
		.slick-slide{ height: 0; padding: 10% 0 300px 0; background-size: 280% auto; background-position: center top;}
		.thetext {width: 100%; margin-right: 0px; padding: 50px 40px 0 40px; }
		.slick-dots{ bottom: 250px; }
	}
</style>

<a href="#skipslides" class="Bskipper">Skip Slideshow</a>
<section class="SL_html" role="complementary">
    <div style="background-image: url(https://www.pinellasfcu.org/files/pinellasfcu/1/image/slideshow/PIN-web-annual-meeting-26-home.jpg); ">
        <div class="thetextholder"><div class="thetext"><p style="margin-left: 215px"><a class="cta" href="https://www.pinellasfcu.org/celebrate">LEARN MORE</a></p></div></div>
    </div>
    <div style="background-image: url(https://www.pinellasfcu.org/files/pinellasfcu/1/image/slideshow/PIN-web-free-gap-26-home-2.jpg); ">
        <div class="thetextholder"><div class="thetext"><p><span style="margin-left: 444px"><a class="cta" href="https://www.pinellasfcu.org/free-gap">LEARN MORE</a></span></p></div></div>
    </div>
</section>
<a name="skipslides"></a>

<script type="text/javascript">
	$(document).on('ready', function() {
		$(".SL_html").slick({
			dots: true,
			arrows: false,
			infinite: true,
			speed: 1000,
			fade: true,
			autoplay: true,
			autoplaySpeed: 4000,
			zIndex: 10,
			adaptiveHeight: true,
		});
	});
</script>

<!-- promos -->
<section class="sand" role="complementary">
    <style type="text/css">
        section.sand {background: url(https://www.pinellasfcu.org/templates/pinellas/images/SandBackground.jpg) center top no-repeat; max-height:  1040px; padding: 40px 0;  position: relative; z-index: 1}
        .center {display: block; width: 90%; max-width: 1110px; height: 630px; margin: 0 auto; position: relative; padding: 10px 0;z-index: 10}
        .center .slick-slide {width: 372px; height: 630px; margin: 0px; padding: 10px; overflow: hidden; transition: all ease-in-out .3s; opacity: 1; float: left;z-index: 10}
        .holder {width: 352px; height: 610px; background-color: white; border-radius: 5px; -webkit-box-shadow: 0 0 5px 0 rgba(0,0,0,.5); box-shadow: 0 0 5px 0 rgba(0,0,0,.5); margin: 0; padding: 30px; text-align: left; z-index: 10}
        .holder img {width: 330px !important; left: -19px; position: relative; }
        .holder h2 {font-family: Montserrat, sans-serif; font-weight: 600; font-size: 22px; line-height:  30px;  margin: 0 0 14px; color: #00539b; }
        .slick-prev, .slick-next{z-index: 101;  position: absolute; top: 50%; display: block;  cursor: pointer;font-size: 0; line-height: 0; -webkit-transition:  color .5s, background-color .5s; transition:  color .5s, background-color .5s;color: #00539b; font-size: 30px; border: none; background: transparent; }
        .slick-prev{left: -30px; }
        .slick-next{right: -30px;}
        .slick-prev:hover, .slick-next:hover{color: #da291c}
        @media only screen and (max-width: 1200px) { .center {max-width: 760px;} }
        @media only screen and (max-width: 1000px) {
            .slick-prev, .slick-next{top: 25%;text-shadow: 0 0 5px #ffffff;  }
            .slick-prev{left: 10px; } .slick-next{right: 10px;}
        }
    </style>
    <div class="center slider">
        <div><div class="holder"><h2><a href="https://www.pinellasfcu.org/personal-banking/loans"><img alt="auto" src="https://www.pinellasfcu.org/files/pinellasfcu/1/image/home_page_promos/loanshomepage2022.jpg" /></a></h2><h2 style="text-align: center;"><strong>We've Got You Covered!</strong></h2><p>We have loans to help you through every stage of life. Call us at 727.586.4422 or stop by one of the branches to apply.</p></div></div>
        <div><div class="holder"><h2><a href="{{ route('register') }}"><img alt="membership" src="https://www.pinellasfcu.org/files/pinellasfcu/1/image/home_page_promos/membershipgraphic2022 (1).jpg" /></a></h2><h2 style="text-align: center;"><strong>Our Members Are Family</strong></h2><p>Since 1956, PFCU has offered an array of benefits to help members reach financial wellness. </p><p> <a class="cta" href="{{ route('register') }}">BECOME A MEMBER</a></p></div></div>
        <div><div class="holder"><h2><img alt="Pinellas Retirement" src="https://www.pinellasfcu.org/files/pinellasfcu/1/image/Loan Promos/PinellasFCU_Launch-WebBanner_FINAL.jpg" /></h2><h2 style="text-align: center;"><strong>Reward Yourself!</strong></h2><p>Check out our newly designed credit card with rewards!</p><p><a class="cta" href="https://www.pinellasfcu.org/personal-banking/visa/visa-credit-cards">LEARN MORE</a></p></div></div>
    </div>
    <script type="text/javascript">
        $(document).on('ready', function() {
          $(".center").slick({
            dots: false,
            infinite: true,
            prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"><span class="fas fa-chevron-left"></span></button>',
            nextArrow: '<button class="slick-next" aria-label="Next" type="button"><span class="fas fa-chevron-right"></span></button>',
            centerMode: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: false,
            variableWidth: true,
            cssEase: 'linear'
          });
        });
    </script>
</section>

<section class="home" role="main" aria-label="Main Content">
    <article id="maincontent">
        <h1><span>Free Financial Guidance</span></h1>
        <p>We care about your financial health and stability. Wherever you are in your financial journey, we are here to help.</p>
        <p><a class="cta" href="https://greenpath.com/partner/pinellasfcu">LEARN MORE</a></p>
        <a href="#nav" class="skipper">Go to main navigation</a>
        <div class="breaker"></div>
    </article>
</section>

<section class="P2" role="complementary">
    <div class="liner">
        <div class="imgbox" style="background-image: url(https://www.pinellasfcu.org/files/pinellasfcu/1/banners/autosmart__pic_71.jpg);"></div>
        <div class="txtbox"><div class="sidebox">
            <div class="code_block_box"><h2>SEARCH, SHOP & FINANCE, ALL IN ONE PLACE</h2>
            <p>Find the best deal on your next vehicle purchase through our partnership with AutoSMART. Buy with confidence.</p>
            <p><a class="cta" href="https://pinellasfcu.cudlautosmart.com/" target="_blank">SHOP NOW</a></p>
            </div></div></div>
        <div class="breaker"></div>
    </div>
</section>

@endsection

@push('style')
<style>
    .cta { display: inline-block; background: #da291c; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 700; text-transform: uppercase; margin-top: 10px; }
    .cta:hover { background: #00539b; }
    .Bheader { font-size: 48px; font-weight: 700; color: #fff; margin-bottom: 20px; line-height: 1.2; }
    .Bsub { font-size: 24px; color: #fff; margin-bottom: 30px; font-weight: 300; }
    section.home { padding: 80px 0; text-align: center; background: #fff; }
    section.home article h1 { font-family: Montserrat, sans-serif; color: #00539b; font-size: 36px; margin-bottom: 20px; }
    section.home article p { font-size: 18px; color: #333; max-width: 800px; margin: 0 auto 30px; }
    section.P2 { padding: 60px 0; background: #f2f2f2; }
    section.P2 .liner { max-width: 1170px; margin: 0 auto; display: flex; align-items: center; }
    section.P2 .imgbox { width: 50%; height: 400px; background-size: cover; background-position: center; border-radius: 10px; }
    section.P2 .txtbox { width: 50%; padding-left: 50px; }
    section.P2 .txtbox h2 { color: #00539b; font-size: 28px; margin-bottom: 20px; }
    @media only screen and (max-width: 800px) {
        section.P2 .liner { flex-direction: column; }
        section.P2 .imgbox, section.P2 .txtbox { width: 100%; padding-left: 0; margin-bottom: 30px; }
    }
</style>
@endpush
