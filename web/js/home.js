(function($) {
	$(function() {
		/*
		 Carousel initialization
		 */
		$('div.jcarousel')
			.jcarousel({
				// Options go here
				index: 1
			});




		$('div.jcarousel').on('jcarousel:animateend', function(event, carousel) {

			carousel._target.css({height:'auto'});
			var height =carousel._target.height();

			carousel._target.siblings().each(function(item){
				$(this).animate({height:height+'px'},'fast');
			});

		});



		$('div.jcarousel-pagination').jcarouselPagination({
			item: function(page,items) {
				return '<a href="#' + page + '">' + items.attr('title') + '</a>';
			}
		});

		/*
		 Pagination initialization
		 */
		$('div.jcarousel-pagination')
			.on('jcarouselpagination:active', 'a', function() {
				$(this).addClass('active');
			})
			.on('jcarouselpagination:inactive', 'a', function() {
				$(this).removeClass('active');
			})
			.jcarouselPagination({
				// Options go here
			});

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

		$('div.jcarousel').jcarousel('scroll', 1,false);


	});
})(jQuery);