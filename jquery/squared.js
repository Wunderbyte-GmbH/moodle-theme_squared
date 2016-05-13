$( document ).ready(function(){
    $('body').on('click', '.searchtoggle', function(e){
        e.preventDefault();
        $('#header').addClass('search-toggled');
    });
    
    $('body').on('click', '#top-search-close', function(e){
        e.preventDefault();
        
        $('#header').removeClass('search-toggled');
    });
    $('.search input').hide();
    $('#search-trigger').click(function(){
        $('.search input').slideToggle('fast').focus();
        $(this).toggleClass('active');
        $('.search-wrapper').toggleClass('inactive');
    });
});