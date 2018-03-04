<footer id="footer" class="footer">
	<div id="footer-content" class="footer-content">
		<p id="footer-content-copyright" class="footer-content-copyright common-text">Copyright © by MEMEBoard</p>
		<p id="footer-content-credits" class="footer-content-credits common-text">Software created with 💙 by <a href="https://www.enricosola.com" id="footer-content-author-link" class="footer-content-author common-text" target="_blank" title="Visit creator's vebsite.">Enrico Sola</a>.</p>
		<p id="footer-content-download" class="footer-content-download common-text">Are you interested in this open source software? Download it from <a href="https://github.com/RyanJ93/MEMEBoard" target="_blank" title="Download from GitHub." class="footer-content-download-link common-text" id="footer-content-download-link">GitHub</a>.</p>
	</div>
	@if ( Auth::check() === true )
		<input type="hidden" id="auth-user" value="{{Auth::user()->id}}" />
		<input type="hidden" id="auth-admin" value="{{Auth::user()->admin}}" />
	@endif
</footer>