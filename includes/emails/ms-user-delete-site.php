<?php
    /*
     * Input fields for 'User deletes site' email (admin)
     *
     * @since 1.0.0
     */
?>
<table class="b3_table b3_table--emails">
    <tbody>
    <tr>
        <th>
            <?php // TODO: check name ?>
            <label for="b3__input--password-changed-subject" class=""><?php esc_html_e( 'Email subject', 'b3-onboarding' ); ?></label>
        </th>
        <td>
            <?php // TODO: check name ?>
            <input class="" id="b3__input--password-changed-subject" name="b3_input_password_change_subject" type="text" value="" />
        </td>
    </tr>
    <tr>
        <th class="align-top">
            <?php // TODO: check name ?>
            <label for="b3__input--password-changed-content" class=""><?php esc_html_e( 'Email content', 'b3-onboarding' ); ?></label>
        </th>
        <td>
            <?php // TODO: check name ?>
            <textarea id="b3__input--password-changed-content" name="b3_input_password_change_content" rows="6"></textarea>
        </td>
    </tr>
    <tr>
        <th>&nbsp;</th>
        <td>
            <input class="button button-primary" type="submit" value="<?php esc_html_e( 'Save settings', 'b3-onboarding' ); ?>" />
        </td>
    </tr>
    </tbody>
</table>
