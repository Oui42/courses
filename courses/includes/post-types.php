<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register CPT: course, lesson and taxonomy: course_category
 * Also add metabox for course settings (powiązany produkt WC, exam JSON builder)
 */
add_action( 'init', function() {
    // Course
    $labels = array(
        'name' => __( 'Kursy', 'courses' ),
        'singular_name' => __( 'Kurs', 'courses' ),
        'add_new_item' => __( 'Dodaj nowy kurs', 'courses' ),
        'edit_item' => __( 'Edytuj kurs', 'courses' ),
        'new_item' => __( 'Nowy kurs', 'courses' ),
        'view_item' => __( 'Zobacz kurs', 'courses' ),
    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'capability_type' => 'post',
        'rewrite' => array( 'slug' => 'courses' ),
        'menu_icon' => 'dashicons-welcome-learn-more',
    );
    register_post_type( 'course', $args );

    // Lesson
    $labels = array(
        'name' => __( 'Lekcje', 'courses' ),
        'singular_name' => __( 'Lekcja', 'courses' ),
    );
    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_rest' => true,
        'supports' => array( 'title', 'editor', 'excerpt' ),
        'rewrite' => array( 'slug' => 'lessons' ),
    );
    register_post_type( 'lesson', $args );

    // Taxonomy
    register_taxonomy( 'course_category', array( 'course' ), array(
        'label' => __( 'Kategorie kursów', 'courses' ),
        'hierarchical' => true,
        'show_in_rest' => true,
    ));
});

/**
 * Metabox for course settings: linked WooCommerce product ID and graphical exam builder
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'courses_settings', __( 'Ustawienia kursu', 'courses' ), 'courses_course_settings_meta_box', 'course', 'side', 'default' );
});

function courses_course_settings_meta_box( $post ) {
    wp_nonce_field( 'courses_save_meta', 'courses_meta_nonce' );
    $product_id = get_post_meta( $post->ID, '_courses_wc_product_id', true );
    $exam_json = get_post_meta( $post->ID, '_courses_exam_json', true );
    $exam_data = $exam_json ? esc_textarea( $exam_json ) : '[]';
    ?>
    <p>
        <label for="courses_wc_product_id"><?php echo esc_html__( 'Powiązany produkt WooCommerce (ID)', 'courses' ); ?></label><br>
        <input type="number" name="courses_wc_product_id" id="courses_wc_product_id" value="<?php echo esc_attr( $product_id ); ?>" style="width:100%;">
        <small><?php echo esc_html__( 'Wpisz ID produktu, którego zakup zapisze użytkownika na ten kurs. Możesz zostawić puste.', 'courses' ); ?></small>
    </p>

    <h4><?php echo esc_html__( 'Egzamin (graficzny builder)', 'courses' ); ?></h4>

    <div id="courses-exam-builder" data-exam='<?php echo $exam_data; ?>'></div>

    <!-- hidden field to store JSON, this is what will be saved -->
    <input type="hidden" id="courses_exam_json" name="courses_exam_json" value="<?php echo esc_attr( $exam_json ); ?>">

    <p><small><?php echo esc_html__( 'Możesz dodawać pytania jednokrotnego wyboru (radio) lub tekstowe. Zapisane automatycznie razem z postem.', 'courses' ); ?></small></p>

    <?php
}

add_action( 'save_post_course', function( $post_id ) {
    if ( ! isset( $_POST['courses_meta_nonce'] ) || ! wp_verify_nonce( $_POST['courses_meta_nonce'], 'courses_save_meta' ) ) {
        return;
    }
    if ( isset( $_POST['courses_wc_product_id'] ) ) {
        update_post_meta( $post_id, '_courses_wc_product_id', intval( $_POST['courses_wc_product_id'] ) );
    }
    if ( isset( $_POST['courses_exam_json'] ) ) {
        // Validate JSON
        $json = trim( wp_unslash( $_POST['courses_exam_json'] ) );
        if ( $json === '' ) {
            delete_post_meta( $post_id, '_courses_exam_json' );
        } else {
            json_decode( $json );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                update_post_meta( $post_id, '_courses_exam_json', $json );
            } else {
                // invalid JSON -> do not overwrite
            }
        }
    }
});
