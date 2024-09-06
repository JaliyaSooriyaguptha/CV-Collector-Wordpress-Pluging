<?php

// Install function to create database table
function cv_collector_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cv_collector';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        province tinytext NOT NULL,
        district tinytext NOT NULL,
        education_level tinytext NOT NULL,
        qualifications text NOT NULL,
        experience text NOT NULL,
        cv_url text NOT NULL,
        linkedin_url text,
        portfolio_url text,
        github_url text,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'cv_collector_install');

// Function to display the CV submission form
function cv_collector_form() {
    ob_start();
    ?>

    <form id="cv-collector-form" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="province">Province:</label>
        <select id="province" name="province" required>
            <option value="central">Central</option>
            <option value="sabaragamuwa">Sabaragamuwa</option>
            <option value="eastern">Eastern</option>
            <option value="north_central">North Central</option>
            <option value="northern">Northern</option>
            <option value="uva">Uva</option>
            <option value="north_western">North Western</option>
            <option value="western">Western</option>
            <option value="southern">Southern</option>
        </select>
        
        <label for="district">District:</label>
        <select id="district" name="district" required>
            <!-- District options will be populated dynamically -->
        </select>
        
        <label for="education_level">Education Level:</label>
        <select id="education_level" name="education_level" required>
            <option value="undergraduate">Undergraduate</option>
            <option value="graduate">Graduate</option>
            <option value="msc">MSc</option>
        </select>
        
        <label for="qualifications">Qualifications:</label>
        <input type="text" id="qualifications" name="qualifications" required>
        
        <label for="experience">Experience:</label>
        <select id="experience" name="experience" required>
            <option>Below 6 Months</option>
            <option>6 Months</option>
            <option>one year</option>
            <option>Two year</option>
            <option>Three year</option>
            <option>Four year</option>
        </select>
        
        <label for="linkedin_url">LinkedIn URL:</label>
        <input type="url" id="linkedin_url" name="linkedin_url">
        
        <label for="portfolio_url">Portfolio URL (Optional):</label>
        <input type="url" id="portfolio_url" name="portfolio_url">
        
        <label for="github_url">GitHub URL:</label>
        <input type="url" id="github_url" name="github_url">
        
        <label for="cv">CV (PDF):</label>
        <input type="file" id="cv" name="cv" accept="application/pdf" required>
        
        <input type="submit" value="Submit">
    </form>

    <div id="cv-collector-response"></div>

    <?php
    return ob_get_clean();
}
add_shortcode('cv_collector_form', 'cv_collector_form');

