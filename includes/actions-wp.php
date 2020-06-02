<?php
    /*
     * This file contains functions hooked to the Wordpress' hooks
     */

    /**
     * Add custom fields to Wordpress' default register form
     */
    function b3_add_registration_fields() {

        // Get and set any values already sent
        $activate_first_last = get_option( 'b3_activate_first_last', false );
        $first_last_required = get_option( 'b3_first_last_required', false );
        $first_name          = ( isset( $_POST[ 'first_name' ] ) ) ? $_POST[ 'first_name' ] : '';
        $last_name           = ( isset( $_POST[ 'last_name' ] ) ) ? $_POST[ 'last_name' ] : '';
        $recaptcha           = get_option( 'b3_recaptcha', false );
        $recaptcha_public    = get_option( 'b3_recaptcha_public', false );
        $privacy_checkbox    = get_option( 'b3_privacy', false );
        $privacy_page        = get_option( 'b3_privacy_page', false );
        $privacy_page_wp     = get_option( 'wp_page_for_privacy_policy' );
        if ( false == $privacy_page && false != $privacy_page_wp ) {
            $privacy_page = get_permalink( $privacy_page_wp );
        }

        if ( true == $activate_first_last ) {
        ?>
        <p>
            <label for="first_name"><?php _e( 'First name', 'b3-onboarding' ) ?> <?php if ( 1 == $first_last_required ) { ?>(<?php esc_html_e( 'required', 'b3-onboarding' ); ?>)<?php }?>
            <br />
            <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr( stripslashes( $first_name ) ); ?>" size="25" /></label>
        </p>

        <p>
            <label for="last_name"><?php _e( 'Last name', 'b3-onboarding' ) ?> <?php if ( 1 == $first_last_required ) { ?>(<?php esc_html_e( 'required', 'b3-onboarding' ); ?>)<?php }?>
            <br />
            <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr( stripslashes( $last_name ) ); ?>" size="25" /></label>
        </p>
        <?php } ?>

        <?php if ( true == $recaptcha ) { ?>
            <?php do_action( 'b3_before_recaptcha_register' ); ?>
            <div class="recaptcha-container">
                <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_public; ?>"></div>
            </div>
            <p></p>
            <?php do_action( 'b3_after_recaptcha_regsiter' ); ?>
        <?php } ?>

        <?php if ( true == $privacy_checkbox ) { ?>
            <p>
                <label>
                    <input name="accept_privacy" type="checkbox" id="accept_privacy" value="1">
                    <?php if ( false != get_option( 'b3_privacy_text', false ) ) { ?>
                        <?php echo get_option( 'b3_privacy_text' ); ?>
                    <?php } else { ?>
                        <?php esc_html_e( 'Accept privacy settings', 'b3-onboarding' ); ?>
                        <?php if ( true == $privacy_page ) { ?>
                            <?php echo '&nbsp;-&nbsp;'; ?>
                            <?php echo sprintf( __( '<a href="%s">Click here</a> for more info.', 'b3-onboarding' ), esc_url( $privacy_page ) ); ?>
                        <?php } ?>
                    <?php } ?>
                </label>
            </p>
            <br class="clear">
        <?php } ?>
    <?php
    }
    add_action( 'register_form', 'b3_add_registration_fields' );

    /**
     * Update usermeta after register
     *
     * @param $user_id
     */
    function b3_update_user_register_fields( $user_id ) {
        if ( ! empty( $_POST[ 'first_name' ] ) ) {
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST[ 'first_name' ] ) );
        }
        if ( ! empty( $_POST[ 'last_name' ] ) ) {
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST[ 'last_name' ] ) );
        }
    }
    add_action( 'user_register', 'b3_update_user_register_fields' );


    /**
     * Add recaptcha to login form
     *
     * @param $user_id
     */
    function b3_add_login_form_fields() {
        $recaptcha_public = get_option( 'b3_recaptcha_public', false );
        $show_recaptcha   = get_option( 'b3_recaptcha_login', false );
        if ( $show_recaptcha && $recaptcha_public ) {
            if ( function_exists( 'b3_add_captcha_registration' ) ) { b3_add_captcha_registration( $recaptcha_public ); }
        }
    }
    add_action( 'login_form', 'b3_add_login_form_fields' );


    /**
     * Do stuff just before deleting the user (and userdata is still available)
     *
     * @param $user_id
     */
    function b3_new_user_rejected( $user_id ) {
        if ( false == get_option( 'b3_disable_delete_user_email', false ) ) {
            $user_object = get_userdata( $user_id );
            $from_name   = get_option( 'blogname' ); // @TODO: add filter for sender name
            $from_email  = get_option( 'admin_email' ); // @TODO: add filter for sender email
            $message     = apply_filters( 'b3_get_account_rejected_message', b3_get_account_rejected_message() );
            $subject     = apply_filters( 'b3_account_rejected_subject', b3_get_account_rejected_subject() );
            $to          = $user_object->user_email;

            if ( in_array( 'b3_approval', $user_object->roles ) || in_array( 'b3_activation', $user_object->roles ) ) {
                $message = b3_replace_template_styling( $message );
                $message = strtr( $message, b3_replace_email_vars( [] ) );
                $message = htmlspecialchars_decode( stripslashes( $message ) );
                $headers = array(
                    'From: ' . $from_name . ' <' . $from_email . '>',
                    'Content-Type: text/html; charset=UTF-8',
                );
                wp_mail( $to, $subject, $message, $headers );
            } elseif ( ! in_array( 'b3_approval', $user_object->roles ) && ! in_array( 'b3_activation', $user_object->roles ) && false == get_option( 'b3_account_rejected_message', false ) ) {
                // $subject = str_replace( 'rejected', 'deleted', $subject );
                // $message = str_replace( 'request ', '', $message );
                // $message = str_replace( 'rejected', 'deleted', $message );
            }
        }
    }
    add_action( 'delete_user', 'b3_new_user_rejected' );