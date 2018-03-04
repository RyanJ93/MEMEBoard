<!doctype html>
<html>
	<head>
		@component('components/meta')
			<title>{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new MEME</title>
			<meta property="og:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new MEME" />
			<meta name="twitter:title" content="{{env('BASE_TITLE', 'MEMEBoard')}} | Create a new MEME" />
		@endcomponent
	</head>
	<body>
		@component('components/header')
		@endcomponent
		<div class="slide">
			<div id="create-container" class="create-container">
				<div class="create-container-section create-section" scope="upload">
					<div id="create-uploader" class="create-uploader" ondragover="MEME.dragOverUploader(event);" ondragleave="MEME.dragLeaveUploader(event);" ondragend="MEME.dragLeaveUploader(event);" ondrop="MEME.setUploaderFile(event);">
						<div id="create-uploader-inner">
							<div id="create-uploader-inner-content">
								<div class="create-uploader-inner-content-wrapper" id="create-uploader-inner-content-wrapper-picker">
									<p class="create-uploader-title common-text" id="create-uploader-inner-content-wrapper-title">Drop your MEME here</p>
									<p class="create-uploader-text common-text" id="create-uploader-inner-content-wrapper-text">or simply</p>
									<button class="create-uploader-inner-content-wrapper-button create-uploader-button common-text" onclick="MEME.openFilePicker();" title="Pick a file">Pick a file</button>
								</div>
								<div class="create-uploader-inner-content-wrapper" id="create-uploader-inner-content-wrapper-preview">
									<div id="create-uploader-inner-content-wrapper-preview-image" ratio="1:1" class="create-uploader-inner-content-wrapper-preview-element create-uploader-preview-image"></div>
									<div id="create-uploader-inner-content-wrapper-preview-video" ratio="1:1" class="create-uploader-inner-content-wrapper-preview-element create-uploader-preview-video">
										<video id="create-uploader-inner-content-wrapper-preview-video-player" onloadedmetadata="MEME.processVideoPreview();" onclick="MEME.playVideoPreview(false);" ondblclick="MEME.playVideoPreview(true);" title="Click to play, double click to rewind and play."></video>
									</div>
									<div id="create-uploader-inner-content-wrapper-preview-loader" class="create-uploader-inner-content-wrapper-preview-element create-uploader-preview-loader">
										<div id="create-uploader-inner-content-wrapper-preview-loader-spinner" class="common-spinner" title="Processing the video..."></div>
									</div>
									<button class="create-uploader-inner-content-wrapper-button create-uploader-button common-text" onclick="MEME.removeFileFromUploader();" title="Remove file">Remove file</button>
								</div>
							</div>
						</div>
						<input type="file" name="element" id="create-uploader-input" onchange="MEME.setUploaderFile(event);" />
					</div>
				</div><div class="create-container-section create-section create-section-info" scope="info">
					<p class="create-container-section-title create-title common-text">Upload a new MEME</p>
					<form accept-charset="utf-8" method="post" action="{{route('memes.store')}}" id="create-container-section-form" autocomplete="off" onsubmit="MEME.triggerCreation(event);">
						<label for="create-container-section-form-title" class="create-container-section-form-label create-label common-text">Give a title to your meme (optional)</label>
						<input type="text" placeholder="Title" class="create-container-section-form-input create-input common-text" name="title" id="create-container-section-form-title" />
						<label for="create-container-section-form-text" class="create-container-section-form-label create-label common-text">Add a description to your meme (optional, max 1000 length)</label>
						<textarea placeholder="Description" class="create-container-section-form-textarea create-textarea common-text" name="text" id="create-container-section-form-text"></textarea>
						<label for="create-container-section-form-category" class="create-container-section-form-label create-label common-text">Select a category for your MEME (up to 3 categories)</label>
						<select id="create-container-section-form-category" class="create-container-section-form-select create-select-multiple common-text" name="category[]" multiple="true">
							@foreach ( $categories as $category )
								<option value="{{$category->id}}" class="common-text">{{$category->name}}</option>
							@endforeach
						</select>
						<label for="create-container-section-form-new-category" class="create-container-section-form-label create-label common-text">Add a new category</label>
						<input type="text" placeholder="New category" class="create-container-section-form-input create-input common-text" name="new-category" id="create-container-section-form-new-category" onkeypress="MEME.addCategory(event);" />
						<span class="create-note common-text">Note that this category will be created only if selected and if this element will be created.</span>
						<label for="create-container-section-form-ratio" class="create-container-section-form-label create-label common-text">Select MEME ratio</label>
						<select id="create-container-section-form-ratio" class="create-container-section-form-select create-select common-text" name="ratio" onchange="MEME.setRatio();">
							<option value="1:1">1:1 (800px * 800px)</option>
							<option value="4:3">4:3 (800px * 800px)</option>
							<option value="16:9">16:9 (800px * 800px)</option>
							<option value="16:10">16:10 (800px * 800px)</option>
						</select>
						<br /><br />
						<input type="submit" value="Create MEME" id="create-container-section-form-button" class="create-button common-text" title="Create MEME" />
					</form>
				</div>
			</div>
			<div id="create-loader" class="create-loader">
				<div class="create-loader-progress" id="create-loader-progress">
					<div class="create-loader-progress-value" id="create-loader-progress-value"></div>
				</div>
				<p class="create-loader-action common-text" id="create-loader-action-uploading">Uploading your MEME...</p>
				<p class="create-loader-action common-text" id="create-loader-action-processing">Processing your MEME...</p>
			</div>
			<input type="hidden" id="video-max-length" value="{{env('MAX_VIDEO_LENGTH', 180)}}" />
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