jQuery(document).ready(function($) {
    // Ajax request
    $.ajax({
        url: custom_script_vars.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'custom_ajax_projects'
        },
        success: function(response) {
            console.log(response);
        },
        error: function(xhr, status, error) {
            console.log(error);
        }
    });
});
