/*
	 * Responsive Tables
	 */
	$('.table-responsive.block').each(function()
	{
		var h = $(this).find('thead');
		var b = $(this).find('tbody');
		b.find('tr').each(function()
		{
			var tr = $(this);
			$(this).find('td').each(function(){
				var i = tr.find('td').index($(this));
				//$(this).attr('data-title', h.find('th').get(i).html('test'));
			});
		});
	});
