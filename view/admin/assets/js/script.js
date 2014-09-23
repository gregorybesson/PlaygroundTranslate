$(function() {
    if(jQuery('#arbo-select').size() > 0) {
        
        $('#arbo-select').on('change',function()
        {
            if($(this[this.selectedIndex]).attr('data-controller') == "") {
                window.location.href = window.location.origin + window.location.pathname 
                    + '?locale=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-lang'));
            } else {
                window.location.href = window.location.origin + window.location.pathname 
                    + '?controller=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-controller')) 
                    + '&action=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-action'))
                    + '&locale=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-lang')) ;
            }
        });
        $('#lang-select').on('change',function()
        {
            window.location.href = window.location.origin + window.location.pathname 
                + '?controller=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-controller')) 
                + '&action=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-action'))
                + '&locale=' + encodeURIComponent($(this[this.selectedIndex]).attr('data-lang')) ;
        });
        $('#translate-keys textarea').on('keyup', function() {
            var key = $(this).parent().parent().find('td').first().html().trim();
            var value = $(this).val();
            var oldValue = $(this).parent().find('input[type=hidden]').val();
            var oldValueInput = $(this).parent().find('input[type=hidden]');

            $(document.getElementById('translate-iframe').contentWindow.document.getElementsByTagName('*')).each(function(i, e){ 
                $(this).contents().each(function(i, e){
                    if($(this)[0].nodeType == 8) {
                        var nodeValue = e.nodeValue;
                        console.log('"' + e.nodeValue + '"', '" traduction-key:' + key + ' "', nodeValue == ' traduction-key:' + key + ' ');
                        if(nodeValue == ' traduction-key:' + key + ' ') {
                            oldValueInput.val(value);
                            $(this).parent().html($(this).parent().html().replace('<!-- traduction-key:' + key + ' --> ' + oldValue + ' <!-- /traduction-key -->', '<!-- traduction-key:' + key + ' --> ' + value + ' <!-- /traduction-key -->'));
                            // <!-- traduction-key:Unser Erbe --> Unser Erbe <!-- /traduction-key -->
                        }
                    }

                }) 
            });
        })

        $(window).on('scroll', function() {
            var top = $(window).scrollTop();
            var container = $('#container-iframe');
            console.log(container.offset().top, top);
            if(container.offset().top < top) {
                container.children()
                    .css('width', container.width())
                    .css('position', 'absolute')
                    .css('top', (top - container.offset().top) + 'px');
            } else {
                container.children()
                    .css('width', 'auto')
                    .css('position', 'relative')
                    .css('top', '');
            }
        });



    }
});