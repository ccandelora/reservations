jQuery(document).ready(function($) {
	$('#reservation_tooltips').appendTo('body');
	$('td.reserved').hover(function() {
		$this = $(this);
		$tooltip = $('#reservation'+$(this).data('reservation-id'));
		$tooltip.show();
		$this.mousemove(function(e){
			   	$tooltip.css({
					top: (e.pageY + 15) + "px",
					left: (e.pageX + 15) + "px"
				});
			});
	},function() {
		$('#reservation'+$(this).data('reservation-id')).hide();
	});
	
	function getHumanReadableHour(hour) {
		if(hour%100) {
			hour = Math.floor(hour/100);
			return hour + ':30';
		} else {
			hour = hour/100;
			return hour + ':00';
		}
	}
	
	function getHumanReadableYear(year) {
		year = year.split('-');
		return year[2]+'.'+year[1]+'.'
	}
	
	$('td.can_reserve').click(function() {
		$('#cancel_form').hide();
		
		$this = $(this);
		$form = $('#reservation_form');
		$form.show();
		
		$form.find('input[name=from]').val($this.data('reserve-hour'));
		$form.find('input[name=date]').val($this.data('reserve-date'));
		$form.find('select[name=play_courts] option:selected').removeAttr("selected");
		$form.find('select[name=play_courts] option[value='+$this.data('reserve-court')+']').attr('selected', 'selected');
		
		$('#text_reservation_from').text(getHumanReadableYear($this.data('reserve-date'))+' od: '+getHumanReadableHour($this.data('reserve-hour')));
	});
	
	$('td.can_cancel').click(function() {
		$('#reservation_form').hide();

		$this = $(this);
		$form = $('#cancel_form');
		$form.show();
		$form.find('input[name=reservation_id]').val($this.data('reservation-id'));
		$('#cancel_form_info').html($('#reservation'+$this.data('reservation-id')).find('p').html());
	});
	
	$('#cancel_reservation').click(function() {
		$('#reservation_form').fadeOut();
	});
	
	$('#cancel_cancellation').click(function() {
		$('#cancel_form').fadeOut();
	});

	$('#reservation_notice').delay(3000).fadeOut('slow');
});