<?php
/**
 * Plugin Name: SSO
 * Author: Garth Mortensen, Mike Hansen
 * Version: 0.5
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! function_exists( 'sso_check' ) ){
    function sso_check(){
        if ( ! isset( $_GET['salt'] ) || ! isset( $_GET['nonce'] ) ){
            sso_req_login();
        }
        if ( sso_check_blocked() ){
            sso_req_login();
        }
        $nonce = esc_attr( $_GET['nonce'] );
        $salt  = esc_attr( $_GET['salt'] );
        $has_epoch = preg_match('/-e(\d+)$/', $nonce, $epoch);
        $expired = ( $has_epoch && (time() - $epoch[1]) > 300 ) ? true : false;

        if ( ! empty( $_GET['user'] ) ){
            $user = esc_attr( $_GET['user'] );
        }else{
            $user = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
            if ( is_array( $user ) && is_a( $user[0], 'WP_User' ) ){
                $user = $user[0];
                $user = $user->ID;
            }else{
                $user = 0;
            }
        }
        $bounce = ! empty( $_GET['bounce'] ) ? $_GET['bounce'] : '';
        $hash   = base64_encode( hash( 'sha256', $nonce . $salt, false ) );
        $hash   = substr( $hash, 0, 64 );

        $token = get_transient( 'sso_token' );
        $from_options = false;
        if ( $token === false ) {
            $token = get_option( 'sso_token' );
            $from_options = true;
        }
        if ( ! $expired && $token == $hash ) {
            if ( is_email( $user ) ){
                $user = get_user_by( 'email', $user );
            }else{
                $user = get_user_by( 'id', (int) $user );
            }
            if ( $from_options ) {
                delete_option( 'sso_token' );
            }
            if ( is_a( $user, 'WP_User' ) ){
                wp_set_current_user( $user->ID, $user->user_login );
                wp_set_auth_cookie( $user->ID );
                do_action( 'wp_login', $user->user_login, $user );
                delete_transient( 'sso_token' );
                wp_safe_redirect( admin_url( $bounce ) );
            }else{
                sso_req_login();
            }
        }else{
            sso_add_failed_attempt();
            sso_req_login();
        }
        die();
    }
}
add_action( 'wp_ajax_nopriv_sso-check', 'sso_check' );
add_action( 'wp_ajax_sso-check', 'sso_check' );

if( ! function_exists( 'sso_req_login' ) ){
    function sso_req_login(){
        wp_safe_redirect( wp_login_url() );
    }
}

if( ! function_exists( 'sso_get_attempt_id' ) ){
    function sso_get_attempt_id(){
        return 'sso' . esc_url( $_SERVER['REMOTE_ADDR'] );
    }
}

if( ! function_exists( 'sso_add_failed_attempt' ) ){
    function sso_add_failed_attempt(){
        $attempts = get_transient( sso_get_attempt_id(), 0 );
        $attempts ++;
        set_transient( sso_get_attempt_id(), $attempts, 300 );
    }
}

if( ! function_exists( 'sso_check_blocked' ) ){
    function sso_check_blocked(){
        $attempts = get_transient( sso_get_attempt_id(), 0 );
        if ( $attempts > 4 ){
            return true;
        }
        return false;
    }
}
