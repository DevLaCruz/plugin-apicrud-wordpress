<?php
/**
 * Plugin Name: CRUD usando API de gorest.co
 * Description: Plugin para realizar operaciones CRUD utilizando la REST API.
 * Author: DavLaCruz
 * Version: 1.0
 */

// Función para crear la tabla al activar el plugin
function mi_plugin_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mis_datos';

    // Verifica si la tabla ya existe
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Crea la tabla
    
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            gender VARCHAR(10) NOT NULL,
            status VARCHAR(10) NOT NULL,
            PRIMARY KEY (id)
        ) ";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'mi_plugin_create_table');

// Función para llenar la tabla con los datos de la API
function mi_plugin_populate_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mis_datos';
    $api_url = 'https://gorest.co.in/public/v1/users';
    $token = 'a176a7e504a83bc0600617b780a67be87c6ba531fd4acaad08151a5cfc414fbd';
    
    // Realiza la petición GET a la API
    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token
        )
    ));
    
    if (is_wp_error($response)) {
        // Manejo del error
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // Verifica si hay datos en la respuesta
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $user) {
            $wpdb->insert($table_name, array(
                'name' => $user['name'],
                'email' => $user['email'],
                'gender' => $user['gender'],
                'status' => $user['status']
            ));
        }
    }
}
register_activation_hook(__FILE__, 'mi_plugin_populate_table');

// Función para mostrar los registros de la tabla en una página
function mi_plugin_display_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mis_datos';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    
    // Formulario para agregar un registro
    echo '<h3>Agregar registro</h3>';
    echo '<form id="mi-plugin-add-form">';
    echo '<div style="display:flex;">';
    echo      '<div>';
    echo '<label for="name">Nombre:</label>';
    echo '<input width:"100px"; type="text" name="name" required><br>';
    echo '<label for="email">Email:</label>';
    echo '<input type="email" name="email" required><br>';
    echo '</div>';
    echo '<div style="margin-left:50px;">';
    echo '<label for="gender">Género:</label>';
    echo '<select name="gender" required>
    <option value="male">Masculino</option>
    <option value="female">Femenino</option>
    </select><br>
    <br>';
    echo '<label for="status">Estado:</label>';
    echo '<select name="status" required>
    <option value="active">Activo</option>
    <option value="inactive">Inactivo</option>
    </select><br>';
    echo '</div>';
    echo '</div>
    <br>';
    echo '<input type="submit" value="Agregar">';
    echo '</form>';
    

    if (!empty($results)) {
        echo '<br>';
        echo '<table>';
        echo '<tr><th style="background-color:#222; color: white;
        ">ID</th><th style="background-color:#222; color: white;
        ">Nombre</th><th style="background-color:#222; color: white;
        ">Email</th><th style="background-color:#222; color: white;
        ">Sexo</th><th style="background-color:#222; color: white;
        ">Estado</th><th style="background-color:#222; color: white;
        ">Acciones</th></tr>';
        
        echo '<h1>Tabla de Registros</h1>';
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . $result->id . '</td>';
            echo '<td>' . $result->name . '</td>';
            echo '<td>' . $result->email . '</td>';
            echo '<td>' . $result->gender . '</td>';
            echo '<td>' . $result->status . '</td>';
            echo '<td>';
            echo '<a href="#" class="mi-plugin-edit-btn" data-record-id="' . $result->id . '">Editar</a> ';
            echo '<a href="#" class="mi-plugin-delete-btn" data-record-id="' . $result->id . '">Eliminar</a>';
            echo '</td>';
            echo '</tr>';
            echo '<tr class="mi-plugin-edit-row" data-record-id="' . $result->id . '" style="display: none;">';
            echo '<td colspan="6">';
            echo '<form class="mi-plugin-edit-form">';
            echo '<div style="display:flex;">';
            echo      '<div>';
            echo '<input type="hidden" name="record_id" value="' . $result->id . '">';
            echo '<label for="edit_name">Nombre:</label>';
            echo '<input type="text" name="edit_name" value="' . $result->name . '" required><br>';
            echo '<label for="edit_email">Email:</label>';
            echo '<input type="email" name="edit_email" value="' . $result->email . '" required><br>';
            echo '</div>';
            echo '<div style="margin-left:50px;">';
            echo '<label for="edit_gender">Género:</label>';
            echo '<select name="edit_gender" value="' . $result->gender . '" required>
            <option value="male">Masculino</option>
            <option value="female">Femenino</option>
            </select><br>
            <br>';
            echo '<label for="edit_status">Estado:</label>';
            echo '<select name="edit_status" value="' . $result->status . '" required>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
            </select><br>';
            echo '</div>';
            echo '</div>
            <br>';
            echo '<input type="submit" value="Guardar">';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo 'No hay registros disponibles.';
    }
}

// Función para editar un registro
function mi_plugin_edit_record() {
    if (isset($_POST['record_id']) && isset($_POST['edit_name']) && isset($_POST['edit_email']) && isset($_POST['edit_gender']) && isset($_POST['edit_status'])) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mis_datos';
        
        $record_id = absint($_POST['record_id']);
        $name = sanitize_text_field($_POST['edit_name']);
        $email = sanitize_text_field($_POST['edit_email']);
        $gender = sanitize_text_field($_POST['edit_gender']);
        $status = sanitize_text_field($_POST['edit_status']);
        
        $wpdb->update($table_name, array(
            'name' => $name,
            'email' => $email,
            'gender' => $gender,
            'status' => $status
        ), array('id' => $record_id));
        
        wp_die();
    }
}
add_action('wp_ajax_mi_plugin_edit_record', 'mi_plugin_edit_record');


// Agrega una página para mostrar la tabla
function mi_plugin_add_page() {
    add_menu_page('Mi Plugin', 'Mi Plugin', 'manage_options', 'mi-plugin', 'mi_plugin_display_table');
}
add_action('admin_menu', 'mi_plugin_add_page');

function mi_plugin_enqueue_scripts() {
    wp_enqueue_script('mi-plugin', plugin_dir_url(__FILE__) . '/js/mi-plugin.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'mi_plugin_enqueue_scripts');

function mi_plugin_enqueue_styles() {
    wp_enqueue_style('mi-plugin', plugin_dir_url(__FILE__) . '/css/mi-plugin.css');
}
add_action('admin_enqueue_scripts', 'mi_plugin_enqueue_styles');


// Función para agregar un registro
function mi_plugin_add_record() {
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['gender']) && isset($_POST['status'])) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mis_datos';
        
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_text_field($_POST['email']);
        $gender = sanitize_text_field($_POST['gender']);
        $status = sanitize_text_field($_POST['status']);
        
        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'gender' => $gender,
            'status' => $status
        ));
        
        wp_die();
    }
}
add_action('wp_ajax_mi_plugin_add_record', 'mi_plugin_add_record');

// Función para eliminar un registro
function mi_plugin_delete_record() {
    if (isset($_POST['record_id'])) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mis_datos';
        
        $record_id = absint($_POST['record_id']);
        
        $wpdb->delete($table_name, array('id' => $record_id));
        
        wp_die();
    }
}
add_action('wp_ajax_mi_plugin_delete_record', 'mi_plugin_delete_record');
