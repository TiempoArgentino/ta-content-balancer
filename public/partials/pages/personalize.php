<?php get_header();
do_action( 'personalize_before');
?>
<div class="container option-container" id="localization">
    <div class="row d-flex align-items-center">
        <div class="col-12 col-md-10 mx-auto text-center mt-5">
            <h3><?php echo sprintf(__('Hi %s','posts-balancer'),wp_get_current_user()->first_name. ' ' .wp_get_current_user()->last_name)?></h3>
            <p>
                <?php echo __('We want to offer you content according to your location. Therefore, tell us where you read us from:','posts-balancer')?>
            </p>
            <div class="row">
                <div class="col-md-8 col-12 mx-auto">
                    <div id="localize-form">
                        <input type="text" name="personalize-city" class="form-control" id="personalize-city" value="<?php echo get_user_meta(wp_get_current_user()->ID,'_personalizer_location',true)?>">
                    </div>
                </div>
            </div>
            <div class="row mt-5 mb-5">
            <div class="col-md-6 button-skip">
                <button type="button" id="skip-1" class="btn btn-block btn-lg btn-secondary"><?php echo __('skip','posts-balancer')?></button>
            </div>
            <div class="col-md-6 button-next">
                <button type="button" id="next-1" data-user="<?php echo wp_get_current_user()->ID?>" class="btn btn-block btn-lg btn-success"><?php echo __('next','posts-balancer')?></button>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="container option-container" id="categories">
    
    <div class="row d-flex align-items-center">
        <div class="col-12 col-md-10 mx-auto text-center mt-5">
            <h3><?php echo __('Select the topics that interest you the most','posts-balancer')?></h3>
           
            <div class="row">
                <div class="col-12 mx-auto">
                    <div id="categories-form" class="d-flex mt-5">
                    <?php if(!empty(balancer_personalize()->get_tags())): ?>
                        <?php foreach(balancer_personalize()->get_tags() as $key => $val):?>
                            <label><input type="checkbox" name="categorie[]" <?php echo is_array(get_user_meta(wp_get_current_user()->ID,'_personalizer_topics',true)) && in_array($key,get_user_meta(wp_get_current_user()->ID,'_personalizer_topics',true)) ? 'checked="checked"' : ''?> class="categorie" value="<?php echo $key?>" /> <?php echo $val?></label>
                            <?php endforeach;?>
                    <?php endif;?>  
                    </div>
                </div>

            </div>
            <div class="row mt-5 mb-5">
            <div class="col-md-6 button-skip">
                <button type="button" id="skip-2" class="btn btn-block btn-lg btn-secondary"><?php echo __('skip','posts-balancer')?></button>
            </div>
            <div class="col-md-6 button-next">
                <button type="button" id="next-2" data-user="<?php echo wp_get_current_user()->ID?>" class="btn btn-block btn-lg btn-success"><?php echo __('next','posts-balancer')?></button>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="container option-container" id="posts-personalize">
    <div class="row d-flex align-items-center">
        <div class="col-12 col-md-10 mx-auto text-center mt-5">
            <h3><?php echo __('These are some recently published Tiempo Argentino articles.','posts-balancer')?></h3>
            <h2><?php echo __('Choose the ones that interest you the most:','posts-balancer')?></h2>
            <div class="row">
                <div class="col-12 mx-auto">
                    <div id="posts-personalize-form" class="d-flex mt-5">
                    <?php if(!empty(balancer_personalize()->get_taxonomies())):?>
                        <?php foreach(balancer_personalize()->get_taxonomies() as $key => $val):?>
                        <?php 
                            $args = [
                                'post_type' => get_option('balancer_editorial_post_type'),
                                'numberposts' => 1,
                                'post_status' => 'publish',
                                //'orderby' => 'rand',
                                'tax_query' => [
                                    'taxonomy' => get_option('balancer_editorial_taxonomy'),
                                    'field' => 'term_id',
                                    'terms' => $key
                                ]
                            ];
                            $query = get_posts($args);  
                            foreach($query as $t):
                                $authors = get_the_terms($t->{'ID'},get_option('balancer_editorial_autor'));
                                $post_author = [];
                                foreach($authors as $a){
                                    $post_author[] = $a->{'term_id'};
                                }
    
                        ?>
                        <label class="post-item">
                            <p><?php echo $val;?></p>
                            <h5><?php echo $t->{'post_title'}?></h5>
                            <input type="checkbox" data-author="<?php echo json_encode($post_author) ?>" name="ost-item[]" <?php echo is_array(get_user_meta(wp_get_current_user()->ID,'_personalizer_taxonomy',true)) && in_array($key,get_user_meta(wp_get_current_user()->ID,'_personalizer_taxonomy',true)) ? 'checked="checked"' : ''?> class="post-item" value="<?php echo $key?>" />
                        </label>
                    <?php 
                        endforeach;
                    endforeach;
                    ?>
                    <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="row mt-5 mb-5">
            <div class="col-md-6 button-skip">
                <button type="button" id="skip-3" class="btn btn-block btn-lg btn-secondary"><?php echo __('skip','posts-balancer')?></button>
            </div>
            <div class="col-md-6 button-next">
                <button type="button" id="next-3" data-user="<?php echo wp_get_current_user()->ID?>" class="btn btn-block btn-lg btn-success"><?php echo __('next','posts-balancer')?></button>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="container option-container" id="emotions">
    <div class="row d-flex align-items-center">
        <div class="col-12 col-md-10 mx-auto text-center mt-5">
            <h3><?php echo __('Which of these photos excite you?','posts-balancer')?></h3>
           
            <div class="row">
                <div class="col-12 mx-auto">
                    <div id="emotions-form" class="d-flex mt-5 ">
                    <?php if(!empty(balancer_personalize()->get_authors())):?>
                        <?php foreach(balancer_personalize()->get_authors() as $key => $val):?>
                        <?php 
                            $args = [
                                'post_type' => get_option('balancer_editorial_post_type'),
                                'numberposts' => 1,
                                'post_status' => 'publish',
                                'orderby' => 'rand',
                                'tax_query' => [
                                    'taxonomy' => get_option('balancer_editorial_autor'),
                                    'field' => 'term_id',
                                    'terms' => $key
                                ]
                            ];
                            $query = get_posts($args);  
                             foreach($query as $t):
                                $taxos = get_the_terms($t->{'ID'},get_option('balancer_editorial_taxonomy'));
                                $post_tax = [];
                                foreach($taxos as $ta){
                                    $post_tax[] = $ta->{'term_id'};
                                }
    
                        ?>
                        <label class="image-item">
                            <img src="<?php echo get_the_post_thumbnail_url($t->{'ID'})?>" class="img-fluid" />
                            <input type="checkbox" data-taxo="<?php echo json_encode($post_tax) ?>" name="photo-item[]" <?php echo is_array(get_user_meta(wp_get_current_user()->ID,'_personalizer_authors',true)) && in_array($key,get_user_meta(wp_get_current_user()->ID,'_personalizer_authors',true)) ? 'checked="checked"' : ''?> class="photo" value="<?php echo $key?>" />
                        </label>
                        <?php endforeach;
                        endforeach?>
                    <?php endif;?>
                    </div>
                </div>
            </div>
            <div class="row mt-5 mb-5">
            <div class="col-md-6 button-skip">
                <button type="button" id="skip-4" class="btn btn-block btn-lg btn-secondary"><?php echo __('skip','posts-balancer')?></button>
            </div>
            <div class="col-md-6 button-next">
                <button type="button" id="next-4" data-user="<?php echo wp_get_current_user()->ID?>" class="btn btn-block btn-lg btn-success"><?php echo __('next','posts-balancer')?></button>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="container" id="thankyou">
    <div class="row d-flex align-items-center">
        <div class="col-12 col-md-10 mx-auto text-center mt-5">
            <h3><?php echo sprintf(__('Thank you %s','posts-balancer'),wp_get_current_user()->first_name. ' ' .wp_get_current_user()->last_name)?></h3>
            <p>
                <?php echo __('We are working so that you enjoy a unique informative experience','posts-balancer')?>
            </p>
            <div class="row">
                <div class="col-md-8 col-12 mx-auto">
                    <a href=""><?php echo __('Go to site','posts-balancer')?></a>
                </div>
            </div>
            <h4><?php echo __('You can edit your preferences, from your user profile, whenever you want.','posts-balancer')?></h4>
        </div>
    </div>
</div>
<?php 
do_action( 'personalize_after');
get_footer()?>