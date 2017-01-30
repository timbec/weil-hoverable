<?php
/*
Plugin Name: Weil Hoverable
Plugin URI: http://weil.com
Description: Attach hoverable context to phrases within your posts. Customized and updated for Weil, Gotshal, Menges blog network. Original Author: Matt Gibbs@forumone.com
Version: 1.0.3
Author: Tim Beckett
Author URI: http://tim-beckett.com

Original Copyright 2010  Matt Gibbs  (email : mgibbs@forumone.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*********************************************************
    Not a great practice but to access the pre-existing data in database (stored by old Hoverable plugin), had to change textdomain from 'weil-hoverable' to 'hoverable'.
    See original and add animations: http://dimsemenov.com/plugins/magnific-popup/documentation.html#animation
**********************************************************/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

//Need to change this class name, so it doesn't clash with the original Hoverable'
if (! class_exists ( 'Weil_Hoverable' ) ) :
    class Weil_Hoverable
    {
        /** Singleton *************************************************************/

        /**
        * @var Weil_Hoverables
        */
        private static $instance;
        private static $actions;

        /**
	 * Main Weil_Hoverable Instance
	 *
	 * Insures that only one instance of Weil-Hoverable exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since v1.0
	 * @staticvar array $instance
	 * @see pw_Weil_Hoverable_load()
	 * @return The one true Weil_Hoverable
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Weil_Hoverable;
			self::$instance->init();
            self::$instance->print_scripts();
            self::$instance->includes();
			do_action( 'weil_hoverables_loaded' );
		}
		return self::$instance;
	}

    //This is wrong. Check Pippins code. 
    private function init() {
        //add_action('admin_init', array($hoverable, 'admin_init'));
        // add_action('admin_head', array($hoverable, 'wp_head'));
        
        /* Backend */
		add_action( 'admin_menu',               array( $this, 'add_meta_box' ) );
     
        add_action('save_post',                 array($this, 'save_meta_box') );

        add_action( 'admin_enqueue_scripts',       array( $this, 'admin_styles' ) );

		add_action( 'admin_print_styles',       array( $this, 'admin_styles' ) );

		/* Frontend */
        add_action( 'wp_enqueue_scripts',         array( $this, 'print_scripts') );

		add_action( 'wp_enqueue_scripts',            array( $this, 'print_styles'  ) );

        //is this returning? 
        add_filter('the_content', array($this, 'the_content'));

    }

//https://forums.envato.com/t/wp-debug-notice-wp-enqueue-script-was-called-incorrectly/76681/6
    function print_scripts() {
        wp_enqueue_script( 'magnific-popup', plugin_dir_url( __FILE__ ) . 'assets/js/magnific-popup.js', array( 'jquery' ), null, true );
        wp_enqueue_script( 'custom-styles', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), null, true );
    }

    function admin_styles() {
        wp_enqueue_style( 'admin-styles', plugin_dir_url( __FILE__ ) . 'assets/css/admin-style.css' );
    }

    function print_styles() {
        wp_enqueue_style( 'magnific-popup', plugin_dir_url( __FILE__ ) . 'assets/css/magnific.css' );
        wp_enqueue_style( 'custom-styles', plugin_dir_url( __FILE__ ) . 'assets/css/custom-style.css' );
    }

    private function includes() {
		//include_once( dirname( __FILE__ ) . '/includes/load-meta-box.php' );
	}

    function add_meta_box() {
		add_meta_box( 'hoverable', __( 'Add Hover Text', 'hoverable' ), array( $this, 'load_meta_box' ), 'post', 'advanced' );
	}


    function load_meta_box() {  ?>
    <span class="hov-add">
        <img src="<?php echo WP_PLUGIN_URL; ?>/weil-hoverable/assets/img/add.png" title="Add" alt="Add" /> Add New
    </span>
    <div class="hov-template">
    <?php
            // Store input boxes into a variable
            ob_start();
    ?>
        <div class="hov-item">
            <div class="hov-row1">
                <input type="text" name="hover_title[]" class="hov-title" value="@TITLE" />
                <img class="hov-remove" src="<?php echo WP_PLUGIN_URL; ?>/weil-hoverable/assets/img/remove.png" title="Remove" alt="Remove" />
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
                        $hover_desc .= '<footer></footer>'; 
                        $content = str_replace($hover_title, "<a href=\"#hov$key\" class=\"hoverbox-popup\">$hover_title</a>", $content);
                        $hidden_divs[$key] = "<div id=\"hov$key\" class=\"popup-box\"><p>$hover_desc</p></div>";
                    }
                }
                if (isset($hidden_divs)) {
                    return $content . '<div class="hov-modal">' . implode('', $hidden_divs) . '</div>';
                }
            }
            return $content;
        }
}
endif; 

function weil_hoverable_load() {
	return Weil_Hoverable::instance();
}

// load Easy Featured Comments
weil_hoverable_load();