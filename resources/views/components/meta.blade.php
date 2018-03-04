<meta charset="UTF-8" />
<meta name="keywords" content="" />
<meta name="description" content="{{env('META_DESCRIPTION', '')}}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="subject" content="{{env('META_SUBJECT', '')}}" />
<meta name="copyright" content="{{env('META_COPYRIGTH', '')}}" />
<meta name="language" content="{{env('META_LANG', '')}}" />
<meta name="author" content="{{env('META_AUTHOR', '')}}" />
<meta name="designer" content="{{env('META_DESIGNER', '')}}" />
<meta name="reply-to" content="{{env('META_REPLY_TO', '')}}" />
<meta name="owner" content="{{env('META_OWNER', '')}}" />
<meta name="coverage" content="Worldwide" />
<meta name="distribution" content="Global" />
<meta name="rating" content="General" />
<meta name="revisit-after" content="7 days" />
<meta name="target" content="all" />
<meta name="HandheldFriendly" content="True" />
<meta name="MobileOptimized" content="320" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1.0" />
<meta name="robots" content="index, follow" />
<meta property="og:description" content="{{env('META_DESCRIPTION', '')}}" />
<meta property="og:type" content="website" />
<meta property="og:locale" content="{{env('META_LANG', '')}}" />
<meta property="og:site_name" content="{{env('META_SITE_NAME', '')}}" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="{{env('META_TWITTER_SITE', '')}}" />
<meta name="twitter:creator" content="{{env('META_TWITTER_CREATOR', '')}}" />
<meta name="twitter:description" content="{{env('META_DESCRIPTION', '')}}" />
@if ( env('GOOGLE', '') !== '' )
	<meta name="google-site-verification" content="{{env('GOOGLE')}}" />
@endif
@if ( env('YANDEX', '') !== '' )
	<meta name="yandex-verification" content="{{env('YANDEX')}}" />
@endif
@if ( env('BING', '') !== '' )
	<meta name="msvalidate.01" content="{{env('BING')}}" />
@endif
@if ( env('NORTON', '') !== '' )
	<meta name="norton-safeweb-site-verification" content="{{env('NORTON')}}" />
@endif
<link rel="alternate" href="" hreflang="{{env('META_LANG', '')}}" />
<link rel="alternate" href="" hreflang="x-default" />
<link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml" />
<meta name="csrf-token" content="{{csrf_token()}}" />
<meta property="og:url" content="{{( mb_strpos(env('APP_URL'), 'http://') !== 0 && mb_strpos(env('APP_URL'), 'https://') !== 0 ? 'http://' . env('APP_URL') : env('APP_URL') ) . $_SERVER['REQUEST_URI']}}" />
<link rel="canonical" href="{{$_SERVER['REQUEST_URI']}}" />
<meta name="url" content="{{$_SERVER['REQUEST_URI']}}" />
<meta name="identifier-URL" content="{{$_SERVER['REQUEST_URI']}}" />
<link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="/icons/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
<link rel="manifest" href="/icons/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
@if ( env('RSS_ENABLED') === true )
	<link rel="alternate" type="application/rss+xml" href="/feed.rss" />
@endif
{{$slot}}
@if ( isset($image) === true && $image !== '' )
	<meta property="og:image" content="{{$image}}" />
	<meta name="twitter:image" content="{{$image}}" />
@else
	@if ( env('META_OG_IMAGE', '') !== '' )
		<meta property="og:image" content="{{env('APP_URL') . '/' . env('META_OG_IMAGE')}}" />
	@endif
	@if ( env('META_TWITTER_IMAGE', '') !== '' )
		<meta name="twitter:image" content="{{env('APP_URL') . '/' . env('META_TWITTER_IMAGE')}}" />
	@endif
@endif
@if ( env('GOOGLE_ANALYICS', '') !== '' )
	<script async src="https://www.googletagmanager.com/gtag/js?id={{env('GOOGLE_ANALYICS')}}"></script>
	<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', '{{env('GOOGLE_ANALYICS')}}');</script>
@endif
@if ( env('YANDEX_METRICA', '') !== '' )
	<script type="text/javascript">(function(d,w,c){(w[c]=w[c] || []).push(function(){try{w.yaCounter47829784=new Ya.Metrika({id:{{env('YANDEX_METRICA')}},clickmap:true,trackLinks:true,accurateTrackBounce:true});}catch(e){}});var n=d.getElementsByTagName("script")[0],s = d.createElement("script"),f=function(){n.parentNode.insertBefore(s,n);};s.type="text/javascript";s.async = true;s.src="https://mc.yandex.ru/metrika/watch.js";if(w.opera=="[object Opera]"){d.addEventListener("DOMContentLoaded",f,false);}else{f();}})(document,window,"yandex_metrika_callbacks");</script><noscript><div><img src="https://mc.yandex.ru/watch/{{env('YANDEX_METRICA')}}" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
@endif
<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
<link rel="stylesheet" href="/css/library.min.css" />
<link rel="stylesheet" href="/css/theme.min.css" />