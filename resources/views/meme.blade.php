<!doctype html>
<html>
	<head>
		@component('components/meta', array('image' => $metaImage))
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | {{$meme->title}}</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$meme->title}}" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | {{$meme->title}}" />
		@endcomponent
	</head>
	<body onload="Comment.loadComments();">
		@component('components/header')
		@endcomponent
		<div class="slide">
			<div id="slide-column" class="slide-column meme-column-content">
				@switch ( $meme->type )
					@case(1)
					@case(2)
						<div id="meme-image" class="meme-image" ratio="{{$meme->ratio}}" style="background-image:url({{( $meme->relativePath === true ? ( '/' . $meme->processedPath ) : $meme->processedPath )}})"></div>
					@break
					@case(3)
						<div id="meme-video" class="meme-video" ratio="{{$meme->ratio}}">
							<video preload="auto" controls="true" src="{{( $meme->relativePath === true ? ( '/' . $meme->processedPath ) : $meme->processedPath )}}" id="meme-video-player" muted="true" autoplay="true"></video>
						</div>
					@break
				@endswitch
			</div><div id="slide-column" class="slide-column meme-column-info">
				<p id="slide-column-title" class="meme-title common-text">{{$meme->title}}</p>
				<p id="slide-column-date" class="meme-date common-text">{{$meme->created_at->diffForHumans()}}</p>
				@if ( $meme->text !== NULL && $meme->text !== '' )
					<p id="slide-column-text" class="meme-text common-text">{{$meme->text}}</p>
				@endif
				@if ( $creator !== NULL )
					<p id="slide-column-user" class="meme-user common-text">Created by: <a id="slide-column-user-link" class="meme-user-link common-text" title="More memes from this user." href="/author/{{$creatorID}}">{{$creator}}</a>.</p>
				@endif
				<ul id="slide-column-categories" class="meme-categories">
					@foreach ( $meme->categories as $category )
						<li class="slide-column-categories-element">
							<a class="slide-column-categories-element-link meme-category common-text" href="/category/{{$category->id}}" title="More memes from this category.">{{$category->name}}</a>
						</li>
					@endforeach
				</ul>
				@if ( Auth::check() === true && Auth::user()->admin === 1 )
					<a class="meme-remove common-text" id="slide-column-remove" title="Remove this MEME." href="javascript:void(0);" onclick="MEME.remove(null);">Remove</a>
				@endif
				<div id="slide-column-counters" class="meme-counters">
					<ul id="slide-column-counters-list">
						<li class="slide-column-counters-list-element meme-counter">
							<div class="slide-column-counters-list-element-icon meme-counter-icon" scope="upVote" onclick="Vote.toggleUpVote(null);" selected="{{$votes['positive'] === true ? 'true' : 'false'}}"></div>
							<span class="slide-column-counters-list-element-counter meme-counter-text common-text" scope="upVote" counter="{{$meme->up_votes}}">{{$counters['up_votes']}}</span> 
						</li>
						<li class="slide-column-counters-list-element meme-counter">
							<div class="slide-column-counters-list-element-icon meme-counter-icon" scope="downVote" onclick="Vote.toggleDownVote(null);" selected="{{$votes['negative'] === true ? 'true' : 'false'}}"></div>
							<span class="slide-column-counters-list-element-counter meme-counter-text common-text" scope="downVote" counter="{{$meme->down_votes}}">{{$counters['down_votes']}}</span> 
						</li>
						<li class="slide-column-counters-list-element meme-counter">
							<div class="slide-column-counters-list-element-icon meme-counter-icon" scope="comment"></div>
							<span class="slide-column-counters-list-element-counter meme-counter-text common-text" scope="comment" counter="{{$meme->comments}}">{{$counters['comments']}}</span> 
						</li>
						<li class="slide-column-counters-list-element meme-counter">
							<span class="slide-column-counters-list-element-counter meme-counter-text common-text">Views: {{$counters['views']}}</span> 
						</li>
					</ul>
				</div>
				<div id="slide-column-share-buttons" class="meme-share-buttons">
					<div class="a2a_kit a2a_kit_size_32 a2a_default_style">
						<a class="a2a_dd" href="https://www.addtoany.com/share"></a>
						<a class="a2a_button_facebook"></a>
						<a class="a2a_button_twitter"></a>
						<a class="a2a_button_google_plus"></a>
						<a class="a2a_button_pinterest"></a>
						<a class="a2a_button_linkedin"></a>
						<a class="a2a_button_telegram"></a>
						<a class="a2a_button_vk"></a>
						<a class="a2a_button_whatsapp"></a>
					</div>
					<script async src="https://static.addtoany.com/menu/page.js"></script>
				</div>
				<div id="slide-column-comments" class="meme-comments">
					<p id="slide-column-comments-title" class="meme-comments-title common-text">Comments</p>
					<div id="slide-column-comments-loader" class="meme-comments-loader">
						<div id="slide-column-comments-loader-spinner" class="common-spinner" title="Loading comments..."></div>
					</div>
					<div id="slide-column-comments-error" class="meme-comments-error">
						<p id="slide-column-comments-error-text" class="meme-comments-error-text common-text">Unable to load comments.</p>
						<button id="slide-column-comments-error-button" class="meme-comments-error-button common-text" title="Retry" onclick="Comment.loadComments();">Retry</button>
					</div>
					<ul id="slide-column-comments-list" class="meme-comments-list"></ul>
					<div id="slide-column-comments-editor" class="meme-comments-editor">
						<form action="/comment" accept-charset="utf-8" method="post" autocomplete="on" onsubmit="Comment.triggerCreation(event);">
							<p id="slide-column-comments-editor-user" class="meme-comments-editor-user common-text">Post as {{$user === '' ? 'anonymous' : $user}}</p>
							<textarea placeholder="Write a comment..." name="comment" id="slide-column-comments-editor-comment" class="meme-comments-editor-textarea common-text" onkeypress="Comment.handleKeyPress(event);" required="true"></textarea>
						</form>
					</div>
				</div>
			</div>
			<input type="hidden" id="meme-id" value="{{$meme->id}}" />
		</div>
		@component('components/forms')
		@endcomponent
		@component('components/cookiePolicy')
		@endcomponent
		@component('components/footer')
		@endcomponent
		<script type="text/javascript" src="/js/library.min.js"></script>
	</body>
</html>