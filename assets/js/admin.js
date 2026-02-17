/**
 * LW Slider Admin JS
 *
 * Handles slide management: sortable, media picker, CRUD, conditional fields.
 */
(function ($) {
	'use strict';

	var slideIndex = 0;

	/**
	 * Initialize on document ready.
	 */
	$(function () {
		initSortable();
		initAddSlide();
		initSlideActions();
		initMediaPickers();
		initConditionalFields();
		initOpacitySliders();
		updateSlideIndexes();
	});

	/**
	 * Initialize jQuery UI Sortable.
	 */
	function initSortable() {
		$('#lw-slider-slides-list').sortable({
			handle: '.lw-slide-drag',
			placeholder: 'ui-sortable-placeholder',
			tolerance: 'pointer',
			update: function () {
				updateSlideIndexes();
			}
		});
	}

	/**
	 * Add new slide button handler.
	 */
	function initAddSlide() {
		$('#lw-slider-add-slide').on('click', function () {
			var template = $('#tmpl-lw-slider-slide').html();
			var newIndex = $('#lw-slider-slides-list .lw-slide-card').length;

			template = template.replace(/\[999\]/g, '[' + newIndex + ']');
			template = template.replace(/slide-999-/g, 'slide-' + newIndex + '-');
			template = template.replace(/data-index="999"/g, 'data-index="' + newIndex + '"');

			var $card = $(template);
			$card.find('.lw-slide-title').text(lwSliderAdmin.newSlide + ' ' + (newIndex + 1));
			$card.find('.lw-slide-body').show();

			$('#lw-slider-slides-list').append($card);
			initMediaPickers();
			initConditionalFields();
			initOpacitySliders();
		});
	}

	/**
	 * Slide expand/collapse and remove handlers.
	 */
	function initSlideActions() {
		$(document).on('click', '.lw-slide-toggle', function () {
			$(this).closest('.lw-slide-card').find('.lw-slide-body').slideToggle(200);
		});

		$(document).on('click', '.lw-slide-remove', function () {
			$(this).closest('.lw-slide-card').fadeOut(200, function () {
				$(this).remove();
				updateSlideIndexes();
			});
		});

		$(document).on('change', '.lw-slide-active-toggle input[type="checkbox"]', function () {
			$(this).closest('.lw-slide-card').toggleClass('lw-slide-inactive', !this.checked);
		});

		$(document).on('click', '.lw-slide-duplicate', function () {
			var $original = $(this).closest('.lw-slide-card');
			var $clone = $original.clone();

			$clone.find('.lw-slide-body').hide();
			$clone.insertAfter($original);
			updateSlideIndexes();
		});
	}

	/**
	 * Initialize media pickers for background images.
	 */
	function initMediaPickers() {
		$(document).off('click.lwSliderMedia').on('click.lwSliderMedia', '.lw-bg-image-select', function (e) {
			e.preventDefault();
			var $card = $(this).closest('.lw-slide-card');
			var $input = $card.find('.lw-bg-image-id');
			var $preview = $card.find('.lw-bg-image-preview');
			var $removeBtn = $card.find('.lw-bg-image-remove');

			var frame = wp.media({
				title: lwSliderAdmin.selectImage,
				button: { text: lwSliderAdmin.useImage },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var thumbUrl = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;

				$input.val(attachment.id);
				$preview.html('<img src="' + thumbUrl + '" alt="" style="max-width:200px;height:auto;">');
				$removeBtn.show();

				var $thumb = $card.find('.lw-slide-thumb');
				var smallUrl = attachment.sizes && attachment.sizes.thumbnail
					? attachment.sizes.thumbnail.url
					: thumbUrl;

				if ($thumb.length) {
					$thumb.attr('src', smallUrl);
				} else {
					$card.find('.lw-slide-drag').after(
						'<img src="' + smallUrl + '" alt="" class="lw-slide-thumb">'
					);
				}
			});

			frame.open();
		});

		$(document).off('click.lwSliderRemoveMedia').on('click.lwSliderRemoveMedia', '.lw-bg-image-remove', function (e) {
			e.preventDefault();
			var $card = $(this).closest('.lw-slide-card');
			$card.find('.lw-bg-image-id').val(0);
			$card.find('.lw-bg-image-preview').empty();
			$card.find('.lw-slide-thumb').remove();
			$(this).hide();
		});
	}

	/**
	 * Initialize conditional field visibility.
	 */
	function initConditionalFields() {
		// Background type toggle.
		$(document).off('change.lwBgType').on('change.lwBgType', '.lw-bg-type-radio', function () {
			var $card = $(this).closest('.lw-slide-card');
			var isImage = $(this).val() === 'image';
			$card.find('.lw-bg-image-fields').toggle(isImage);
			$card.find('.lw-bg-color-fields').toggle(!isImage);
		});

		// CTA mode toggle.
		$(document).off('change.lwCtaMode').on('change.lwCtaMode', '.lw-cta-mode-radio', function () {
			var $card = $(this).closest('.lw-slide-card');
			var isButton = $(this).val() === 'button';
			$card.find('.lw-cta-button-fields').toggle(isButton);
		});

		// Autoplay delay toggle.
		$('input[name="lw_slider_settings[autoplay]"]').on('change', function () {
			$('.lw-autoplay-delay-field').toggle(this.checked);
		});
	}

	/**
	 * Initialize opacity range sliders.
	 */
	function initOpacitySliders() {
		$(document).off('input.lwOpacity').on('input.lwOpacity', '.lw-opacity-range', function () {
			$(this).closest('p').find('.lw-opacity-value').text(this.value);
		});
	}

	/**
	 * Update all slide input name indexes after reorder/remove.
	 */
	function updateSlideIndexes() {
		$('#lw-slider-slides-list .lw-slide-card').each(function (i) {
			var $card = $(this);
			$card.attr('data-index', i);

			$card.find('[name]').each(function () {
				var name = $(this).attr('name');
				if (name) {
					$(this).attr('name', name.replace(/lw_slider_slides\[\d+\]/, 'lw_slider_slides[' + i + ']'));
				}
			});

			$card.find('[id]').each(function () {
				var id = $(this).attr('id');
				if (id) {
					$(this).attr('id', id.replace(/lw-slide-\d+-/, 'lw-slide-' + i + '-'));
				}
			});

			$card.find('label[for]').each(function () {
				var forAttr = $(this).attr('for');
				if (forAttr) {
					$(this).attr('for', forAttr.replace(/lw-slide-\d+-/, 'lw-slide-' + i + '-'));
				}
			});
		});
	}

})(jQuery);
