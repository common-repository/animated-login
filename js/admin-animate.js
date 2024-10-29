 
//jQuery animate admin test pages
  function testAnim(x) {
	jQuery("#td-animated-image").removeClass().addClass(x + " animated").one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
		  jQuery(this).removeClass();
		});
	};

	jQuery(document).ready(function(){
		jQuery("#loginimage").change(function(){
		  var newimage = jQuery(this).val();
		  jQuery("#animated-em-your-image").attr("src",newimage);
		  jQuery("#animated-em-dynamic-text").contents().replaceWith("Your Image &rarr;");

		});

		jQuery(".js--animations").change(function(){
		  var anim = jQuery(this).val();
		  var timedelay = jQuery("#animation-delay").val();
		  setTimeout(function() { testAnim(anim) },timedelay);
		});
	});