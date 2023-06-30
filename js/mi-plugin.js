jQuery(document).ready(function($) {
    // Función para agregar un nuevo registro
    $('#mi-plugin-add-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=mi_plugin_add_record',
            success: function(response) {
                alert('Registro agregado con éxito');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('Error al agregar el registro');
            }
        });
    });
    
    // Función para eliminar un registro
    $('.mi-plugin-delete-btn').on('click', function(e) {
        e.preventDefault();
        
        var recordId = $(this).data('record-id');
        
        if (confirm('¿Estás seguro de eliminar este registro?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mi_plugin_delete_record',
                    record_id: recordId
                },
                success: function(response) {
                    alert('Registro eliminado con éxito');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('Error al eliminar el registro');
                }
            });
        }
    });
    
    // Función para mostrar el formulario de edición
    $('.mi-plugin-edit-btn').on('click', function(e) {
        e.preventDefault();
        
        var recordId = $(this).data('record-id');
        
        $('.mi-plugin-edit-row[data-record-id="' + recordId + '"]').toggle();
    });
    
    // Función para editar un registro
    $('.mi-plugin-edit-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=mi_plugin_edit_record',
            success: function(response) {
                alert('Registro editado con éxito');
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('Error al editar el registro');
            }
        });
    });
});
