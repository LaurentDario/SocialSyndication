$(document).ready(function(){



		/*jQuery.ajax({
			type: "post",
			dataType: "json",
			url: myAjax.ajaxurl,
			data : {  action: "load_more", category : category, rowNum : rowNum, mode : mode, exclusion_ids: exclusion_ids },
			success: function (response) {
				console.log(response);
				$('.blocks .row').last().after(response.htmlText);
				fadeinElements();
				$(".block").unbind();
				bindTilesEvent();

				var loadButton = $('.loadButton');
				loadButton.css('background', original_background);

				if(!response.hasMorePosts){
					loadButton.unbind('click');
					loadButton.css({display:'none'});
					loadButton.click(function(e){
						jQuery(this).addClass('disabled');
						e.preventDefault();
					})
				}
			},
			error: function (error){
				console.log(error);
			}
		});
*/

	//console.log('triggered');

});