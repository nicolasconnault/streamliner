$(function() {
    $('.info-icon').on('click', function() {
        $(($(this).children()[0])).simplemodal({overlayClose: true, autoResize: true});
    });
});
