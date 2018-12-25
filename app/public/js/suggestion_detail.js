document.addEventListener("DOMContentLoaded", function(event) {
	var fields = ['name', 'phone_number', 'address', 'external_web_url'];

	function isSuggestionDoingNothing(field) {
		var original_val = $('#original_' + field).val();
		var suggested_val = $('#suggestion_' + field).val();
		return original_val === suggested_val;
	}

	function hide(field) {
		field = 'accept_' + field;
		var $parent = $('#' + field).closest('div');
		$parent.addClass('does-nothing');
		var $form_rows = $parent.closest('form .row');

		// If any row contains more than 
		if ($form_rows.find('.does-nothing').length > 1) {
			$form_rows.addClass('does-nothing');
		}
	}

	function hideSuggestionsThatDoNothing() {
		fields.filter(isSuggestionDoingNothing).forEach(hide);
	}

	function bindField(tag) {
		var $tag = $("#accept_"+ tag);
		var $div = $tag.closest('div');
		$tag.click(function() {
			sendRequest(getData(tag), tag);
		});
		$div.find('.resolve').click(function() {
			var $element = $div.find('input[type="button"].btn-primary');
			var field_name = $element.attr('id');
			markResolved(tag);
		});
	}

	function sendRequest(data, tag) {
		var method = 'POST';
		if (tag === 'resolve_all') {
			method = 'DELETE';
		}
		if (tag === 'accept_all') {
			url = '/api/suggestion/merge';
		}
		$.ajax({
			url: url,
			method: method,
			data: data,
			success: function() {
				if (tag === 'accept_all' || tag === 'resolve_all') {
					// navigate to suggestion list.
					location.href = '/suggestion-list';
				}
				else {
					location.reload();
				}
			},
			error:function(result) {
				$("#" + tag).parent().prepend("<div class='alert alert-danger'>"
												+ "Something went wrong."
												+ "</div>");
			}
		});
	}

	function markResolved(fieldname) {
		$('#suggestion_' + fieldname).val($('#original_' + fieldname).val());
		hide(fieldname);
	}

	function getData(fieldname) {
		var data = {};
		data._token = $('[name="_token"]').val();
		data.location_id = $("#location_id").val();
		var fields = ['name', 'external_web_url', 'address', 'phone_number'];
		fields.forEach(function(field) {
			data[field] = $('#original_' + field).val();
		});

		function acceptSuggestion(field) {
			data[field] = $('#suggestion_' + field).val();
		}

		function acceptAll() {
			fields.forEach(acceptSuggestion);
		}

		if ( fieldname === 'accept_all' ) {
			acceptAll();
		}
		else {
			acceptSuggestion(fieldname);
		}

		return data;
	}

	fields.forEach(bindField);
	hideSuggestionsThatDoNothing();
});
