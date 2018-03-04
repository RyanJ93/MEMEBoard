<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>Meme board | Not found</title>
			<meta property="og:title" content="Meme board | Not found" />
			<meta name="twitter:title" content="Meme board | Not found" />
		@endcomponent
		<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
		<link rel="stylesheet" href="/css/library.css" />
		<link rel="stylesheet" href="/css/theme.css" />
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide error-center">
			<p class="error-title common-text" id="error-title">403</p>
			<p class="error-subtitle common-text" id="error-subtitle">You are not allowed to see this page.</p>
			<br />
			<a class="error-button-link common-text" id="error-button-link" title="Go back to homepage" href="/">Go back to homepage</a>
		</div>
		@component('components/forms')
		@endcomponent
		@component('components/cookiePolicy')
		@endcomponent
		@component('components/footer')
		@endcomponent
		<script type="text/javascript" src="/js/library.js"></script>
	</body>
</html>