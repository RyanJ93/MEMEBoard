@extends('mail/template')
@section('body')
	<header class="slide" id="header">
		<a id="main-logo-link" target="_blank" title="Visit the website." href="{{env('APP_URL')}}">
			<div id="main-logo"></div>
		</a>
	</header>
	<section class="slide">
		<p id="main-content-title">{!! $title !!}</p>
		<p id="main-content-text">{!! $text !!}</p>
	</section>
	<footer class="slide">
		<span class="footer-text">This e-mail message may contain confidential informations, if you received this message accidentally, please delete it.</span>
	</footer>
@endsection