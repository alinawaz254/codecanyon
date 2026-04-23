<?php
	if(partial_access("all")):
		echo '</div> <!-- container fluid ends /-->';
		echo '</div><!-- Content Inner /-->';
		require_once("lib/includes/footer_bar.php");						
		echo '</div>
			<!-- End Page Content -->';
		echo '</div><!-- page end /-->';
		echo '<a href="#" class="go-top"><i class="la la-arrow-up"></i></a>
		<!-- Offcanvas Sidebar -->';
	endif;
?>
	<!-- Begin Vendor Js -->
	<script src="assets/vendors/js/base/core.min.js"></script>
	<script src="assets/vendors/js/nicescroll/nicescroll.min.js"></script>
	<script src="assets/vendors/js/app/app.min.js"></script>
	<script src="assets/js/components/tabs/animated-tabs.min.js"></script>

	<?php if(isset($datatables) && $datatables == "1"): ?>
	<script src="assets/vendors/js/datatables/datatables.min.js"></script>
	<script src="assets/vendors/js/datatables/dataTables.buttons.min.js"></script>
	<script src="assets/vendors/js/datatables/jszip.min.js"></script>
	<script src="assets/vendors/js/datatables/buttons.html5.min.js"></script>
	<script src="assets/vendors/js/datatables/pdfmake.min.js"></script>
	<script src="assets/vendors/js/datatables/vfs_fonts.js"></script>
	<script src="assets/vendors/js/datatables/buttons.print.min.js"></script>
	<!-- Begin Page Snippets -->
	<script src="assets/js/components/tables/tables.js"></script>
	<!-- End Page Snippets -->
	<?php endif; ?>

	<?php if(isset($datepicker) && $datepicker == "1"): ?>
	<script src="assets/vendors/js/datepicker/moment.min.js"></script>
	<script src="assets/vendors/js/datepicker/daterangepicker.js"></script>
	<script src="assets/js/components/datepicker/datepicker.js"></script>
	<script>
	$(function() {
		$('input[name="date_of_birth"]').each(function() {
			var $el = $(this);
			var initialValue = $el.val();
			$el.daterangepicker({
				singleDatePicker: true,
				showDropdowns: true,
				autoUpdateInput: false,
				minYear: 1901,
				maxYear: parseInt(moment().format('YYYY'),10),
				locale: {
					format: 'MM/DD/YYYY'
				}
			});
			if(initialValue) {
				$el.val(initialValue);
			}
		});

		$('input[name="date_of_birth"]').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY'));
		});

		$('input[name="date_of_birth"]').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});
	});
	</script>
	<?php endif; ?>
	<?php if(isset($croppic) && $croppic == 1) : ?>
	<script src="assets/js/croppic.min.js"></script>
    <style>
        /* Modern styling for Croppic controls to make them look like buttons */
        .cropControls {
            background-color: rgba(0,0,0,0.85) !important;
            padding: 10px !important;
            border-radius: 5px !important;
            display: flex !important;
            gap: 10px !important;
            align-items: center !important;
        }
        .cropControls i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-style: normal !important;
            font-size: 14px !important;
            color: #fff !important;
            cursor: pointer !important;
            padding: 5px 12px !important;
            border-radius: 4px !important;
            background: #444 !important;
            min-width: 60px !important;
            transition: all 0.2s !important;
        }
        .cropControls i:hover { background: #666 !important; }
        .cropControls i.cropControlZoomIn::after { content: " + Zoom"; margin-left: 5px; }
        .cropControls i.cropControlZoomOut::after { content: " - Zoom"; margin-left: 5px; }
        .cropControls i.cropControlCrop { background: #28a745 !important; }
        .cropControls i.cropControlCrop::after { content: " SET & CROP"; margin-left: 5px; font-weight: bold; }
        .cropControls i.cropControlReset { background: #dc3545 !important; }
        .cropControls i.cropControlReset::after { content: " CANCEL"; margin-left: 5px; }
    </style>
	<script type="text/javascript">
		var croppicContaineroutputOptions = {
				uploadUrl:'lib/includes/img_save_to_file.php',
				cropUrl:'lib/includes/img_crop_to_file.php', 
				outputUrlId:'cropOutput',
				modal:true,
				doubleZoomControls:false,
				rotateControls:false,
				loaderHtml:'<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div>',
                onAfterImgCrop: function() {
                    alert("Profile image cropped successfully! Now click 'Update User' to save changes.");
                },
                onError: function(errormsg) {
                    alert("Error: " + errormsg);
                }
		}
		var cropContaineroutput = new Croppic('cropContaineroutput', croppicContaineroutputOptions);
	</script>
	<?php endif; ?>
	<!-- End Page Snippets -->

	<script type="text/javascript" src="assets/js/tinymce/tinymce.min.js"></script>

	<script type="text/javascript">
		tinymce.init({
			selector: "textarea.tinyst, .advance_editor textarea",
			branding: false,
			fixed: true, // Crucial for proper positioning
			zindex: 11000, // Base z-index for dialogs
			body_class: 'tox-dialog',
			menubar : false,
			plugins: "codesample, link, lists, image, media",
			toolbar: "styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | codesample | link image media|",
			placeholder : String,
			setup: function (editor) {
				editor.on('change', function () {
					tinymce.triggerSave();
				});
			}
		 });

		//Confim delet on deleting objects.
		function confirm_delete() { 
			var del = confirm('<?php _e("Confirm delete this cannot be undone."); ?>');
			if(del == true) { 
				return true;
			} else { 
				return false;
			}
		}//delete_confirmation ends here.
	</script>

	<script type="text/javascript">
		jQuery(function($) {
			$('form[data-async]').on('submit', function(event) {
				var $form 	= $(this);
				var $target = $($form.attr('data-target'));

				var formdata = new FormData(this);

				$.ajax({
					type: $form.attr('method'),
					url: 'lib/includes/formprocessing.php',
					data: formdata,
					mimeTypes:"multipart/form-data",
					contentType: false,
					cache: false,
					processData: false,
					dataType: 'json',

					success: function(response) {
						//console.log(response);
						var message 		= response.message;

						$('.form-message').html('<div class="alert alert-warning alert-dismissible fade show" role="alert">'+message+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
						//Reset Form
						if(response.submission == "INSERT") {
							$form.trigger("reset");	
						}
						$("#wc_data").load(window.location + " #wc_data");
					}
				});
			event.preventDefault();
			});

			$('a.addadditionaluserfield').on('click', function(event) {
				event.preventDefault();
				$.ajax({
					type: 'POST',
					url: 'lib/includes/formprocessing.php',
					data: {
						'form_type': 'return_additional_field',
						'include':'delete_option',
					},
					dataType: 'json',
					beforeSend: function() {
						console.log('Sending');
					},
					success: function(response) {
						$('.wcrb_additional_fields_wrap').append(response);
					}
				});
			});

			$(document).on('click', '.delmeextrafield', function(e) {
				e.preventDefault();
				console.log('Delete functional');
				$(this).closest('div.wcrb_repater_field').remove();
			});
		});//Endof jQuery
	</script>
<?php if((isset($_GET["update_data"]) && $_GET["update_data"] == "1")): ?>
<script type="text/javascript">
    $(window).on('load',function(){
        $('#target_edit_add_form').modal('show');
		// prevent Bootstrap from hijacking TinyMCE modal focus   
		$(document).on('focusin', function(e) {
			if ($(e.target).closest(".tox").length) {
				e.stopImmediatePropagation();
			}
		});
    });
</script>
<?php endif; ?>
<script type="text/javascript">
	var selectStateInputEl = document.querySelector('#multiple-select-plans');
	if(selectStateInputEl) {
	    const choices = new Choices(selectStateInputEl,{
            removeItemButton: true,
	    }); 
	}
	$(document).ready(function() {
		// $('#investment-users').select2();
		$('#investment-users').select2({
			width: '100%',
			placeholder: "Search & Select User",
			allowClear: true
		});
		
		$('#referral-users').select2({
			width:'100%',
			placeholder:"Search & Select Referral User",
			allowClear:true,
		    // templateResult: function (data) {
		    //     if (!data.id) return data.text;

		    //     let option = $(data.element);

		    //     return option.data('user-name') || data.text;
		    // },
		    // templateSelection: function (data) {
		    //     if (!data.id) return data.text;

		    //     let option = $(data.element);

		    //     return option.data('user-full-name') || data.text;
		    // }
		});

		$(".toggle-password").click(function() {
		    $(this).toggleClass("fa-eye fa-eye-slash");
		    input = $(this).parent().find("input");
		    if (input.attr("type") == "password") {
		        input.attr("type", "text");
		    } else {
		        input.attr("type", "password");
		    }
		});
	

		// Close other dropdowns when one is opened in side panel
		$('.side-navbar .collapse').on('show.bs.collapse', function () {
			$('.side-navbar .collapse').not(this).collapse('hide');
		});

		// $('#congratsModal').modal('show');
	});
	
	//notification
	setInterval(function(){

	fetch("get_notifications.php")
	.then(response => response.text())
	.then(count => {

	document.getElementById("notification-count").innerText = count;
	document.getElementById("notification-count-title").innerText = count;

	let pulse = document.getElementById("notification-pulse");

	if(count > 0){
	pulse.classList.remove("hidden");
	}else{
	pulse.classList.add("hidden");
	}

	});

	}, 5000);

	
</script>
</body>
</html>