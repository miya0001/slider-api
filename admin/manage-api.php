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
<?php foreach(get_option(self::option_sliders, array()) as $id => $name): ?>
    <div class="sliders tab-pane well" id="<?php echo esc_attr($id); ?>" style="overflow: auto;">
    <h4 class="slider-label"><?php echo esc_html($name); ?></h4>
    </div>
<?php endforeach; ?>
    </div>
</div>

</div><!-- #manage-sliders -->


