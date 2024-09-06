<?php
function cv_collector_display_database() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cv_collector';

    // Handle AJAX search request
    if (isset($_POST['search'])) {
        $search = sanitize_text_field($_POST['search']);
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE name LIKE %s",
                '%' . $wpdb->esc_like($search) . '%'
            )
        );
    } else {
        $results = $wpdb->get_results("SELECT * FROM $table_name");
    }

    ob_start();
    ?>
    <div id="cv-database">
        <form id="cv-search-form">
            <input type="text" name="search" placeholder="Search by name" />
            <button type="submit">Search</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>CV</th>
                </tr>
            </thead>
            <tbody id="cv-database-results">
                <?php foreach ($results as $row) : ?>
                    <tr>
                        <td><?php echo esc_html($row->id); ?></td>
                        <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html($row->email); ?></td>
                        <td><a href="<?php echo esc_url($row->cv); ?>" target="_blank">Download CV</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('cv_collector_display', 'cv_collector_display_database');