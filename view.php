<?php

$actions = apply_filters('aab-actions', []);
if (empty($actions)) {
    return;
}

?>
<div class="aab-wrapper">
    <div>
        hi <?= wp_get_current_user()->display_name ?>
    </div>

    <?php foreach ($actions as $action) : ?>
        <span class="aab-action">
            <?= $action ?>
        </span>
    <?php endforeach; ?>
</div>