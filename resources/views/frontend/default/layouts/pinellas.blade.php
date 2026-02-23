<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<meta name="description" content="@yield('meta_description')" />
	<meta name="keywords" content="@yield('meta_keywords')" />
	<meta name="viewport" content="initial-scale=1.0001" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<link rel="canonical" href="{{ url()->current() }}" />
	<title>@yield('title') - Pinellas Federal Credit Union</title>

	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon-16x16.png') }}">
	<link rel="manifest" href="{{ asset('manifest.json') }}">
	<meta name="theme-color" content="#00549b">

	<!-- jquery -->
	<script src="https://www.pinellasfcu.org/templates/COMMON_JS/jquery-1.11.3.min.js"></script>
	<script src="https://www.pinellasfcu.org/templates/pinellas/js/jqueriness.js"></script>

	<!-- universal styles -->
	<link rel="stylesheet" href="https://www.pinellasfcu.org/admin/css/universal_template.css">
	
    <!-- Locator Module Header Code Start -->
    <script>window.initMap = function() { console.log("Google Maps (Suppressed)"); };</script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDNnLqqlvFVicPs-6hZiiohT0pN4XGVUbw&callback=initMap" type="text/javascript"></script>
    <!-- Locator Module Header Code End -->
    
    <!-- Forms Module Header Code Start -->
    <link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/COMMON_JS/CSS/default_form.css" />
    <link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/COMMON_JS/CSS/default_form_side.css" />
    <!-- Forms Module Header Code End -->

    <!-- Tables Module Header Code Start -->
    <link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/COMMON_JS/CSS/default_table.css" />
    <!-- Tables Module Header Code End -->

    <!-- Modal Popups & Alerts Code Start -->
    <script src="https://www.pinellasfcu.org/templates/COMMON_JS/jquery.cookie.js" ></script>
    <!-- Modal Popups & Alerts Code End -->

    <!-- Modern Calendar Module Header Code Start -->
    <link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/COMMON_JS/CSS/modern_cal.css" />
    <!-- Modern Calendar Module Header Code End -->

    <!-- Generic CMS Styles Start -->
    <style>UL.content_simple_gallery{list-style:none}UL.content_simple_gallery LI{display:inline-block;margin-right:10px}LI.simple_gallery_view_more{vertical-align:middle;margin-left:40px}</style>
    <!-- Generic CMS Styles End -->

    <!-- font awesome -->
    <link href="https://www.pinellasfcu.org/templates/COMMON_JS/fontawesome-pro-5.15.1-web/css/all.min.css" rel="stylesheet">
    
    <!-- generic frontend scripting -->
    <script type="text/javascript" src="https://www.pinellasfcu.org/admin/js/frontend.js"></script>
    <!-- for validating forms -->
    <script type="text/javascript" src="https://www.pinellasfcu.org/form_system/js/uniValidate.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	
	<!-- STYLESHEETS -->
	<link rel="stylesheet" href="https://use.typekit.net/kdd4cmy.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/pinellas/css/style.css">
	<link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/pinellas/css/responsive.css">

	@include('frontend::home.include.pinellas_inline_styles')

<script src="https://www.pinellasfcu.org/templates/pinellas/js/pushmenu.js"></script>

<script>
jQuery(function(){ // on DOM load
	menu1 = new pushmenu({ // initialize menu example
	menuid: 'pushmenu1',
	position: 'right',
	marginoffset: 0,
	revealamt: -150,
	onopenclose:function(state){
	//console.log(state) //"open" or "closed"
	}
	})
})
</script>

<style type="text/css">
#sitesearch {font-size: 10px;  color: white; cursor: pointer; -webkit-transition:  all .5s; transition:  all .5s; background: #da291c; border: none; padding: 0; width: 22px;height: 22px; border-radius: 50%; display: inline; margin-left: 5px; position: relative; float: right;}
#sitesearch:hover, #sitesearch:focus { background:#009eda;  color: white;}
.toplinks #sitesearch .fas {display: block;}
.sitesearch {display: inline-block; width: 200px; height: 24px; background-color: white; border: solid 1px #333; border-radius: 5px; z-index: 99; position: relative;  margin: 0; padding: 0}
.sitesearch label {display: none; }
.searchbox {width: 170px; height: 24px; border: 0; position: absolute; left: 5px; top: 0px; background: transparent; padding-left: 10px;}
.sitesearch button{position: absolute; right: 3px; top: 0; border: 0; background-color: transparent; color: #da291c !important; height: 24px; width: 24px; font-size: 26px;  padding: 0;  }
.sitesearch button:hover, .sitesearch button:focus {color: #009eda; cursor: pointer}
@media only screen and (max-width: 1000px) {
    #sitesearch { background: none; height: auto; border-radius: 0;  margin: 0; color: white; text-decoration: none; display: block;  width: 16%; font-size: 24px; }
    #sitesearch:hover, #sitesearch:focus { background:none}
    .sitesearch {display: block;  position: absolute; bottom: -30px; right: 30px; }
}
@media only screen and (max-width: 600px) {
    .sitesearch { right: 20px; }
}
</style>

<link rel="stylesheet" type="text/css" href="https://www.pinellasfcu.org/templates/pinellas/css/slick.css" />
<script src="https://www.pinellasfcu.org/templates/COMMON_JS/slick.js" type="text/javascript" charset="utf-8"></script>

@stack('style')
</head>

<body>

	<div id="contentwrapper">
        @include('frontend::home.include.pinellas_header')

        @yield('content')

        @include('frontend::home.include.pinellas_footer')
    </div>

    @include('frontend::home.include.pinellas_mobile_menu')

    <script type="text/javascript">!function(){var b=function(){window.__AudioEyeSiteHash = "a0766210036659e0a1e317dafb330ab7"; var a=document.createElement("script");a.src="https://wsmcdn.audioeye.com/aem.js";a.type="text/javascript";a.setAttribute("async","");document.getElementsByTagName("body")[0].appendChild(a)};"complete"!==document.readyState?window.addEventListener?window.addEventListener("load",b):window.attachEvent&&window.attachEvent("onload",b):b()}();</script>

    <script>
    $(document).ready(function() {
        $(".code_block_accordion .code_block_indiv_top").click(function() {
            $(this).parent().toggleClass("open");
            $(this).parent().children(".code_block_indiv_content").slideToggle();
            $(this).parent().children(".code_block_indiv_image").slideToggle();
        });
    })
    </script>
    <script src="https://tether.netteller.com/pinellasfcu/login.js"></script>
    @stack('script')
</BODY>
</HTML>
