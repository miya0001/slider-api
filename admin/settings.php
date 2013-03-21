<div id="settings">

<div class="well" style="background-color: #ffffff; margin-top: 3em; overflow: auto;">
<h3 style="margin-top: 0;">Delete Slider API</h3>
<ul style="margin-top: 2em;">
<?php foreach(get_option(self::option_sliders) as $id => $name): ?>
<li><label class="radio"><input type="radio" name="delete-slider" value="<?php echo $id; ?>" /> <?php echo esc_html($name); ?></label></li>
<?php endforeach; ?>
</ul>

<form id="delete-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
    <input type="hidden" id="delete-id" name="id" value="" />
    <input type="hidden" name="delete-slider" value="<?php echo wp_create_nonce(self::nonce_key); ?>" />
    <p><button type="submit" disabled="disabled" class="btn btn-danger btn-large pull-right">Delete Slider</button></p>
</form>
</div>




</div><!-- #settings -->

