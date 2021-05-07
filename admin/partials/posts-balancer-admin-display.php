<div class="wrap">
    <h2><?php echo __('Balancer', 'posts-balancer') ?></h2>
    <form method="post">
        <h3><?php echo __('Set percentages', 'posts-balancer') ?></h3>

        <?php do_action('balancer_admin_actions')?>
        
        <div class="help notice notice-warning">
            <p>The percentage cannot be greater or less than 100%</p>
        </div>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php echo __('Editorial Percent', 'posts-balancer') ?></th>
                    <td>
                        <input type="number" max="100" name="percent" id="percent" class="small-text balancer-percent" value="<?php echo get_option('_balancer_percent_editorial') !== '' ? get_option('_balancer_percent_editorial') : '' ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('More views percent', 'posts-balancer') ?></th>
                    <td>
                        <input type="number" max="100" name="views" id="views" class="small-text balancer-percent" value="<?php echo get_option('_balancer_percent_views') !== '' ? get_option('_balancer_percent_views') : '' ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Percent user', 'posts-balancer') ?></th>
                    <td>
                        <input type="number" max="100" name="user" id="user" class="small-text balancer-percent" value="<?php echo get_option('_balancer_percent_user') !== '' ? get_option('_balancer_percent_user') : '' ?>" />
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <button type="submit" id="percent-button" class="button button-primary" disabled><?php echo __('Configure', 'posts-balancer') ?></button>
        </p>
    </form>
    <form method="post">
        <h3><?php echo __('Data source', 'posts-balancer') ?></h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php echo __('Editorial taxonomy', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_taxonomy">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_taxonomies() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_taxonomy'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Autor taxonomy', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_autor">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_taxonomies() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_autor'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Tags taxonomy', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_tags">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_taxonomies() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_tags'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Topics taxonomy', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_topics">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_taxonomies() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_topics'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Place taxonomy', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_place">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_taxonomies() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_place'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo __('Post type to balance', 'posts-balancer') ?></th>
                    <td>
                        <select name="balancer_editorial_post_type">
                            <option value=""><?php echo __('-- select --', 'posts-balancer') ?></option>
                            <?php foreach (get_post_types() as $key => $value) : ?>
                                <option value="<?php echo $value ?>" <?php selected(get_option('balancer_editorial_post_type'), $value, true) ?>><?php echo $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                </tr>
                <tr>
                   <th scope="row"><?php echo __('Days ago','posts-balancer')?></th>
                   <td>
                   <input type="number" name="balancer_editorial_days" class="small-text" value="<?php echo get_option('balancer_editorial_days') !== '' ? get_option('balancer_editorial_days') : '' ?>" />        
                   </td>             
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary"><?php echo __('Save', 'posts-balancer') ?></button>
        </p>
    </form>
</div>