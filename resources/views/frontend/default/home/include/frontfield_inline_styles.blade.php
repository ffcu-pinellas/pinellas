<style type=text/css>
	nav.pc  {height: 37px; width: 100%; z-index: 100; position: absolute; bottom: 10px; right: 0px; }
	.navliner {width: 100%;  width: 900px; position: relative; float: right; height: 37px; z-index: 100; display: flex; justify-content: space-between; align-items: center; align-content: space-around; border: solid 0px fuchsia}
	.pushmenu, .pushmenu .closebutton, .menutoggler{display: none;}
	button.CMitem, a.CM, a.CM:visited{display: inline-block; text-align: center;line-height: 1; padding: 10px 0px; margin: 0 10px;  color: #00539b; font-family: Montserrat, sans-serif; font-weight: 700; font-size: 15px; text-transform: uppercase; -webkit-transition:  all .5s; transition:  all .5s;  cursor: pointer; background: transparent; border: none;text-decoration: none;border-bottom: solid 2px white;}
	button.CMitem:hover, a.CM:hover, .CMitem:focus, a.CM:focus, button.CMitem.active, a.CM.active, a.CM.active:visited {color: #00539b; background: transparent; border: none; border-bottom: solid 2px #da291c; outline: none; text-decoration: none;}
	.CMcontent {display: none; position: absolute; top: 47px;  z-index: 100; width: 100%; max-width: 1170px; background-color: #00539b;  color: white; text-align: left; font-family: Montserrat, sans-serif; font-weight: 600; font-size: 15px; line-height: 24px; }
	header .CMcontent .liner {height: auto;}
	.CMcontent .liner {display: flex; justify-content: space-between; align-items: stretch;  align-content: stretch; width: 100%; padding: 30px 60px;}
	.flexbox {text-align: left; width: 100%; padding: 0 20px 0 0;}
	.flexbox.last{text-align: center; border-left: solid 1px white; padding: 0 20px;}
	.CMcontent a, .CMcontent a:visited {color: white; text-decoration: none; margin-bottom: 50px; display: block;}
	.CMcontent a:hover, .CMcontent a:focus {color: white;text-decoration: underline;}
	.CMcontent a span {display:block; font-size: 22px; line-height:  24px; width: 30px; text-align: center; margin-bottom: 6px;}
	/* for FRC*/
	#FRCmenu{ background: transparent; border: none; color: #da291c; transform: rotate(-90deg); position: absolute; top: 120px; left: -65px; font-size: 22px;  cursor: pointer; padding: 5px;cursor: pointer;}
	#FRCmenu span.out {transform: rotate(180deg);}
	.FRCmenu {width: 300px; position: fixed; top: 200px; right: -266px; border: solid 2px #00539b;border-right: none; padding: 20px 20px 20px 40px; border-radius: 10px 0 0 10px; background-color: white;-webkit-transition: right .5s; transition:  right .5s; z-index: 99; text-align: left;}
	.FRCmenu.out{right: 0;}
	.FRCmenu ul {list-style: none; padding: 0; margin: 0}
	@media only screen and (max-width: 1200px) {
		nav.pc{display: none;}
		.FRCmenu {display: none;}
		#contentwrapper{position: relative; -moz-transition: left 100ms ease-in-out, right 100ms ease-in-out; -webkit-transition: left 100ms ease-in-out, right 100ms ease-in-out;  transition: left 100ms ease-in-out, right 100ms ease-in-out;}
		.menutoggler {font-size: 36px; position: absolute; top: 65px; cursor: pointer; color: #00539b;  display: block; text-transform: uppercase; z-index: 101; }
		.wordmenu {font-family: Montserrat, sans-serif; font-weight: 600; font-size: 14px; position: relative; top: -8px; left: -5px}
		.menutoggler:hover, .menutoggler:focus{color: #009eda}
		.menutoggler.right{left: auto; right: 30px;}
		.pushmenu{background-color:#00539b; color: white; width: 280px; height: 100%; position: fixed; z-index: 1000; top: -100%; clear: both; display: block; visibility: 'hidden'; overflow-y: auto;  -moz-transition: all 100ms ease-in-out;  -webkit-transition: all 100ms ease-in-out;  transition: all 100ms ease-in-out;}
		.pushmenu.left{ box-shadow: 0 0 5px black;}
		.pushmenu.right{box-shadow: 0 0 5px black; width: 300px;}
		.pushmenu .closebutton{position: absolute; right: 15px; top: 12px; cursor: pointer; font-size: 24px; text-align: center; display: block;	color: #009eda}
		.pushmenu .closebutton:hover, .pushmenu .closebutton:focus{color: white}
		.side_menu {display: inline; width: 100%; overflow: hidden;}
		ul.mobimenu {text-align: left;  margin: 40px 0 0 0; padding: 10px 20px; list-style: none; font-size: 16px;}
		ul.mobimenu li {padding: 5px 10px; margin: 0; border-bottom: solid 1px #009eda;}
		ul.mobimenu span.far {display: none;}
		ul.mobimenu li p{display: inline; margin: 0; padding: 0}
		ul.mobimenu li a, ul.mobimenu li a:visited, ul.mobimenu li button{color: white; background: transparent; border: none; font-size: 14px;font-family: 'Open Sans', arial, sans-serif; position: relative; display: block; width: 100%; text-align: left; padding: 0; text-decoration: none;}
		ul.mobimenu li button .fa-caret-down { position: absolute; top: 0px; right: 0px; font-size: 20px;}
		ul.mobimenu li button.flipped .fa-caret-down { transform: rotate(180deg);}
		ul.mobimenu li a:hover, ul.mobimenu li a:focus, ul.mobimenu li button:hover, ul.mobimenu li button:focus{color: #009eda;text-decoration: none;}
		ul.mobimenu li ul {margin: 0; padding: 0 0 0 20px;display: none; }
		ul.mobimenu li ul li {list-style: none; border: none; padding: 5px 0}
	}
	@media only screen and (max-width: 400px) {
		.wordmenu {display: none}
		.menutoggler { top: 22px;}
		.menutoggler.right{ right: 20px;}
	}
</style>
