jQuery(document).ready(function() {

	jQuery('input.autocomplete').each(function() {
		var input = jQuery(this);
		var service = input.data('url');
		var name = input.data('name');

		input.devbridgeAutocomplete({
			serviceUrl: function (element, query) {
				return service + '?query=' + query + '&parameter=' + name;
			},
			ajaxSettings: {
				dataType : 'json'
			},
			onSelect: function(suggestion) {
				// console.log('You selected: ' + suggestion.value + ', ' + suggestion.data);
				console.log(suggestion);
				$('input[name=' + name + ']').val(suggestion.data.id);
				// $('#selction-ajax').html('You selected: ' + suggestion.value + ', ' + suggestion.data);
			},
			transformResult: function(response) {
				return {
					suggestions: $.map(response.data, function(dataItem) {
						return { value: dataItem.value, data: dataItem };
					})
				};
			},
			onInvalidateSelection: function() {
				// $('#selction-ajax').html('You selected: none');
				$('input[name=' + name + ']').val('');
				// empty id field
			}
		});
	});

});