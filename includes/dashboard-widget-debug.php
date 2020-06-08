<?php
    /**
     * The function which outputs the dashboard widget
     *
     * @since 1.0.0
     */
    function b3_dashboard_widget_debug_function() {
        if ( current_user_can('manage_options' ) ) {
            $preview_page = network_admin_url( 'admin.php?page=b3-onboarding&preview=', '' );
            ?>

            <div class="b3_widget--dashboard">
                <h3><?php esc_html_e( 'Email preview links', 'b3-onboarding' ); ?></h3>
                <ul>
                    <li><a href="<?php echo $preview_page; ?>account-approved"><?php esc_html_e( 'Account approved (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>account-activated"><?php esc_html_e( 'Account activated (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>account-rejected"><?php esc_html_e( 'Account rejected (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>email-activation"><?php esc_html_e( 'Email activation (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>forgotpass"><?php esc_html_e( 'Forgot pass (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>new-user-admin"><?php esc_html_e( 'New user (admin)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>request-access-admin"><?php esc_html_e( 'Request access (admin)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>request-access-user"><?php esc_html_e( 'Request access (user)', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>styling"><?php esc_html_e( 'Styling', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>template"><?php esc_html_e( 'Template', 'b3-onboarding' ); ?></a></li>
                    <li><a href="<?php echo $preview_page; ?>welcome-user"><?php esc_html_e( 'Welcome (user)', 'b3-onboarding' ); ?></a></li>
                </ul>
            </div>
        <?php }
    }
    wp_add_dashboard_widget( 'b3-dashboard-debug', 'B3 Onboarding (debug)', 'b3_dashboard_widget_debug_function' );
