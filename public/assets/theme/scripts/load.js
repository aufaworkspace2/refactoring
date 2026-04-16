$(function()
{
	// initialize tooltips
	$("[rel='tooltip']").tooltip();
	
	// initialize modals & hide
	$('.modal').modal('hide');
	
	// initialize wysihtml5
	if ($('textarea.wysihtml5').length > 0)
	{
		$('textarea.wysihtml5').wysihtml5();
	}
	
	$('.mainMenu ul.collapse').on('show', function(e){
		$(this).parent().addClass('open');
		$(this).parent().addClass('active');
	}).on('hidden', function(e){
		$(this).parent().removeClass('open');
		$(this).parent().removeClass('active');
	});
	
	$("#sticky_footer [data-toggle='menu-position']").click(function(e)
	{
		if ($(this).attr('href') != '#') 
			return true;
		else
			e.preventDefault();
		
		var p = $(this).attr('data-menu-position');
		$.cookie('menuPosition', p);
		location.reload();
	});
	
	/*
	 * Site Pages: Responsive markup
	 */
	$('.site-pages').each(function(){
		$(this).find('a').addClass('hidden-phone').clone().removeClass('hidden-phone').addClass('visible-phone').appendTo($(this));
		$(this).find('.visible-phone .txt').addClass('btn-block-txt').appendTo($(this).find('.visible-phone .btn'));
		$(this).find('.visible-phone .btn').addClass('btn-block');
	});
	
	
});
