jQuery(document).ready(function($){
    // When the user scrolls down 50px from the top of the document, fixed the header to the top
    window.onscroll = function() {
        rightWidth = jQuery('.single-lesson-plans #main .container .lp-featured-section').outerWidth()
        if (document.body.scrollTop > 52 || document.documentElement.scrollTop > 52) {
            jQuery('.tc-lp-details-header').css({
                "background":"#ffffff",
                "padding":"10px 50px",
                "border":"1px solid #4f4f4f",
                "box-shadow": "1px 1px 2px 0px rgba(0,0,0,0.8)",
                "z-index":"1",
                "width": rightWidth + "px"
            });
        } else {
            jQuery('.tc-lp-details-header').css({
                "background":"none",
                "padding":"25px 50px 0",
                "border":"none",
                "box-shadow":"none",
                "z-index":"0",
                "width":"100%"
            });
        }
    };
    
    $('.lp-nav-right-ajax').on("click", function(){
        var id = $(this).attr('data-id');
        var cnt = $(this).attr('data-count');
        var next = $(this);
        $.post(lp_ajax_object.ajaxurl,
            {
            action:'lp_get_source_callback',
            curriculum_id: next.attr('data-curriculum'),
            next_source: next.attr('data-nextsource'),
            index: next.attr('data-id')
            }).done(function (response) {
                response = JSON.parse(response);
                
                /** Navigation Arrow Updates **/
                $('.ps-header').css({'background':'url(' + response.featured_image + ') no-repeat top left'})
                $('.ps-header').attr('data-curid', id);
                $('.ps-expand > a').attr('href', response.featured_image);
                nextId = parseInt(id)+1;
                if (nextId>=cnt) {
                    next.attr('data-id',nextId);
                    next.parent().addClass('ps-nav-hidden');
                } else {
                    next.attr('data-id',nextId);
                }
                prevId = id-1;
                if (prevId>=0) {
                    $('.lp-nav-left').parent().removeClass('ps-nav-hidden');
                    $('.lp-nav-left').attr('data-id',prevId);
                }
                /** Update Resource Details **/
                $('.ps-info-title').text(response.resource.post_title);
                $('.ps-info-description').html(response.resource.post_content);
                $('.ps-resource-url .ps-value a').attr('href',response.resource_meta.oer_resource_url);
                $('.ps-resource-url .ps-value a').text(response.resource_meta.oer_resource_url);
                
                /** Update Student Info Tab Content **/
                $('#ps-student-info-tab-content').html("");
                $('#ps-student-info-tab-content').html(response.student_info);
                
                /** Update Teacher Info Tab Content **/
                $('#ps-teacher-info-tab-content').html("");
                $('#ps-teacher-info-tab-content').html(response.teacher_info);
            }
        );
    });
    
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.lp-nav-right,.lp-nav-left').attr('data-activetab', e.target.id);
        var activeContent = $(e.target).attr('href');
        if ($(activeContent).find('.tc-tab-content').height()<340){
            $(activeContent).find('.tc-read-more').addClass('tc-btn-hide');
        }
    });
    
    $(document).on("show.bs.collapse", '#tcHiddenFields.collapse', function (){
        $('#see-more-link').text("SEE LESS -");
    });
    
    $(document).on("hide.bs.collapse", '#tcHiddenFields.collapse', function (){
        $('#see-more-link').text("SEE MORE +");
    });
    
    $(document).on("show.bs.collapse", '.lp-subject-hidden.collapse', function (){
        var more_count = $('.see-more-subjects').attr('data-count');
        $('.see-more-subjects').text("SEE " + more_count + " LESS -");
    });
    
    $(document).on("hide.bs.collapse", '.lp-subject-hidden.collapse', function (){
        var more_count = $('.see-more-subjects').attr('data-count');
        $('.see-more-subjects').text("SEE " + more_count + " MORE +");
    });
    
    $(document).on("show.bs.collapse", '.tc-lp-details-standard.collapse', function (){
        $(this).parent().find('.lp-standard-toggle i').removeClass('fa-caret-right').addClass('fa-caret-down');
    });
    
    $(document).on("hide.bs.collapse", '.tc-lp-details-standard.collapse', function (){
        $(this).parent().find('.lp-standard-toggle i').removeClass('fa-caret-down').addClass('fa-caret-right');
    });
    
    $(document).on("click", '.lp-read-more', function (){
        $('.lp-excerpt').hide();
        $('.lp-full-content').show();
    });
    
     $(document).on("click", '.lp-read-less', function (){
        $('.lp-excerpt').show();
        $('.lp-full-content').hide();
    });
    
    
    $('.lp-nav-right,.lp-nav-left').on("click", function(e){
        e.preventDefault();
        var nav = $(this);
        var url = nav.attr('href');
        var tab = nav.attr('data-activetab');
        lp_redirect_with_post(url, tab);
    });
    
    $('.question-popup-button').on("click", function(e){
        window.scroll({
            top: $('.ps-info-tabs-content').offset().top, 
            left: 0, 
            behavior: 'smooth'
        });
        tab_content = $(this).attr('href');
        $('.ps-info-tabs a[href="' + tab_content + '"]').tab("show");
        $('.ps-info-tabs a[href="' + tab_content + '"]').removeClass*('show');
    });
    
    if ($('.lp-nav-right,.lp-nav-left').is(":visible")) {
         $('.lp-nav-right,.lp-nav-left').attr('data-activetab', $('.ps-info-tabs li a.nav-link.active').attr('id'));
    }
    
    if (typeof wp.data !== "undefined") {
		wp.data.subscribe(function(){
			var isSav = wp.data.select('core/editor').isSavingPost();
			var isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
			
			if (isSavingPost && !isAutosavingPost) {
				window.tinyMCE.triggerSave();
			}
		});
	}
});

function lp_redirect_with_post(url, tab) {
    var form = document.createElement('form');
    form.action = url;
    form.method = 'POST';
    
    var hInput = document.createElement('input');
    hInput.type = 'hidden';
    hInput.name = 'activeTab';
    hInput.value = tab;
    form.appendChild(hInput);
    
    document.body.appendChild(form);
    form.submit();
}