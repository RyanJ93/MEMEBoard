@extends('mail/template')
@section('body')
	<style type="text/css">
		#main-link-null, #main-link-null:active, #main-link-null:hove{
			cursor:pointer;
			text-decoration:none;
		}
		
		#main-image{
			width:100%;
			max-width:800px;
			border-radius:3px;
			background-color:#FFF;
			margin:auto;
			background-position:center;
			background-repeat:no-repeat;
			background-size:cover;
		}
		
		#main-image:after{
			content:'';
			display:block;
		}
		
		#main-image[ratio="1:1"]:after{
			padding-bottom:100%;
		}
		
		#main-image[ratio="4:3"]:after{
			padding-bottom:75%;
		}
		
		#main-image[ratio="16:9"]:after{
			padding-bottom:56%;
		}
		
		#main-image[ratio="16:10"]:after{
			padding-bottom:63%;
		}
		
		#main-categories{
			list-style:none;
			padding:0px;
			margin:0;
		}
		
		#main-categories[margin="true"]{
			margin-top:18px;
		}
		
		.main-categories-element{
			list-style:none;
			padding:0px;
			margin:0;
			display:inline;
			padding-left:6px;
		}
		
		.main-categories-element:first-child{
			padding:0px !important;
		}
		
		.main-categories-element-link, .main-categories-element-link:active, #main-user-link, #main-user-link:active{
			font-family:'Raleway', sans-serif;
			font-size:12px;
			color:#666;
			text-decoration:none;
			cursor:pointer;
			transition:color 250ms;
		}
		
		.main-categories-element-link:hover, #main-user-link:hover{
			color:rgb(23, 147, 190) !important;
		}
		
		#main-user{
			font-family:'Raleway', sans-serif;
			font-size:12px;
			color:#666;
			padding:0px;
			margin-top:18px;
		}
	</style>
	<header class="slide" id="header">
		<a id="main-logo-link" target="_blank" title="Visit the website." href="{{env('APP_URL')}}">
			<div id="main-logo"></div>
		</a>
	</header>
	<section class="slide">
		<p id="main-content-title">{{$globalTitle}}</p>
		<p id="main-title">{{$title}}</p>
		<a id="main-link-null" title="See the MEME." href="{{$memeLink}}" target="_blank">
			<div id="main-image" ratio="{{$ratio}}" style="background-image:url({{$image}});"></div>
		</a>
		@if ( isset($text) === true && $text !== '' )
			<p id="main-content-text">{{$text}}</p>
		@endif
		@if ( $user !== NULL )
			<p id="main-user">Created by <a title="More MEMEs from this user" id="main-user-link" target="_blank" href="{{env('APP_URL') . '/author/' . $user['id']}}">{{$user['name']}}</a></p>
		@endif
		<ul id="main-categories" margin="{{$user !== NULL ? 'false' : 'true'}}">
			@foreach ( $categories as $key => $value )
				<li class="main-categories-element">
					<a class="main-categories-element-link" target="_blank" href="{{env('APP_URL') . '/category/' . urlencode($value)}}" title="More MEMEs from this category.">{{$value}}</a>
				</li>
			@endforeach
		</ul>
	</section>
	<footer class="slide">
		<span class="footer-text">This e-mail message is part of our newsletter, if you don't want to receive these message anymore, use <a class="footer-link" target="_blank" href="{{env('APP_URL') . '/unsubscribe?token=' . urlencode($token)}}">this link</a> to remove your address from our newsletter.</span>
	</footer>
@endsection