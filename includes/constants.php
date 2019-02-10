<?php
    
    if ( ! defined( 'B3_REGISTER' ) ) {
        
        $page = get_option( 'b3_register_page_id' );
        if ( false != $page && get_post( $page ) ) {
            if ( class_exists( 'Sitepress' ) ) {
                define( 'B3_REGISTER', apply_filters( 'wpml_object_id', $page, 'page', true ) );
            } else {
                define( 'B3_REGISTER', $page );
            }
    
        }
    }
    
    if ( ! defined( 'B3_LOGIN' ) ) {
        
        $page = get_option( 'b3_login_page_id' );
        if ( false != $page && get_post( $page ) ) {
            if ( class_exists( 'Sitepress' ) ) {
                define( 'B3_LOGIN', apply_filters( 'wpml_object_id', $page, 'page', true ) );
            } else {
                define( 'B3_LOGIN', $page );
            }
        }
    }
    
    if ( ! defined( 'B3_FORGOTPASS' ) ) {
        
        $page = get_option( 'b3_forgotpass_page_id' );
        if ( false != $page && get_post( $page ) ) {
            if ( class_exists( 'Sitepress' ) ) {
                define( 'B3_FORGOTPASS', apply_filters( 'wpml_object_id', $page, 'page', true ) );
            } else {
                define( 'B3_FORGOTPASS', $page );
            }
        }
    }
    
    if ( ! defined( 'B3_RESETPASS' ) ) {
        
        $page = get_option( 'b3_resetpass_page_id' );
        if ( false != $page && get_post( $page ) ) {
            if ( class_exists( 'Sitepress' ) ) {
                define( 'B3_RESETPASS', apply_filters( 'wpml_object_id', $page, 'page', true ) );
            } else {
                define( 'B3_RESETPASS', $page );
            }
        }
    }
