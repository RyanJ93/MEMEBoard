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
			<p class="error-title common-text" id="error-title">503</p>
			<p class="error-subtitle common-text" id="error-subtitle">An error occurred while processing the request, please retry later.</p>
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