// Function to handle AJAX form submission
function cv_collector_save_data() {
    if (!empty($_FILES['cv']['name'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cv_collector';

        // Sanitization and data handling
        $cv = $_FILES['cv'];
        $upload = wp_handle_upload($cv, array('test_form' => false));

        if ($upload && !isset($upload['error'])) {
            $cv_url = $upload['url'];

            $inserted = $wpdb->insert($table_name, array(
                'name' => sanitize_text_field($_POST['name']),
                'email' => sanitize_email($_POST['email']),
                'province' => sanitize_text_field($_POST['province']),
                'district' => sanitize_text_field($_POST['district']),
                'education_level' => sanitize_text_field($_POST['education_level']),
                'qualifications' => sanitize_text_field($_POST['qualifications']),
                'experience' => sanitize_text_field($_POST['experience']),
                'cv_url' => $cv_url,
                'linkedin_url' => sanitize_text_field($_POST['linkedin_url']),
                'portfolio_url' => sanitize_text_field($_POST['portfolio_url']),
                'github_url' => sanitize_text_field($_POST['github_url']),
            ));

            if ($inserted !== false) {
                wp_send_json_success(array('message' => 'Congratulations! Successfully added your CV.'));
            } else {
                wp_send_json_error(array('message' => 'Error inserting data into the database.'));
            }
        } else {
            wp_send_json_error(array('message' => 'There was an error uploading the file.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Please fill in all fields and upload a CV.'));
    }
}
add_action('wp_ajax_cv_collector_save_data', 'cv_collector_save_data');
add_action('wp_ajax_nopriv_cv_collector_save_data', 'cv_collector_save_data');

// Function to display the CV database table
function cv_collector_display_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cv_collector';
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    ob_start();
    ?>

    <label for="filter-province">Filter by Province:</label>
    <select id="filter-province" onchange="filterTable('filter-province', 2)">
        <option value="">All Provinces</option>
        <option value="central">Central</option>
        <option value="sabaragamuwa">Sabaragamuwa</option>
        <option value="eastern">Eastern</option>
        <option value="north_central">North Central</option>
        <option value="northern">Northern</option>
        <option value="uva">Uva</option>
        <option value="north_western">North Western</option>
        <option value="western">Western</option>
        <option value="southern">Southern</option>
    </select>

    <label for="filter-education">Filter by Education Level:</label>
    <select id="filter-education" onchange="filterTable('filter-education', 4)">
        <option value="">All Education Levels</option>
        <option value="undergraduate">Undergraduate</option>
        <option value="graduate">Graduate</option>
        <option value="msc">MSc</option>
    </select>

    <table id="cv-collector-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Province</th>
                <th>District</th>
                <th>Education Level</th>
                <th>Qualifications</th>
                <th>Experience</th>
                <th>LinkedIn URL</th>
                <th>Portfolio URL</th>
                <th>GitHub URL</th>
                <th>CV</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row) : ?>
                <tr data-id="<?php echo esc_attr($row->id); ?>">
                    <td><?php echo esc_html($row->name); ?></td>
                    <td><?php echo esc_html($row->email); ?></td>
                    <td><?php echo esc_html($row->province); ?></td>
                    <td><?php echo esc_html($row->district); ?></td>
                    <td><?php echo esc_html($row->education_level); ?></td>
                    <td><?php echo esc_html($row->qualifications); ?></td>
                    <td><?php echo esc_html($row->experience); ?></td>
                    <td><a href="<?php echo esc_url($row->linkedin_url); ?>" target="_blank">View LinkedIn</a></td>
                    <td><a href="<?php echo esc_url($row->portfolio_url); ?>" target="_blank">View Portfolio</a></td>
                    <td><a href="<?php echo esc_url($row->github_url); ?>" target="_blank">View GitHub</a></td>
                    <td><a href="<?php echo esc_url($row->cv_url); ?>" target="_blank">View CV</a></td>
                    <td>
                        <button class="delete-btn" data-id="<?php echo esc_attr($row->id); ?>">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function filterTable(filterId, columnIndex) {
            let input = document.getElementById(filterId);
            let filter = input.value.toLowerCase();
            let table = document.getElementById("cv-collector-table");
            let rows = table.getElementsByTagName("tr");
            
            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName("td");
                let cellValue = cells[columnIndex].textContent.toLowerCase();
                if (cellValue.includes(filter) || filter === "") {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    var row = this.closest('tr');
                    var id = row.getAttribute('data-id');

                    if (confirm('Are you sure you want to delete this record?')) {
                        jQuery.ajax({
                            url: cv_collector_params.ajax_url,
                            method: 'POST',
                            data: {
                                action: 'cv_collector_delete_data',
                                id: id
                            },
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    alert(response.data.message);
                                } else {
                                    console.error('Error:', response.data.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', error);
                            }
                        });
                    }
                });
            });
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('cv_collector_display', 'cv_collector_display_data');

// Handle delete request
function cv_collector_delete_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cv_collector';

    $id = intval($_POST['id']);
    $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

    if ($result) {
        wp_send_json_success(array('message' => 'Record deleted successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete record.'));
    }
}
add_action('wp_ajax_cv_collector_delete_data', 'cv_collector_delete_data');
