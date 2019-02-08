<?php
    /*
    Plugin Name: B3 - User onboarding
    Version: 0.6-beta
    Tags: user, management, registration, login, forgot password, reset password, account
    Plugin URI: http://www.berrplasman.com
    Description: This plugin handles the registration/login process
    Author: Beee
    Author URI: http://www.berryplasman.com
    Text-domain: b3-onboarding

    Source: https://code.tutsplus.com/tutorials/build-a-custom-wordpress-user-flow-part-1-replace-the-login-page--cms-23627

    http://www.berryplasman.com
       ___  ____ ____ ____
      / _ )/ __/  __/  __/
     / _  / _/   _/   _/
    /____/___/____/____/

    
    @TODO:
    - processing through AJAX
    
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    if ( ! class_exists( 'B3Onboarding' ) ) {
    
        class B3Onboarding {
        
            /**
             * Initializes the plugin.
             *
             * To keep the initialization fast, only add filter and action
             * hooks in the constructor.
             */
            /**
             *  A dummy constructor to ensure plugin is only initialized once
             */
            function __construct() {
            }
    
            function initialize() {
                $this->settings = array(
                    'path'    => trailingslashit( dirname( __FILE__ ) ),
                    'version' => '0.6-beta',
                );
    
                // set text domain
                load_plugin_textdomain( 'b3-onboarding', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
                // actions
                register_activation_hook( __FILE__,            array( $this, 'b3_plugin_activation' ) );
                register_deactivation_hook( __FILE__,          array( $this, 'b3_plugin_deactivation' ) );
            
                add_action( 'wp_enqueue_scripts',                   array( $this, 'b3_enqueue_scripts_frontend' ) );
                add_action( 'admin_enqueue_scripts',                array( $this, 'b3_enqueue_scripts_backend' ) );
                add_action( 'admin_menu',                           array( $this, 'b3_add_admin_pages' ) );
                add_action( 'admin_init',                           array( $this, 'b3_register_settings_fields' ) );
                add_action( 'init',                                 array( $this, 'b3_registration_form_handling' ) );
                add_action( 'admin_init',                           array( $this, 'b3_add_post_notice' ) );
                add_action( 'widgets_init',                         array( $this, 'b3_register_widgets' ) );
                // add_action( 'wp_print_footer_scripts',               array( $this, 'add_captcha_js_to_footer' ) );
            
                add_filter( 'display_post_states',                  array( $this, 'b3_add_post_state' ), 10, 2 );
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),  array( $this, 'b3_settings_link' ) );
    
                add_filter( 'wp_mail_from',                         array( $this, 'b3_email_from' ) );
                add_filter( 'wp_mail_from_name',                    array( $this, 'b3_email_from_name' ) );
                add_filter( 'wp_mail_content_type',                 array( $this, 'b3_email_content_type' ) );
                add_filter( 'wp_mail_charset',                      array( $this, 'b3_email_charset' ) );
    
    
                add_shortcode( 'register-form',                array( $this, 'b3_render_register_form' ) );
                add_shortcode( 'login-form',                   array( $this, 'b3_render_login_form' ) );
                add_shortcode( 'forgotpass-form',              array( $this, 'b3_render_forgot_password_form' ) );
                add_shortcode( 'resetpass-form',               array( $this, 'b3_render_reset_password_form' ) );
                // add_shortcode( 'account-page',                  array( $this, 'b3_render_account_page' ) );
            
                include( 'includes/constants.php' );
                include( 'includes/do-stuff.php' );
                include( 'includes/dashboard-widget.php' );
                include( 'includes/emails.php' );
                include( 'includes/examples.php' );
                include( 'includes/filters.php' );
                include( 'includes/form-handling.php' );
                include( 'includes/functions.php' );
                include( 'includes/tabs.php' );
            }
        
            /*
             * Do stuff upon plugin activation
             */
            public function b3_plugin_activation() {
            
                // create necessary pages
                b3_create_initial_pages();
                
                $this->b3_set_default_settings();
    
                /**
                 * Independent
                 */
                $aw_activation = get_role( 'b3_activation' );
                if ( ! $aw_activation ) {
                    add_role( 'b3_activation', __( 'Awaiting activation' ), [] );
                }
                $aw_approval = get_role( 'b3_approval' );
                if ( ! $aw_approval ) {
                    add_role( 'b3_approval', __( 'Awaiting approval' ), [] );
                }
    
            }
    
    
            public function b3_set_default_settings() {

                // update_option( 'b3_add_br_html_email', '0' );
                // update_option( 'b3_custom_emails', '0' );
                // update_option( 'b3_custom_passwords', '0' );
                // update_option( 'b3_dashboard_widget', '1' );
                // update_option( 'b3_html_emails', '0' );
                // update_option( 'b3_mail_sending_method', 'wpmail' );
                update_option( 'b3_notification_sender_email', get_bloginfo( 'admin_email' ) );
                update_option( 'b3_notification_sender_name',get_bloginfo( 'name' ) );
                // update_option( 'b3_sidebar_widget', '1' );
    
            }
    
            /*
             * Do stuff upon plugin activation
             */
            public function b3_plugin_deactivation() {
                
                // remove settings is for testing
    
                $meta_keys = b3_get_all_custom_meta_keys();
                foreach ( $meta_keys as $key ) {
                    delete_option( $key );
                }
    
                $roles = array(
                    'b3_activation',
                    'b3_approval',
                );
                foreach( $roles as $role ) {
                    remove_role( $role );
                }
            }
        
        
            /**
             * Enqueue scripts front-end
             */
            public function b3_enqueue_scripts_frontend() {
                wp_enqueue_style( 'b3-main', plugins_url( 'assets/css/style.css', __FILE__ ) );
                wp_enqueue_script( 'b3-ob-js', plugins_url( 'assets/js/js.js', __FILE__ ), array( 'jquery' ) );
            }
        
        
            /**
             * Enqueue scripts in backend
             */
            public function b3_enqueue_scripts_backend() {
                wp_enqueue_style( 'b3-admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
                // wp_enqueue_script( 'b3-tabs', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ) );
                wp_enqueue_script( 'b3-ob-js-admin', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ) );
            }
        
        
            /**
             * Adds a page to admin sidebar menu
             */
            public function b3_add_admin_pages() {
                include( 'includes/admin-page.php' ); // content for the settings page
                add_menu_page( 'B3 Onboarding', 'B3 Onboarding', 'manage_options', 'b3-onboarding', 'b3_user_register_settings', '', '3' );
            }
        
        
            /**
             * Handle registration form
             */
            public function b3_registration_form_handling() {
            
                if ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] ) {
                    if ( isset( $_POST[ 'b3_register_user' ] ) ) {
                        $redirect_url = home_url( 'register' );
                        if ( ! wp_verify_nonce( $_POST[ "b3_register_user" ], 'b3-register-user' ) ) {
                            // error ?
                        } else {
                        
                            $user_login = ( isset( $_POST[ 'b3_user_login' ] ) ) ? $_POST[ 'b3_user_login' ] : false;
                            $user_email = ( isset( $_POST[ 'b3_user_email' ] ) ) ? $_POST[ 'b3_user_email' ] : false;
                            $first_name = ( isset( $_POST[ 'b3_first_name' ] ) ) ? sanitize_text_field( $_POST[ 'b3_first_name' ] ) : false;
                            $last_name  = ( isset( $_POST[ 'b3_last_name' ] ) ) ? sanitize_text_field( $_POST[ 'b3_last_name' ] ) : false;
                            $meta_data  = [];
                        
                            if ( ! is_multisite() ) {
                                if ( ! get_option( 'users_can_register' ) ) {
                                    // Registration closed, display error
                                    $redirect_url = add_query_arg( 'registration-error', 'closed', $redirect_url );
                                    // @TODO: add option if users should still be allowed to register
                                } else {
    
                                    if ( false != $first_name ) {
                                        $meta_data[ 'first_name' ] = $first_name;
                                    }
                                    if ( false != $last_name ) {
                                        $meta_data[ 'last_name' ] = $last_name;
                                    }
    
                                    // register new user
                                    $result = $this->b3_register_user( $user_login, $user_email, $meta_data );
    
                                    if ( is_wp_error( $result ) ) {
                                        // Parse errors into a string and append as parameter to redirect
                                        $errors       = join( ',', $result->get_error_codes() );
                                        $redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
                                    } else {
                                        // Success, redirect to login page.
                                        $redirect_url = home_url( 'login' ); // @TODO: make dynamic
                                        $redirect_url = add_query_arg( 'registered', 'success', $redirect_url );
                                    }
                                }
                                
                            } else {
                                
                                // is_multisite
                                $meta_data  = array(
                                    'blog_public' => '1',
                                    'lang_id'     => '0',
                                );
                                $meta       = apply_filters( 'add_user_meta', $meta_data );
                                $sub_domain = ( isset( $_POST[ 'b3_subdomain' ] ) ) ? $_POST[ 'b3_subdomain' ] : false;
                                if ( false != $sub_domain ) {
                                    // @TODO: check this for options
                                    if ( true == domain_exists( $sub_domain, '/' ) ) {
                                        $redirect_url = add_query_arg( 'registration-error', 'domain_exists', $redirect_url );
                                    }
                                    $result = $this->b3_register_wpmu_user( $user_login, $user_email, $sub_domain, $meta );
                                }
                            }
                        }
                    
                        wp_redirect( $redirect_url );
                        exit;
                    
                    }
                }
            }
        
        
            /**
             * Add post states for plugin pages
             *
             * @param $post_states
             * @param $post
             *
             * @return array
             */
            public function b3_add_post_state( $post_states, $post ) {
                
                if ( defined( 'B3_ACCOUNT' ) && $post->ID == B3_ACCOUNT ) {
                    $post_states[] = 'B3: Account';
                }
                if ( defined( 'B3_REGISTER' ) && $post->ID == B3_REGISTER ) {
                    $post_states[] = 'B3: Register';
                }
                if ( defined( 'B3_LOGIN' ) && $post->ID == B3_LOGIN ) {
                    $post_states[] = 'B3: Login';
                }
                if ( defined( 'B3_FORGOTPASS' ) && $post->ID == B3_FORGOTPASS ) {
                    $post_states[] = 'B3: Forgot password';
                }
                if ( defined( 'B3_RESETPASS' ) && $post->ID == B3_RESETPASS ) {
                    $post_states[] = 'B3: Reset password';
                }
                
            
                return $post_states;
            }
        
        
            /**
             * Add settings link to plugin page
             *
             * @param $links
             *
             * @return mixed
             */
            public function b3_settings_link( $links ) {
                $settings_link = '<a href="admin.php?page=b3-onboarding">' . esc_html__( 'Settings', 'b3-onboarding' ) . '</a>';
                // add if for if add-ons are active
                $add_ons_link  = '<a href="admin.php?page=b3-onboarding&tab=addon">' . esc_html__( 'Add-ons', 'b3-onboarding' ) . '</a>';
                array_unshift( $links, $settings_link );
            
                return $links;
            }
    
    
            /**
             * For filter 'wp_mail_from', returns a proper from-address when sending e-mails
             *
             * @param   string $original_email_address
             * @return  string
             */
            public function b3_email_from( $original_email_address ) {
                // Make sure the email adress is from the same domain as your website to avoid being marked as spam.
                $from_email = get_option( 'b3_notification_sender_name' );
                if ( $from_email ) {
                    return $from_email;
                }
        
                return $original_email_address;
            }
    
    
            /**
             * For filter 'wp_mail_from_name', returns a proper from-name when sending e-mails
             *
             * @param   string $original_email_from
             * @return  string
             */
            public function b3_email_from_name( $original_from_name ) {
                
                $sender_name = get_option( 'b3_notification_sender_name' );
                if ( $sender_name ) {
                    return $sender_name;
                }
                
                return $original_from_name;
            }
    
    
            /**
             * For filter 'wp_mail_content_type', overrides content-type
             *
             * @return  string
             */
            public function b3_email_content_type() {
                $html_emails = get_option( 'b3_notification_sender_name' );
                if ( $html_emails ) {
                    return 'text/html';
                }
                
                return 'text/plain';
            }
    
    
            /**
             * For filter 'wp_mail_charset', overrides char-set
             *
             * @return  string
             */
            public function b3_email_charset() {
                $char_set = get_option( 'b3_email_charset' );
                if ( $char_set ) {
                    error_log('charset');
                    return $char_set;
                }

                return 'UTF-8';
            }
            
            
            /**
             * Redirects the user to the correct page depending on whether he / she
             * is an admin or not.
             *
             * @param string $redirect_to An optional redirect_to URL for admin users
             */
            private function redirect_logged_in_user( $redirect_to = null ) {
                $user = wp_get_current_user();
                if ( user_can( $user, 'manage_options' ) ) {
                    if ( $redirect_to ) {
                        wp_safe_redirect( $redirect_to );
                    } else {
                        wp_redirect( admin_url() );
                    }
                } else {
                    wp_redirect( home_url() );
                }
            }
        
        
            /**
             *
             */
            public function b3_add_post_notice() {
                if ( ! empty( $_GET[ 'post' ] ) ) {
                    $post = get_post( $_GET[ 'post' ] );
                    if ( 'page' == $post->post_type && 'account' == $post->post_name ) {
                        /* Add a notice to the edit page */
                        add_action( 'edit_form_after_title', array( $this, 'b3_add_page_notice' ), 1 );
                    }
                }
            }
        
        
            /**
             *
             */
            public function b3_register_widgets() {
                if ( true == get_option( 'b3_sidebar_widget' ) ) {
                    include( 'includes/B3WidgetSidebar.php' );
                    register_widget( 'B3WidgetSidebar' );
                }
            }
        
        
            /**
             *
             */
            public function b3_add_page_notice() {
                echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'You are currently editing the profile edit page. Do not edit the slug of this page!', 'b3-onboarding' ) . '</p></div>';
            }
        
        
            /**
             * Finds and returns a matching error message for the given error code.
             *
             * @param string $error_code The error code to look up.
             *
             * @return string               An error message.
             */
            private function get_error_message( $error_code ) {
                switch ( $error_code ) {
                
                    // Login errors
                    case 'empty_username':
                        return esc_html__( 'Enter a user name', 'b3-onboarding' );
                
                    case 'empty_password':
                        return esc_html__( 'You need to enter a password to login.', 'b3-onboarding' );
                
                    case 'invalid_username':
                        return esc_html__( "We don't have any users with that email address. Maybe you used a different one when signing up?", 'b3-onboarding' );
                
                    case 'incorrect_password':
                        $err = esc_html__( "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?", 'b3-onboarding' );
                    
                        return sprintf( $err, wp_lostpassword_url() );
                
                    // Registration errors
                    case 'username_exists':
                        return esc_html__( 'This username address is already in use.', 'b3-onboarding' );
                
                    case 'email':
                        return esc_html__( 'The email address you entered is not valid.', 'b3-onboarding' );
                
                    case 'email_exists':
                        return esc_html__( 'An account exists with this email address.', 'b3-onboarding' );
                
                    case 'closed':
                        return esc_html__( 'Registering new users is currently not allowed.', 'b3-onboarding' );
                
                    case 'captcha':
                        return esc_html__( 'The Google reCAPTCHA check failed. Are you a robot?', 'b3-onboarding' );
                
                    // Lost password
                    case 'invalid_email':
                    case 'invalidcombo':
                        return esc_html__( 'There are no users registered with this email address.', 'b3-onboarding' );
                
                    // Reset password
                    case 'expiredkey':
                    case 'invalidkey':
                        return esc_html__( 'The password reset link you used is not valid anymore.', 'b3-onboarding' );
                
                    case 'password_reset_mismatch':
                        return esc_html__( "The two passwords you entered don't match.", 'b3-onboarding' );
                
                    case 'password_reset_empty':
                        return esc_html__( "Sorry, we don't accept empty passwords.", 'b3-onboarding' );
                
                    // Multisite
                    case 'domain_exists':
                        return esc_html__( "Sorry, this subdomain has already been taken.", 'b3-onboarding' );
                
                    case 'user_registered':
                        return esc_html__( "You have successfully registered. Please check your email for an activation link.", 'b3-onboarding' );
                
                    case 'settings_saved':
                        return esc_html__( "Settings saved", 'b3-onboarding' );
                
                    default:
                        break;
                }
            
                return esc_html__( 'An unknown error occurred. Please try again later.', 'b3-onboarding' );
            }
        
        
            /**
             * Validates and then completes the new user signup process if all went well.
             *
             * @param string $user_login
             * @param string $user_email
             * @param string array $meta
             *
             * @return int|WP_Error
             */
            protected function b3_register_user( $user_login, $user_email, $meta = array() ) {
                $errors = new WP_Error();
            
                if ( username_exists( $user_login ) ) {
                    $errors->add( 'username_exists', $this->get_error_message( 'username_exists' ) );
                
                    return $errors;
                }
            
                if ( ! is_email( $user_email ) ) {
                    $errors->add( 'email', $this->get_error_message( 'email' ) );
                
                    return $errors;
                }
            
                if ( username_exists( $user_email ) || email_exists( $user_email ) ) {
                    $errors->add( 'email_exists', $this->get_error_message( 'email_exists' ) );
                
                    return $errors;
                }
            
                if ( ! is_email( $user_email ) ) {
                    $errors->add( 'email', $this->get_error_message( 'email' ) );
                
                    return $errors;
                }
            
                if ( username_exists( $user_email ) || email_exists( $user_email ) ) {
                    $errors->add( 'email_exists', $this->get_error_message( 'email_exists' ) );
                
                    return $errors;
                }
            
                $user_data = array(
                    'user_login' => $user_login,
                    'user_email' => $user_email,
                    'user_pass'  => '',
                );

                $first_name = ( isset( $meta[ 'first_name' ] ) && ! empty( $meta[ 'first_name' ] ) ) ? $meta[ 'first_name' ] : false;
                if ( false != $first_name ) {
                    $user_data[ 'first_name' ] = $first_name;
                }

                $last_name  = ( isset( $meta[ 'last_name' ] ) && ! empty( $meta[ 'last_name' ] ) ) ? $meta[ 'last_name' ] : false;
                if ( false != $first_name ) {
                    $user_data[ 'last_name' ] = $last_name;
                }
            
                // return 160878; // for testing
            
                $user_id = wp_insert_user( $user_data );
                if ( ! is_wp_error( $user_id ) ) {
                    wp_new_user_notification( $user_id, null, 'user' ); // @TODO: make notify 'changable'
                }
                // @TODO: add if for if user needs to activate
                // @TODO: add if for if admin needs to activate
            
                return $user_id;
            }
        
            private function b3_register_wpmu_user( $user_name, $user_email, $sub_domain, $meta = array() ) {
                $errors = new WP_Error();
            
                $register_type = get_site_option( 'registration' );
                if ( 'user' == $register_type ) {
                    wpmu_signup_user( $user_name, $user_email, $meta );
                } elseif ( 'all' == $register_type ) {
                    wpmu_signup_blog( $sub_domain . '.' . $_SERVER[ 'HTTP_HOST' ], '/', ucfirst( $sub_domain ), $user_name, $user_email, apply_filters( 'add_signup_meta', $meta ) );
                } elseif ( 'none' == $register_type ) {
                    wpmu_signup_blog( $sub_domain . '.' . $_SERVER[ 'HTTP_HOST' ], '/', ucfirst( $sub_domain ), $user_name, $user_email, apply_filters( 'add_signup_meta', $meta ) );
                    // @TODO: add if for user or user + site
                    // @TODO: add if for if user needs to activate
                    // @TODO: add if for if admin needs to activate
                }
            
                $errors->add( 'user_registered', $this->get_error_message( 'user_registered' ) );
            
                return $errors;
            }
        
        
            /**
             * An action function used to include the reCAPTCHA JavaScript file
             * at the end of the page.
             */
            public function add_captcha_js_to_footer() {
                echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
            }
        
        
            /**
             * Registers the settings fields needed by the plugin (echoed on user page)
             */
            public function b3_register_settings_fields() {
                // Create settings fields for the two keys used by reCAPTCHA
                register_setting( 'general', 'b3-onboarding-recaptcha-site-key' );
                register_setting( 'general', 'b3-onboarding-recaptcha-secret-key' );
            
                ## add to site settings (general)
                // add_settings_field(
                //     'b3-onboarding-recaptcha-site-key',
                //     '<label for="b3-onboarding-recaptcha-site-key">' . esc_html__( 'reCAPTCHA site key' , 'b3-onboarding' ) . '</label>',
                //     array( $this, 'render_recaptcha_site_key_field' ),
                //     'general'
                // );
            
                // add_settings_field(
                //     'b3-onboarding-recaptcha-secret-key',
                //     '<label for="b3-onboarding-recaptcha-secret-key">' . esc_html__( 'reCAPTCHA secret key' , 'b3-onboarding' ) . '</label>',
                //     array( $this, 'render_recaptcha_secret_key_field' ),
                //     'general'
                // );
            }
        
            public function render_recaptcha_site_key_field() {
                $value = get_option( 'b3-onboarding-recaptcha-site-key', '' );
                echo '<input type="text" id="b3-onboarding-recaptcha-site-key" name="b3-onboarding-recaptcha-site-key" value="' . esc_attr( $value ) . '" />';
            }
        
            public function render_recaptcha_secret_key_field() {
                $value = get_option( 'b3-onboarding-recaptcha-secret-key', '' );
                echo '<input type="text" id="b3-onboarding-recaptcha-secret-key" name="b3-onboarding-recaptcha-secret-key" value="' . esc_attr( $value ) . '" />';
            }
        
        
            ## FORM RENDERS
        
            /**
             * A shortcode for rendering the new user registration form.
             *
             * @param  array $attributes Shortcode attributes.
             * @param  string $content The text content for shortcode. Not used.
             *
             * @return string  The shortcode output
             */
            public function b3_render_register_form( $user_variables, $content = null ) {
            
                // Parse shortcode attributes
                $default_attributes = array(
                    'show_title' => false,
                    'template'   => 'register-form',
                );
                $attributes         = shortcode_atts( $default_attributes, $user_variables );
            
                // @TODO: IF
                // Retrieve recaptcha key
                $attributes[ 'recaptcha_site_key' ] = get_option( 'b3-onboarding-recaptcha-site-key', null );
            
                if ( is_user_logged_in() ) {
                    return esc_html__( 'You are already signed in.', 'b3-onboarding' );
                    // } elseif ( ! get_option( 'users_can_register' ) ) {
                    //     return esc_html__( 'Registering new users is currently not allowed.', 'b3-onboarding' );
                } else {
                
                    // Retrieve possible errors from request parameters
                    $attributes[ 'errors' ] = array();
                    if ( isset( $_REQUEST[ 'registration-error' ] ) ) {
                        $error_codes = explode( ',', $_REQUEST[ 'registration-error' ] );
                    
                        foreach ( $error_codes as $error_code ) {
                            $attributes[ 'errors' ][] = $this->get_error_message( $error_code );
                        }
                    }
                
                    return $this->get_template_html( $attributes[ 'template' ], $attributes );
                }
            }
        
        
            /**
             * A shortcode for rendering the login form.
             *
             * @param  array $attributes Shortcode attributes.
             * @param  string $content The text content for shortcode. Not used.
             *
             * @return string  The shortcode output
             */
            public function b3_render_login_form( $user_variables, $content = null ) {
            
                // Parse shortcode attributes
                $default_attributes = array(
                    'show_title' => false,
                    'template'   => 'login-form',
                );
                $attributes         = shortcode_atts( $default_attributes, $user_variables );
            
                $show_title = $attributes[ 'show_title' ];
            
                if ( is_user_logged_in() ) {
                    return esc_html__( 'You are already signed in.', 'b3-onboarding' );
                }
            
                // Pass the redirect parameter to the WordPress login functionality: by default,
                // don't specify a redirect, but if a valid redirect URL has been passed as
                // request parameter, use it.
                $attributes[ 'redirect' ] = '';
                if ( isset( $_REQUEST[ 'redirect_to' ] ) ) {
                    $attributes[ 'redirect' ] = wp_validate_redirect( $_REQUEST[ 'redirect_to' ], $attributes[ 'redirect' ] );
                }
            
                // Error messages
                $errors = array();
                if ( isset( $_REQUEST[ 'login' ] ) ) {
                    $error_codes = explode( ',', $_REQUEST[ 'login' ] );
                
                    foreach ( $error_codes as $code ) {
                        $errors [] = $this->get_error_message( $code );
                    }
                }
                $attributes[ 'errors' ] = $errors;
            
                // Check if user just updated password
                $attributes[ 'password_updated' ] = isset( $_REQUEST[ 'password' ] ) && $_REQUEST[ 'password' ] == 'changed';
                // Check if the user just requested a new password
                $attributes[ 'lost_password_sent' ] = isset( $_REQUEST[ 'checkemail' ] ) && $_REQUEST[ 'checkemail' ] == 'confirm';
                // Check if user just logged out
                $attributes[ 'logged_out' ] = isset( $_REQUEST[ 'logged_out' ] ) && $_REQUEST[ 'logged_out' ] == true;
                // Check if the user just registered
                $attributes[ 'registered' ] = isset( $_REQUEST[ 'registered' ] );
            
                // Render the login form using an external template
                // return $this->get_template_html( 'login-form', $attributes );
                return $this->get_template_html( $attributes[ 'template' ], $attributes );
            }
        
        
            /**
             * A shortcode for rendering the form used to initiate the password reset.
             *
             * @param  array $attributes Shortcode attributes.
             * @param  string $content The text content for shortcode. Not used.
             *
             * @return string  The shortcode output
             */
            public function b3_render_forgot_password_form( $user_variables, $content = null ) {
                // Parse shortcode attributes
                $default_attributes = array(
                    'show_title' => false,
                    'template'   => 'forgotpass-form',
                );
                $attributes         = shortcode_atts( $default_attributes, $user_variables );
            
                // Retrieve possible errors from request parameters
                $attributes[ 'errors' ] = array();
                if ( isset( $_REQUEST[ 'errors' ] ) ) {
                    $error_codes = explode( ',', $_REQUEST[ 'errors' ] );
                
                    foreach ( $error_codes as $error_code ) {
                        $attributes[ 'errors' ] [] = $this->get_error_message( $error_code );
                    }
                }
            
                if ( is_user_logged_in() ) {
                    return esc_html__( 'You are already logged in.', 'b3-onboarding' );
                } else {
                    return $this->get_template_html( $attributes[ 'template' ], $attributes );
                }
            }
        
        
            /**
             * A shortcode for rendering the form used to reset a user's password.
             *
             * @param  array $attributes Shortcode attributes.
             * @param  string $content The text content for shortcode. Not used.
             *
             * @return string  The shortcode output
             */
            public function b3_render_reset_password_form( $user_variables, $content = null ) {
                // Parse shortcode attributes
                $default_attributes = array(
                    'show_title' => false,
                    'template'   => 'resetpass-form',
                );
                $attributes         = shortcode_atts( $default_attributes, $user_variables );
            
                if ( is_user_logged_in() ) {
                    return esc_html__( 'You are already signed in.', 'b3-onboarding' );
                } else {
                    if ( isset( $_REQUEST[ 'login' ] ) && isset( $_REQUEST[ 'key' ] ) ) {
                        $attributes[ 'login' ] = $_REQUEST[ 'login' ];
                        $attributes[ 'key' ]   = $_REQUEST[ 'key' ];
                    
                        // Error messages
                        $errors = array();
                        if ( isset( $_REQUEST[ 'error' ] ) ) {
                            $error_codes = explode( ',', $_REQUEST[ 'error' ] );
                        
                            foreach ( $error_codes as $code ) {
                                $errors [] = $this->get_error_message( $code );
                            }
                        }
                        $attributes[ 'errors' ] = $errors;
                    
                        return $this->get_template_html( $attributes[ 'template' ], $attributes );
                    } else {
                        return esc_html__( 'Invalid password reset link.', 'b3-onboarding' );
                    }
                }
            }
        
        
            /**
             * A shortcode to render the account page
             *
             * @param      $attributes
             * @param null $content
             *
             * @return string
             */
            public function b3_render_account_page( $user_variables, $content = null ) {
            
                // Parse shortcode attributes
                $default_attributes = array(
                    'show_title' => false,
                    'template'   => 'account-page',
                );
                $attributes         = shortcode_atts( $default_attributes, $user_variables );
            
                return $this->get_template_html( $attributes[ 'template' ], $attributes );
            }
        
        
            /**
             * Renders the contents of the given template to a string and returns it.
             *
             * @param string $template_name The name of the template to render (without .php)
             * @param array $attributes The PHP variables for the template
             *
             * @return string               The contents of the template.
             */
            private function get_template_html( $template_name, $attributes = null ) {
                if ( ! $attributes ) {
                    $attributes = array();
                }
            
                ob_start();
            
                do_action( 'b3_before_' . $template_name );
            
                require( 'templates/' . $template_name . '.php' );
            
                do_action( 'b3_after_' . $template_name );
            
                $html = ob_get_contents();
                ob_end_clean();
            
                return $html;
            }
        
        
        }
    
        /**
         * The main function responsible for returning the one true B3Onboarding instance to functions everywhere.
         *
         * @return \B3Onboarding
         */
        function init_b3_onboarding() {
            global $b3_onboarding;
        
            if ( ! isset( $b3_onboarding ) ) {
                $b3_onboarding = new B3Onboarding();
                $b3_onboarding->initialize();
            }
        
            return $b3_onboarding;
        }
    
        // Initialize the plugin
        init_b3_onboarding();
    
    } // class_exists check
