<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | About</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | About" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | About" />
		@endcomponent
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide">
			<p class="about-title common-text">MEME board</p>
			<p class="about-subtitle common-text">A very simple multimedia board written in PHP while learning more about Laravel 5.5</p>
			<div id="about-image" class="about-image"></div>
			<p class="about-text common-text" id="about-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec id sagittis nulla. Praesent sagittis augue arcu, a gravida ligula commodo et. Phasellus elit mauris, porttitor non aliquet ac, dignissim eu dui. Nam et nibh eget nulla consectetur iaculis. Nam in est tempor, volutpat nulla sed, tincidunt metus. Etiam at blandit eros. Maecenas molestie posuere est, in auctor purus congue quis. Integer gravida ipsum id aliquet venenatis. <br /><br />Cras eleifend, sem vel pellentesque hendrerit, diam velit blandit mauris, sit amet feugiat neque est vitae dolor. Integer in odio orci. Aliquam erat volutpat. In hac habitasse platea dictumst. Fusce nec ipsum sit amet nibh volutpat condimentum. Praesent vehicula ligula in sapien vulputate placerat. Aliquam erat volutpat. Cras porttitor sollicitudin orci, ut ullamcorper nunc euismod at. Quisque sed diam sed nisi vehicula rhoncus vel gravida ex. Integer purus turpis, pharetra ut semper in, fermentum in sapien. Nullam elementum sodales varius. Vestibulum ultricies nec nibh vitae condimentum. Donec suscipit quam nec pretium tincidunt. Vestibulum vitae auctor velit. Sed bibendum a ante vitae vestibulum. <br /><br />Sed commodo porta nunc. Proin urna metus, congue dictum tortor vel, condimentum cursus elit. Nullam non fermentum est, eu laoreet sapien. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus tristique eget dui vitae aliquet. Cras eget aliquam mi, eget rhoncus quam. Morbi pulvinar lacus id urna pretium, eu suscipit ante lobortis. Praesent non maximus ligula, sed iaculis lorem. Maecenas viverra laoreet mi a elementum. Sed sem magna, interdum at facilisis a, rhoncus quis erat. Mauris in porttitor lorem. Sed pretium orci id elit commodo accumsan. Aliquam ipsum leo, rhoncus a semper in, pellentesque non risus. Mauris id maximus massa. <br /><br />Nam eget lacus orci. Mauris scelerisque facilisis quam id accumsan. Ut rhoncus ex sagittis dolor dapibus, vitae posuere nisi pharetra. Donec pellentesque nec lectus nec semper. Nullam molestie ornare enim, sed sagittis sem consequat vel. Nulla ultricies, lacus ut sagittis fermentum, velit mauris ornare dui, at varius lacus lacus ac enim. Phasellus bibendum, nisl at tempus imperdiet, lorem mi sagittis ipsum, sit amet luctus ex elit non purus. Vestibulum libero dui, ultrices ac lacus id, semper mattis tellus. Suspendisse interdum dui sit amet tellus pretium, ac venenatis lorem ullamcorper. Duis non efficitur nisi. Phasellus at nibh efficitur, pharetra purus id, mollis lorem. Aliquam ac dolor elementum mi luctus lacinia. Vestibulum viverra lacinia viverra. Donec aliquet sem lacus, eu maximus neque tempor eget. Maecenas fringilla faucibus dolor et tincidunt. Praesent vitae eleifend ligula. <br /><br />Mauris tempor magna dolor, a porttitor tellus suscipit sed. Sed dictum justo et ligula commodo iaculis. Nullam non commodo metus. Nunc auctor laoreet magna, at aliquet dolor viverra id. Nulla imperdiet ante non rhoncus molestie. Aliquam sit amet sem eget libero sodales rutrum. Integer sit amet mauris eu lorem eleifend porttitor. Nulla quis urna tempor, maximus nisl vel, feugiat quam. Donec at dolor maximus, consectetur augue ac, malesuada magna. In ultricies lorem nec egestas eleifend.</p>
			<form accept-charset="utf-8" id="about-contact" class="about-contact" method="post" autocomplete="on" onsubmit="Contact.triggerSubmit(event);" action="{{route('contact')}}">
				<p class="about-title common-text">Contact us</p>
				<p class="about-subtitle common-text">Wanna suggest something or have some questions? Feel free to drop us a line.</p>
				<br />
				<input type="text" class="about-input common-text" name="name" id="about-contact-name" placeholder="Your name" required="true" maxlength="30" value="{{$user['name']}}" />
				<input type="email" class="about-input common-text" name="email" id="about-contact-email" placeholder="Your e-mail address" required="true" value="{{$user['email']}}" />
				<textarea class="about-textarea common-text" name="message" id="about-contact-message" placeholder="What's up?" required="true" maxlength="10000"></textarea>
				<p class="about-privacy common-text" id="about-contact-privacy">By sending this message you allow us to handle your personal information according to privacy laws.</p>
				<input type="submit" value="Send" title="Send" id="about-contact-button" class="about-button common-text" />
			</form>
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