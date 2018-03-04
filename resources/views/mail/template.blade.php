<!doctype html>
<html>
	<head>
		<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet" />
		<style type="text/css">
			#header{
				text-align:center;
				margin-bottom:24px;
			}
			
			.slide{
				width:100%;
				max-width:800px;
				margin:auto;
				padding:12px;
				box-sizing:border-box;
			}
			
			#main-logo{
				width:128px;
				height:128px;
				background-position:center;
				background-size:cover;
				background-repeat:no-repeat;
				margin:auto;
				margin-bottom:24px;
				background-image:url({{env('APP_URL') . '/' . env('MAIL_LOGO')}});
			}
			
			#main-logo-link, #main-logo-link:active, #main-logo-link:hover{
				cursor:pointer;
				text-decoration:none;
			}
			
			#main-title, #main-title:active{
				font-family:'Raleway', sans-serif;
				font-size:24px;
				color:#666;
				text-decoration:none;
				cursor:pointer;
				transition:color 250ms;
			}
			
			#main-title:hover{
				color:rgb(23, 147, 190) !important;
			}
			
			#main-content-title{
				font-family:'Raleway', sans-serif;
				font-size:16px;
				color:#666;
				margin:0;
				margin-bottom:6px;
			}
			
			#main-content-text{
				font-family:'Raleway', sans-serif;
				font-size:12px;
				color:#666;
				margin:0;
			}
			
			#main-content-link, #main-content-link:active{
				font-family:'Raleway', sans-serif;
				font-size:12px;
				color:#666;
				text-decoration:none;
				cursor:pointer;
				transition:color 250ms;
			}
			
			#main-content-link:hover{
				color:rgb(23, 147, 190) !important;
			}
			
			.footer-text{
				font-family:'Raleway', sans-serif;
				font-size:12px;
				color:#666;
			}
			
			.footer-link, footer-link:active{
				font-family:'Raleway', sans-serif;
				font-size:12px;
				color:#666;
				text-decoration:none;
				cursor:pointer;
				transition:color 250ms;
			}
			
			.footer-link:hover{
				color:rgb(23, 147, 190) !important;
			}
		</style>
	</head>
	<body>
		@yield('body')
	</body>
</html>