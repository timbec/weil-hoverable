<?php 

    // function add_meta_box() {
	// 	add_meta_box( 'hoverable_meta_box', __( 'Hoverable', 'hoverable' ), array( $this, 'hoverable_meta_box' ), 'hoverable', 'normal' );
	// }

    function load_meta_box() {  ?>
    <span class="hov-add">
        <img src="<?php echo WP_PLUGIN_URL; ?>/hoverable/assets/add.png" title="Add" alt="Add" /> Add New
    </span>
    <div class="hov-template">
    <?php
            // Store input boxes into a variable
            ob_start();
    ?>
        <div class="hov-item">
            <div class="hov-row1">
                <input type="text" name="hover_title[]" class="hov-title" value="@TITLE" />
                <img class="hov-remove" src="<?php echo WP_PLUGIN_URL; ?>/hoverable/assets/remove.png" title="Remove" alt="Remove" />
            </div>
            <div class="hov-row2">
                <textarea name="hover_desc[]" class="hov-desc">@DESC</textarea>
            </div>
        </div>
    <?php
            /**
            * Stick a hidden copy of the input boxes into the HTML.
            * This is used to dynamically generate new input boxes when
            * the "Add New" button is clicked
            */
            $hover_form = ob_get_clean();
            $output = str_replace('@TITLE', '', $hover_form);
            echo str_replace('@DESC', '', $output);
    ?>
    </div>
    <div class="hov-container">
    <?php
            global $post;
            $saved_data = get_post_meta($post->ID, 'hoverable', true);

            // Output input boxes for each item loaded
            if (!empty($saved_data)) {
                foreach ($saved_data as $key => $item) {
                    $output = str_replace('@TITLE', $item['hover_title'], $hover_form);
                    echo str_replace('@DESC', htmlspecialchars($item['hover_desc']), $output);
                }
            }
    ?>
    </div>
    <p>Enter a search string (top box) and its hover caption (bottom box).</p>
    <?php
        }

        /**
        * Combine hover_title and hover_desc into an associative array.
        * Then, save the array using update_post_meta. WP automagically
        * converts the array to a serialized string.
        *
        * Note: In WP < 3.0, global $post is unavailable
        */
        function save_meta_box($post_id) {
            $output = array();
            $hover_title = $_POST['hover_title'];
            $hover_desc = $_POST['hover_desc'];

            // Stick the input into an associative array (skip the first element)
            for ($i = 1, $z = count($hover_title); $i < $z; $i++) {
                $output[] = array('hover_title' => $hover_title[$i], 'hover_desc' => $hover_desc[$i]);
            }

            // Save the array as postmeta
            update_post_meta($post_id, 'hoverable', $output);
        }

        /**
        * This is a glorified search/replace. It creates a link for every
        * matching phrase, and sticks the description into a separate, hidden
        * DIV that gets triggered by Facebox.
        */
        function the_content($content) {
            if (is_single() || is_page()) {
                global $post;
                $saved_data = get_post_meta($post->ID, 'hoverable', true);

                if (!empty($saved_data)) {
                    foreach ($saved_data as $key => $item) {
                        $hover_title = $item['hover_title'];
                        $hover_desc = $item['hover_desc'];
                        $content = str_replace($hover_title, "<a href=\"#hov$key\" class=\"facebox\">$hover_title</a>", $content);
                        $hidden_divs[$key] = "<div id=\"hov$key\">$hover_desc</div>";
                    }
                }
                if (isset($hidden_divs)) {
                    return $content . '<div class="hov-modal">' . implode('', $hidden_divs) . '</div>';
                }
            }
            return $content;
        }