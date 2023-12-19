$=jQuery;
$(document).ready(function(){
	
	$('ul.partenaires-list a.broken_link').removeClass('broken_link');
	
	//OPEN CLOSE LIST
	$('ul.partenaires-list.openclose-list li').click(function(){
		//console.log(this);
		var subelem=$(this).find('ul.partenaires-list');
		if(subelem.is(':visible')){
			subelem.hide();
			$(this).removeClass('collapsed');
		}else{
			subelem.show();
			$(this).addClass('collapsed');
		}
	});
});