@extends('layouts.default', ['body_class' => 'nav-profile'])
@section('head-content')
  <link href="/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css">
  <script src="/css/jquery/external/jquery/jquery.js"></script>
  <script src="/css/jquery/jquery-ui.js"></script>
  <script src="/js/profile.js"></script>
  <script src="/js/profile_save_button.js"></script>
  <script src="/js/profile_save_prompt.js"></script>
  <script src="/js/utils.js"></script>
  <script src="/js/question_explanation.js"></script>
@stop
@section('content')
<div class="profile row">
	<div class="col-md-3 col-sm-4 col-xs-12">
		@if ($has_profile_photo)
			<div class="photo-display">
			    <p class="remove-photo"><a href="/profile-photo/delete">Remove Photo</a></p>
				<div id="profile-photo-rotate" onclick="rotateImage()"><i class="fa fa-repeat fa-4x"></i></div>
				<div class="photo-changer" onclick="selectImageFile()">
					<div class="uploaded-photo">
					</div>
					<div class="progress-element"></div>
					<p>Change Photo</p>
				</div>
			</div>
		@else

        <div class="photo-display" onclick="selectImageFile()">
            <div class="user-icon">
                <div><i class="fa fa-user"></i></div>
                <p>Choose File</p>
            </div>
        </div>
        @endif
		<p class="text-danger text-center" id="profile_error"></p>
		<form id="photo-upload" method="post" action="/profile-photo-upload" enctype="multipart/form-data">
			{!! csrf_field() !!}
            <input class="hidden-uploader" type="file" name="profile_photo" onchange="upload(event)">
		</form>
 	</div>
    <div class="col-md-9 col-sm-8 col-xs-12">
		@if ( $is_internal_user )
			<a class="internal-dashboard-link" href="/dashboard"><em class="fa fa-gears"></em></a>
		@endif
        <h1>{{ $user->first_name.' '.$user->last_name }}<a href="/profile/names" title="Edit first and last name"><em class="fa fa-edit"></em></a></h1>
		<p class="home-address">
		@if ( $user_home_address_text === '' )
			<a href="/profile/home-address">Indicate home region</a>
		@else
			{{ $user_home_address_text }}<a href="/profile/home-address" title="Edit home region"><em class="fa fa-edit"></em></a>
		@endif
		</p>
		<form id="profileForm" method="post" action="/profile">
			{!! csrf_field() !!}
			@include('pages.validation_messages', array('errors'=>$errors))

			<h2>My Accessibility Requirements </h2>
			<div class="box accessibility-interests">
				<div class="checkbox">
					<label>
					@if ( $user->uses_screen_reader )
					<input type="checkbox" name="uses_screen_reader" checked>
					@else
					<input type="checkbox" name="uses_screen_reader">
					@endif
					Screen Reader</label>
				</div>
				<div id="accordion">

				@foreach ($question_categories as $category)

					<h3>{{ $category->name }}</h3>
					<div class="category">
						<div class="checkbox">
							<button type="button" class="btn btn-lg btn-primary select-all">Select All</button>
						</div>
						<div class="questions">
							@foreach ($category->getSortedQuestions() as $question)
								<div class="checkbox">
									<label>
										@if ($user->isQuestionRequired($required_questions, $question->id))
										<input name="question_{{ $question->id }}" type="checkbox" checked>
										@else
										<input name="question_{{ $question->id }}" type="checkbox">
										@endif
									</label>
									<div>
									{!! $question->question_html !!}
									@if ($question->explanation)
											@include('pages.components.question_explanation_link',
											array(
												'question_id' => $question->id
												)
											)
									@endif
									</div>
								</div>
							@endforeach
						</div>
					</div>

				@endforeach

				</div>

			</div>

			<h2>My Reviews</h2>
			<div class="box rewards">
				<div>
					<a class="btn btn-default" href="/reviewed-locations">My Reviews({{ $num_reviews }})</a>
					<a class="btn btn-default" href="/location/management/my-locations">My Locations({{ $num_locations_added_by_me }})</a>
					<a class="btn btn-default" href="/suggestion-list">Suggestions({{ $num_suggestions_to_review }})</a>
				</div>
			</div>

			<div class="text-right">
				<button type="submit" id="submitButton" class="btn btn-lg btn-primary save-button" disabled>Save Profile</button>
		   </div>
	 </form>

	</div>
</div>

@stop
