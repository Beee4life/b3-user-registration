<?php

    // Admin settings
    function b3_admin_form_handling() {

        if ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] ) {
            if ( isset( $_POST[ 'b3_settings_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=settings' );

                if ( ! wp_verify_nonce( $_POST[ "b3_settings_nonce" ], 'b3-settings-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    // Custom passwords (not used yet)
                    if ( isset( $_POST[ 'b3_activate_custom_passwords' ] ) ) {
                        update_option( 'b3_custom_passwords', '1', true );
                    } else {
                        delete_option( 'b3_custom_passwords' );
                    }

                    // Custom emails
                    if ( isset( $_POST[ 'b3_activate_custom_emails' ] ) ) {
                        update_option( 'b3_custom_emails', 1, true );
                    } else {
                        update_option( 'b3_custom_emails', 0, true );
                    }

                    // Custom login page
                    if ( isset( $_POST[ 'b3_custom_login_page' ] ) ) {
                        update_option( 'b3_custom_login_page', 1, true );
                    } else {
                        update_option( 'b3_custom_login_page', 0, true );
                    }

                    // Custom login page
                    if ( isset( $_POST[ 'b3_force_custom_login_page' ] ) ) {
                        update_option( 'b3_force_custom_login_page', 1, true );
                    } else {
                        update_option( 'b3_force_custom_login_page', 0, true );
                    }

                    // Sidebar widget
                    if ( isset( $_POST[ 'b3_activate_sidebar_widget' ] ) ) {
                        update_option( 'b3_sidebar_widget', 1, true );
                    } else {
                        update_option( 'b3_sidebar_widget', 0, true );
                    }

                    // Sidebar widget
                    if ( isset( $_POST[ 'b3_disable_action_links' ] ) ) {
                        update_option( 'b3_disable_action_links', 1, true );
                    } else {
                        update_option( 'b3_disable_action_links', 0, true );
                    }

                    // Dashboard widget (not in use yet)
                    if ( isset( $_POST[ 'b3_activate_dashboard_widget' ] ) ) {
                        update_option( 'b3_dashboard_widget', 1, true );
                    } else {
                        update_option( 'b3_dashboard_widget', 0, true );
                    }

                    $redirect_url = add_query_arg( 'success', 'settings_saved', $redirect_url );

                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_pages_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=pages' );
                if ( ! wp_verify_nonce( $_POST[ "b3_pages_nonce" ], 'b3-pages-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    $page_ids = [
                        'b3_account_page_id',
                        'b3_forgotpass_page_id',
                        'b3_login_page_id',
                        'b3_logout_page_id',
                        'b3_register_page_id',
                        'b3_resetpass_page_id',
                    ];
                    if ( isset( $_POST[ 'b3_approval_page_id' ] ) ) {
                        $page_ids[] = 'b3_approval_page_id';
                    }
                    foreach( $page_ids as $page ) {
                        $old_id = get_option( $page );
                        update_option( $page, $_POST[ $page ], true );
                        delete_post_meta( $old_id, '_b3_page' );
                        update_post_meta( $_POST[ $page ], '_b3_page', true );
                    }

                    $redirect_url = add_query_arg( 'success', 'pages_saved', $redirect_url );
                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_registration_nonce' ] ) ) {
                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=registration' );
                if ( ! wp_verify_nonce( $_POST[ "b3_registration_nonce" ], 'b3-registration-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    // Registration options
                    if ( isset( $_POST[ 'b3_registration_type' ] ) ) {
                        if ( is_multisite() ) {
                            if ( is_main_site() ) {
                                $ms_registration_type = $_POST[ 'b3_registration_type' ];
                                if ( 'closed' == $ms_registration_type ) {
                                    $registration_type = 'none';
                                    update_option( 'b3_registration_type', $ms_registration_type );
                                } elseif ( 'request_access_subdomain' == $ms_registration_type ) {
                                    // not in use (yet)
                                    $registration_type = '';
                                    update_option( 'b3_registration_type', $ms_registration_type );
                                } elseif ( 'ms_loggedin_register' == $ms_registration_type ) {
                                    $registration_type = 'blog';
                                    update_option( 'b3_registration_type', $ms_registration_type );
                                } elseif ( 'ms_register_user' == $ms_registration_type ) {
                                    $registration_type = 'user';
                                    update_option( 'b3_registration_type', $ms_registration_type );
                                } elseif ( 'ms_register_site_user' == $ms_registration_type ) {
                                    $registration_type = 'all';
                                    update_option( 'b3_registration_type', $ms_registration_type );
                                }
                                update_site_option( 'registration', $registration_type );
                            }
                        } else {
                            if ( 'closed' == $_POST[ 'b3_registration_type' ] ) {
                                update_option( 'users_can_register', 0, true );
                            } else {
                                update_option( 'users_can_register', 1, true );
                            }
                            update_option( 'b3_registration_type', $_POST[ 'b3_registration_type' ], true );
                        }
                    }

                    // First/last name
                    if ( isset( $_POST[ 'b3_activate_first_last' ] ) ) {
                        update_option( 'b3_activate_first_last', $_POST[ 'b3_activate_first_last' ], true );
                    } else {
                        delete_option( 'b3_activate_first_last' );
                        delete_option( 'b3_first_last_required' );
                    }

                    // First/last name
                    if ( isset( $_POST[ 'b3_first_last_required' ] ) ) {
                        update_option( 'b3_first_last_required', $_POST[ 'b3_first_last_required' ], true );
                    } else {
                        delete_option( 'b3_first_last_required' );
                    }

                    // reCAPTCHA (not in use yet)
                    if ( isset( $_POST[ 'b3_activate_recaptcha' ] ) ) {
                        update_option( 'b3_recaptcha', 1, true );
                    } else {
                        update_option( 'b3_recaptcha', 0, true );
                    }

                    // Privacy (not in use yet)
                    if ( isset( $_POST[ 'b3_activate_privacy' ] ) ) {
                        update_option( 'b3_privacy', 1, true );
                    } else {
                        update_option( 'b3_privacy', 0, true );
                    }

                    $redirect_url = add_query_arg( 'success', 'registration_settings_saved', $redirect_url );

                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_loginpage_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=loginpage' );
                if ( ! wp_verify_nonce( $_POST[ "b3_loginpage_nonce" ], 'b3-loginpage-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    if ( ! empty( $_POST[ 'b3_loginpage_bg_color' ] ) ) {
                        $color = $_POST[ 'b3_loginpage_bg_color' ];
                        if ( '#' == substr( $_POST[ 'b3_loginpage_bg_color' ], 0, 1 ) ) {
                            $color = substr( $_POST[ 'b3_loginpage_bg_color' ], 1 );
                        }
                        $length = strlen($color);
                        if ( 3 != $length && 6 != $length ) {
                            $redirect_url = add_query_arg( 'errors', 'wrong_hexlength', $redirect_url );
                            wp_redirect( $redirect_url );
                            exit;
                        } else {
                            update_option( 'b3_loginpage_bg_color', $color );
                        }
                    } else {
                        delete_option( 'b3_loginpage_bg_color' );
                    }

                    update_option( 'b3_loginpage_font_family', $_POST[ 'b3_loginpage_font_family' ] );
                    update_option( 'b3_loginpage_font_size', $_POST[ 'b3_loginpage_font_size' ] );
                    update_option( 'b3_loginpage_logo', $_POST[ 'b3_loginpage_logo' ] );

                    $max_width  = 320;
                    $max_height = 150;
                    if ( $_POST[ 'b3_loginpage_logo_width' ] >= $max_width ) {
                        update_option( 'b3_loginpage_logo_width', $max_width );
                    } else {
                        update_option( 'b3_loginpage_logo_width', $_POST[ 'b3_loginpage_logo_width' ] );
                    }
                    if ( $_POST[ 'b3_loginpage_logo_height' ] >= $max_height ) {
                        update_option( 'b3_loginpage_logo_height', $max_height );
                    } else {
                        update_option( 'b3_loginpage_logo_height', $_POST[ 'b3_loginpage_logo_height' ] );
                    }

                    $redirect_url = add_query_arg( 'success', 'loginpage_saved', $redirect_url );
                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_emails_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=emails' );

                if ( ! wp_verify_nonce( $_POST[ "b3_emails_nonce" ], 'b3-emails-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    if ( isset( $_POST[ 'b3_email_styling' ] ) ) {
                        update_option( 'b3_email_styling', $_POST[ 'b3_email_styling' ], true );
                    }
                    if ( isset( $_POST[ 'b3_email_template' ] ) ) {
                        update_option( 'b3_email_template', stripslashes( $_POST[ 'b3_email_template' ] ), true );
                    }

                    update_option( 'b3_forgot_password_message', stripslashes( $_POST[ 'b3_forgot_password_message' ] ), true );
                    update_option( 'b3_forgot_password_subject', $_POST[ 'b3_forgot_password_subject' ], true );
                    update_option( 'b3_notification_sender_email', $_POST[ 'b3_notification_sender_email' ], true );
                    update_option( 'b3_notification_sender_name', $_POST[ 'b3_notification_sender_name' ], true );

                    if ( in_array( get_option( 'b3_registration_type' ), [ 'open', 'email_activation' ] ) ) {
                        update_option( 'b3_account_activated_subject', $_POST[ 'b3_account_activated_subject' ], true );
                        update_option( 'b3_account_activated_message', stripslashes( $_POST[ 'b3_account_activated_message' ] ), true );

                        update_option( 'b3_new_user_message', stripslashes( $_POST[ 'b3_new_user_message' ] ), true );
                        update_option( 'b3_new_user_notification_addresses', $_POST[ 'b3_new_user_notification_addresses' ], true );
                        update_option( 'b3_new_user_subject', $_POST[ 'b3_new_user_subject' ], true );
                        update_option( 'b3_welcome_user_message', stripslashes( $_POST[ 'b3_welcome_user_message' ] ), true );
                        update_option( 'b3_welcome_user_subject', $_POST[ 'b3_welcome_user_subject' ], true );
                    }

                    if ( in_array( get_option( 'b3_registration_type' ), [ 'email_activation' ] ) ) {
                        update_option( 'b3_email_activation_subject', stripslashes( $_POST[ 'b3_email_activation_subject' ] ), true );
                        update_option( 'b3_email_activation_message', stripslashes( $_POST[ 'b3_email_activation_message' ] ), true );
                    }

                    if ( 'request_access' == get_option( 'b3_registration_type' ) ) {
                        update_option( 'b3_account_approved_message', $_POST[ 'b3_account_approved_message' ], true );
                        update_option( 'b3_account_approved_subject', $_POST[ 'b3_account_approved_subject' ], true );
                        update_option( 'b3_request_access_message_admin', stripslashes( $_POST[ 'b3_request_access_message_admin' ] ), true );
                        update_option( 'b3_request_access_notification_addresses', $_POST[ 'b3_request_access_notification_addresses' ], true );
                        update_option( 'b3_request_access_subject_admin', $_POST[ 'b3_request_access_subject_admin' ], true );
                        update_option( 'b3_request_access_message_user', stripslashes( $_POST[ 'b3_request_access_message_user' ] ), true );
                        update_option( 'b3_request_access_subject_user', $_POST[ 'b3_request_access_subject_user' ], true );
                    }

                    $redirect_url = add_query_arg( 'success', 'emails_saved', $redirect_url );
                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_users_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=users' );

                if ( ! wp_verify_nonce( $_POST[ "b3_users_nonce" ], 'b3-users-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch' );
                } else {

                    // Front-end approval
                    if ( isset( $_POST[ 'b3_activate_frontend_approval' ] ) ) {
                        update_option( 'b3_front_end_approval', 1, true );
                    } else {
                        update_option( 'b3_front_end_approval', 0, true );
                        delete_option( 'b3_approval_page_id' );
                    }

                    // Restrict admin
                    if ( isset( $_POST[ 'b3_restrict_admin' ] ) ) {
                        update_option( 'b3_restrict_admin', $_POST[ 'b3_restrict_admin' ], true );
                    } else {
                        delete_option( 'b3_restrict_admin' );
                    }

                    $redirect_url = add_query_arg( 'success', 'user_settings_saved', $redirect_url );
                }

                wp_redirect( $redirect_url );
                exit;

            } elseif ( isset( $_POST[ 'b3_recaptcha_nonce' ] ) ) {

                $redirect_url = admin_url( 'admin.php?page=b3-onboarding&tab=integrations' );

                if ( ! wp_verify_nonce( $_POST[ "b3_recaptcha_nonce" ], 'b3-recaptcha-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    $recaptcha_public  = $_POST[ 'b3_recaptcha_public' ];
                    $recaptcha_secret  = $_POST[ 'b3_recaptcha_secret' ];
                    $recaptcha_version = $_POST[ 'b3_recaptcha_version' ];

                    // @TODO
                    if ( ! $recaptcha_public ) {
                        // error_log('no public');
                    }
                    if ( ! $recaptcha_secret ) {
                        // error_log('no secret');
                    }
                    if ( ! $recaptcha_version ) {
                        // error_log('no version');
                    }


                    update_option( 'b3_recaptcha_public', $_POST[ 'b3_recaptcha_public' ], true );
                    update_option( 'b3_recaptcha_secret', $_POST[ 'b3_recaptcha_secret' ], true );
                    update_option( 'b3_recaptcha_version', $_POST[ 'b3_recaptcha_version' ], true );

                    $redirect_url = add_query_arg( 'success', 'recaptcha_saved', $redirect_url );
                }

                wp_redirect( $redirect_url );
                exit;

            }
        }
    }
    add_action( 'admin_init', 'b3_admin_form_handling' );


    // Admin settings
    function b3_approve_deny_users() {

        if ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] ) {
            if ( isset( $_POST[ 'b3_manage_users_nonce' ] ) ) {

                if ( is_admin() ) {
                    $redirect_url = network_admin_url( 'admin.php?page=b3-user-approval' );
                } else {
                    if ( false != b3_get_user_approval_id() ) {
                        $redirect_url = get_permalink( b3_get_user_approval_id() );
                    } else {
                        $redirect_url = '';
                    }
                }

                if ( ! wp_verify_nonce( $_POST[ "b3_manage_users_nonce" ], 'b3-manage-users-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {

                    $approve   = ( isset( $_POST[ 'b3_approve_user' ] ) ) ? $_POST[ 'b3_approve_user' ] : false;
                    $reject    = ( isset( $_POST[ 'b3_reject_user' ] ) ) ? $_POST[ 'b3_reject_user' ] : false;
                    $user_id   = ( isset( $_POST[ 'b3_user_id' ] ) ) ? $_POST[ 'b3_user_id' ] : false;
                    $user_object = ( isset( $_POST[ 'b3_user_id' ] ) ) ? new WP_User( $user_id ) : false;

                    if ( false != $approve && isset( $user_object->ID ) ) {

                        // activate user
                        $user_object = new WP_User( $user_id );
                        $user_object->set_role( get_option( 'default_role' ) );

                        // send mail
                        $blog_name  = get_option( 'blogname' ); // @TODO: add filter
                        $from_email = get_option( 'admin_email' ); // @TODO: add filter
                        $to         = $user_object->user_email;
                        $subject    = esc_html__( 'Account approved', 'b3-onboarding' );
                        $subject    = apply_filters( 'b3_account_approved_subject', b3_get_account_approved_subject() );
                        $message    = sprintf( esc_html__( 'Welcome to %s. Your account has been approved and you can now set your password on %s.', 'b3-onboarding' ), $blog_name, esc_url( b3_get_forgotpass_url() ) );
                        $headers    = array(
                            'From: ' . $blog_name . ' <' . $from_email . '>',
                            'Content-Type: text/plain; charset=UTF-8',
                        );

                        wp_mail( $to, $subject, $message, $headers );

                        do_action( 'b3_new_user_activated_by_admin', $user_id );

                        $redirect_url = add_query_arg( 'user', 'approved', $redirect_url );

                    } elseif ( false != $reject && isset( $user_object->ID ) ) {

                        require_once(ABSPATH.'wp-admin/includes/user.php' );
                        // do reject user
                        if ( true == wp_delete_user( $user_id ) ) {
                            // send mail
                            $blog_name  = get_option( 'blogname' ); // @TODO: add filter
                            $from_email = get_option( 'admin_email' ); // @TODO: add filter
                            $to         = $user_object->user_email;
                            $subject    = sprintf( esc_html__( 'Account rejected for %s', 'b3-onboarding' ), $blog_name );
                            $message    = sprintf( esc_html__( "We're sorry to have to inform you, but your request for access to %s was rejected.", "b3-onboarding" ), $blog_name );
                            $headers    = array(
                                'From: ' . $blog_name . ' <' . $from_email . '>',
                                'Content-Type: text/plain; charset=UTF-8',
                            );
                            wp_mail( $to, $subject, $message, $headers );

                            do_action( 'b3_new_user_rejected', $user_id );

                            $redirect_url = add_query_arg( 'user', 'rejected', $redirect_url );
                        } else {
                            // @TODO: add error
                            // $redirect_url = add_query_arg( 'user', 'rejected', $redirect_url );
                        }

                    }
                }

                wp_redirect( $redirect_url );
                exit;
            }
        }
    }
    add_action( 'init', 'b3_approve_deny_users' );


    /**
     * Profile form handling
     */
    function b3_profile_form_handling() {

        if ( 'POST' == $_SERVER[ 'REQUEST_METHOD' ] && ! empty( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'profile' ) {
            if ( isset( $_POST[ 'b3_profile_nonce' ] ) ) {

                $redirect_url = home_url( 'account' );
                if ( ! wp_verify_nonce( $_POST[ "b3_profile_nonce" ], 'b3-profile-nonce' ) ) {
                    $redirect_url = add_query_arg( 'errors', 'nonce_mismatch', $redirect_url );
                } else {
                    global $current_user, $wp_roles;

                    require_once( ABSPATH . 'wp-admin/includes/user.php' );
                    require_once( ABSPATH . 'wp-admin/includes/misc.php' );

                    define( 'IS_PROFILE_PAGE', true );

                    $errors = edit_user( $current_user->ID );

                    if ( ! is_wp_error( $errors ) ) {
                        $args     = array( 'updated' => 'true' );
                        $redirect = add_query_arg( $args );
                        wp_redirect( $redirect );
                        exit;
                    } else {
                        error_log('error in profile form handling');
                    }

                    if ( ! empty( $_POST[ 'first_name' ] ) ) {
                        wp_update_user( array( 'ID' => $current_user->ID, 'first_name' => esc_attr( $_POST[ 'first_name' ] ) ) );
                    }
                    if ( ! empty( $_POST[ 'last_name' ] ) ) {
                        wp_update_user( array( 'ID' => $current_user->ID, 'last_name' => esc_attr( $_POST[ 'last_name' ] ) ) );
                    }
                }
            }
        }
    }
    add_action( 'init', 'b3_profile_form_handling' );

