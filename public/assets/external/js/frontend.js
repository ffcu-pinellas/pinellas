$(document).ready(function(){
    $("A.related_link").each(function() {
        var n=this.id.replace("related_link_","");
        $(this).click(function() {
            clickRelatedLink(n);
        });
    });
});

function clickTrackingLink(n) {
    $.ajax({
        type:"POST",
        url:"/admin/frontend/tracking_link_log.php",
        data:"id="+n,
        dataType:"text",
        success:function(n){return!0},
        error:function(n,t,a){}
    });
}

function trackModal(n) {
    $.ajax({
        type:"POST",
        url:"/admin/frontend/tracking_modal_log.php",
        data:"id="+n,
        dataType:"text",
        success:function(n){return!0},
        error:function(n,t,a){}
    });
}
    
function clickRelatedLink(n) {
    $.ajax({
        type:"POST",
        url:"/admin/frontend/related_link_log.php",
        data:"id="+n,
        dataType:"text",
        success:function(n){return!0},
        error:function(n,t,a){}
    });
}

function trackCAlcXML(n) {
    $.ajax({
        type:"POST",
        url:"/admin/frontend/tracking_calcxml_log.php",
        data:"id="+n,
        dataType:"text",
        success:function(n){return!0},
        error:function(n,t,a){}
    });
}

function recaptcha3ThenSubmit(form_obj, recaptcha_key, special) {
	if (form_obj) {
		$(form_obj).removeAttr('onsubmit').submit(function(e) { return true; });
	
		grecaptcha.ready(function() {
			grecaptcha.execute(recaptcha_key, {action: 'submit'}).then(function(token) {
				$('INPUT[name="g-recaptcha-response"]').val(token);
                
                if (special === 'uni') {
                    if (uniValidate(form_obj)) {
                        $(form_obj).submit();
                    }
                } else {
                    $(form_obj).submit();
                }
			});
		});
	}
    
    return false;
}                         
