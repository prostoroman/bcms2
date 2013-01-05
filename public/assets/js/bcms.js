$().ready(function() {

    // Autohide alert messages
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 5000);

    var elf = $('#elfinder').elfinder({
            url : '/assets/elfinder/php/connector.php',  // connector URL (REQUIRED)
            lang: 'ru',             // language (OPTIONAL)
    }).elfinder('instance');
        
    // tooltip demo
    $("a[rel=tooltip]").tooltip();

    // popover demo
    $("a[rel=popover]").popover().click(function(e) {
      e.preventDefault()
    });
    
    $("#pages-list tr.pageRows").hover(function() {
       var id = $(this).attr('id');
       //$('td#actions-' + id).html('');
    });

    $('.selectAll').change(function() {
        var checkboxes = $(this).closest('form').find(':checkbox');
        if($(this).is(':checked')) {
            checkboxes.attr('checked', 'checked');
        } else {
            checkboxes.removeAttr('checked');
        }
    });

    function clearPanel(){
        // You can put some code in here to do fancy DOM transitions, such as fade-out or slide-in.
    }
    
    Path.map("#!/delete_many").to(function(){
        $('#mass-actions').attr('action', '/admin/pages/delete_many');
        window.location.href = "#";
        $('#mass-actions').submit();
    }).enter(clearPanel);

    Path.map("#!/move_up/:id").to(function(){
        
        var id = this.params['id'];
        
        $('#row-' + id).insertBefore($('#row-' + id).prev());
        
        //alert("Move Up!" + this.params['id']);
        window.location.href = "#";

    }).enter(clearPanel);

    Path.map("#!/move_down/:id").to(function(){
        
        var id = this.params['id'];
        var elem = $('#row-' + id);
        var level = elem.attr('data-level');
        
        alertMessage('test! ' + level, 'error');
        
        $('#row-' + id).insertAfter($('#row-' + id).next());
        
        //alert("Move Up!" + this.params['id']);
        //window.location.href = "#";

    }).enter(clearPanel);
     
    
    Path.listen();

});

var alertMessage = function (text, type)
{
    $('#message').html('<div class="alert alert-' + type + '"><button type="button" class="close" data-dismiss="alert">&times;</button>' + text + '</div>');
}
