<div id="manage-sliders">

<form action="<?php echo admin_url('admin.php?page=slider-api'); ?>" method="post">
<input type="hidden" name="add-slider" value="<?php echo wp_create_nonce(self::nonce_key); ?>" />
<div class="input-append">
  <input class="span3" id="slider-name" name="slider-name" placeholder="Slider Name" type="text">
  <button class="btn" type="submit"> Add New Slider API !</button>
</div>
</form>

<h3>Manage Slider APIs</h3>

<div class="tabbable tabs-left">
    <ul id="sliders-tab" class="nav nav-tabs"></ul>
    <div class="tab-content">
<?php foreach(get_sliders_array() as $id => $name): ?>
    <div class="sliders tab-pane well" id="<?php echo esc_attr($id); ?>" style="overflow: auto;">
    <h4 class="slider-label"><?php echo esc_html($name); ?></h4>
    <h5>API Endpoint</h5>
    <?php if (get_option('permalink_structure')): ?>
    <p><code><?php echo home_url(SLIDER_API_ENDPOINT); ?>/<?php echo $id; ?>/</code></p>
    <?php else: ?>
    <p><code><?php echo home_url(); ?>/?<?php echo SLIDER_API_ENDPOINT; ?>=<?php echo $id; ?></code></p>
    <?php endif; ?>
    <p>or you can paste the code in functions.php like below.</p>
    <pre>add_action('wp_footer', 'my_sliders');
function my_sliders(){
    echo '&lt;script type="text/javascript"&gt;';
    echo "var slider_apis = ".get_slider_api_endpoints().";";
    echo '&lt;/script&gt;';
}</pre>
    <h5>Default Image Size</h5>
    </div>
<?php endforeach; ?>
    </div>
</div>

</div><!-- #manage-sliders -->


