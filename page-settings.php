<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>Social sharing</h2>
    <div class="form">
        <form method="post" action="<?php echo $form_action ?>">
            <table class="form-table">
            <tbody>
                <?php foreach ($settings as $id => $s): ?>
                <tr>
                    <th colspan="2"><h3><?php echo $s['caption'] ?></h3></th>
                </tr>
                <tr>
                    <th>Link</th>
                    <td>
                        <input type="text" name="follow_link[<?php echo $id ?>]" value="<?php echo esc_attr($s['link']) ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th>Custom label</th>
                    <td>
                        <input type="text" name="follow_label[<?php echo $id ?>]" value="<?php echo esc_attr($s['label']) ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th>Open link in</th>
                    <td>
                        <select name="follow_target[<?php echo $id ?>]">
                            <option value="_blank" <?php echo $s['target'] == '_blank' ? 'selected="selected"' : ''?>>New window</option>
                            <option value="" <?php echo $s['target'] == '' ? 'selected="selected"' : ''?>>Same window</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
            </table>

            <p class="submit">
                <input type="submit" class="button button-primary button-large" value="Save" />
            </p>
        </form>
    </div>
</div>