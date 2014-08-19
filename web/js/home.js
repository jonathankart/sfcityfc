(function($) {
	$(function() {
		/*
		 Carousel initialization
		 */

		$('.subscribeWidget form').submit(function(e){
			e.preventDefault();

			var $form = $(this);
			var formData={};

			$form.find('input').each(function() {
				$input = $(this);
				formData[$input.attr('name')] = $input.val();
			});


			$.ajax({
				type: "POST",
				url: '/subscribe',
				data: formData,
				success: function(result){
					if(result.success){
						$form.hide();
						$(".formMessage").text("You are subscribed!").removeClass('error');

					}else{
						$(".formMessage").text("The email you provided is invalid.").addClass('error');
					}
				},
				dataType: 'json'
			});
		});

		$('.eventMore').click(function (e){
			e.preventDefault();
			var $link = $(this);
			var index = $link.attr('data-event-index');
			$('.eventDescription.'+index).slideDown('fast');
			$link.remove();
		});

		$("#grievance-link").click(function(e){
			e.preventDefault();
			$("#grievance-explanation").slideDown();
		});

		$("#grievance-complete").click(function(e){
			e.preventDefault();
			$("#grievance-explanation").slideUp(400,function(){
				$("a[href='#join']").click();
			});
		});


	});
})(jQuery);