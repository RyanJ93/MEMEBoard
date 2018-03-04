<header id="header" class="header">
	<nav id="header-content" class="header-content">
		<ul id="header-content-nav" class="header-nav">
			<li class="header-content-nav-element header-nav-element">
				<a class="header-content-nav-element-link-null" title="Go to home page." href="/">
					<div id="header-content-nav-elemen-icon" class="header-icon" title="Go to home page."></div>
				</a>
			</li>
			<li class="header-content-nav-element header-nav-element header-nav-element-menu header-content-nav-element-text" selected="{{Request::path() === '/' ? 'true' : 'false'}}">
				<a class="header-content-nav-element-link header-link header-link-menu common-text" title="Home" href="/">Home</a>
			</li>
			<li class="header-content-nav-element header-nav-element header-nav-element-menu header-content-nav-element-text" selected="{{Request::path() === 'categories' ? 'true' : 'false'}}">
				<a class="header-content-nav-element-link header-content-nav-element-separator header-link header-link-menu header-separator common-text" title="Categories" href="/categories">Categories</a>
			</li>
			<li class="header-content-nav-element header-nav-element header-nav-element-menu header-content-nav-element-text" selected="{{Request::path() === 'about' ? 'true' : 'false'}}">
				<a class="header-content-nav-element-link header-content-nav-element-separator header-link header-link-menu header-separator common-text" title="About" href="/about">About</a>
			</li>
			<li class="header-content-nav-element header-nav-element header-nav-element-menu header-content-nav-element-text" selected="{{Request::path() === 'search' ? 'true' : 'false'}}">
				<a class="header-content-nav-element-link header-content-nav-element-separator header-link header-link-menu header-separator common-text" title="Search" href="/search">Search</a>
			</li>
			<li class="header-content-nav-element-empty"></li>
			@if ( Auth::check() !== true )
				<li class="header-content-nav-element header-nav-element header-content-nav-element-text">
					<a class="header-content-auth-element-link header-auth-link common-text" scope="login" href="javascript:void(0);" title="Login" onclick="UI.form.showLogin();">Login</a>
				</li>
				<li class="header-content-nav-element header-nav-element header-content-nav-element-text">
					<a class="header-content-auth-element-link header-content-nav-element-separator header-auth-link common-text" scope="register" href="javascript:void(0);" title="Register" onclick="UI.form.showRegistration();">Register</a>
				</li>
			@else
				@if ( Auth::user()->admin === 1 )
					<li class="header-content-nav-element header-nav-element header-content-nav-element-text">
						<a class="header-content-auth-element-link header-auth-link common-text" href="{{route('memes.create')}}" title="Create a MEME">Create MEME</a>
					</li>
				@endif
				<li class="header-content-nav-element header-nav-element header-content-nav-element-text">
					<p class="header-content-auth-element-text {{Auth::user()->admin === 1 ? 'header-content-nav-element-separator header-separator ' : ''}} header-auth-text common-text"><a class="header-content-auth-element-link header-auth-link common-text" href="{{route('user.currentProfile')}}" title="Your profile">Profile</a> (<a class="header-content-auth-element-link header-auth-link common-text" scope="logout" href="javascript:void(0);" title="Logout" onclick="User.authenticator.logout();">Logout</a>)</p>
				</li>
			@endif
			<li class="header-content-nav-element header-menu-opener header-nav-element header-content-nav-element-menu">
				<div id="header-content-menu-opener" class="header-menu-opener-icon" title="Open menu" onclick="UI.menu.toggle();"></div>
			</li>
		</ul>
		<div id="header-content-mobile" class="header-content-mobile">
			<ul id="header-content-mobile-list">
				<li class="header-content-mobile-list-element header-mobile-element">
					<a class="header-content-mobile-list-element-link header-mobile-link common-text" title="Popular MEMEs" href="/" onclick="UI.menu.setOrdering(event, 'popular');">Popular MEMEs</a>
				</li>
				<li class="header-content-mobile-list-element header-mobile-element">
					<a class="header-content-mobile-list-element-link header-mobile-link common-text" title="Newer MEMEs" href="/#new" onclick="UI.menu.setOrdering(event, 'new');">Newer MEMEs</a>
				</li>
				<li class="header-content-mobile-list-element header-mobile-element">
					<a class="header-content-mobile-list-element-link header-mobile-link common-text" title="Categories" href="/categories">Categories</a>
				</li>
				<li class="header-content-mobile-list-element header-mobile-element">
					<a class="header-content-mobile-list-element-link header-mobile-link common-text" title="About" href="/about">About</a>
				</li>
				<li class="header-content-mobile-list-element header-mobile-element">
					<a class="header-content-mobile-list-element-link header-mobile-link common-text" title="Search" href="/search">Search</a>
				</li>
				@if ( Auth::check() !== true )
					<li class="header-content-mobile-list-element header-mobile-element header-mobile-element-margin">
						<a class="header-content-mobile-list-element-link header-mobile-link common-text" href="javascript:void(0);" title="Login" scope="login" onclick="UI.form.showLogin();">Login</a>
					</li>
					<li class="header-content-mobile-list-element header-mobile-element">
						<a class="header-content-mobile-list-element-link header-mobile-link common-text" href="javascript:void(0);" title="Register" scope="register" onclick="UI.form.showRegistration();">Register</a>
					</li>
				@else
					@if ( Auth::user()->admin === 1 )
						<li class="header-content-mobile-list-element header-mobile-element header-mobile-element-margin">
							<a class="header-content-mobile-list-element-link header-mobile-link common-text" href="{{route('memes.create')}}" title="Create a MEME">Create MEME</a>
						</li>
					@endif
					<li class="header-content-mobile-list-element header-mobile-element{{Auth::user()->admin === 1 ? '' : ' header-mobile-element-margin'}}">
						<a class="header-content-mobile-list-element-link header-mobile-link common-text" href="{{route('user.currentProfile')}}" title="Your profile">Profile</a>
					</li>
					<li class="header-content-mobile-list-element header-mobile-element">
						<a class="header-content-mobile-list-element-link header-mobile-link common-text" href="javascript:void(0);" title="Logout" scope="logout" onclick="User.authenticator.logout();">Logout</a>
					</li>
				@endif
			</ul>
		</div>
	</nav>
</header>
<div class="header-overlay" id="header-overlay" onclick="UI.menu.close();"></div>