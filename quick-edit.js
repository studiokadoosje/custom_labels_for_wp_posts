jQuery(document).ready(function($) {
    // Hook into Quick Edit click
    $(document).on('click', '.editinline', function() {
        let post_id = $(this).closest('tr').attr('id').replace('post-', '');
        let label = $('#post-' + post_id + ' .column-custom_label span').data('label') || '';
        
        // Wait for Quick Edit form to be ready
        setTimeout(function() {
            $('.inline-edit-custom-label').val(label);
        }, 100);
    });
});
