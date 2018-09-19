<?php if (count($generators) > 0): ?>

    <input
        id="lima-sell-licenses"
        name="lima-sell-licenses"
        type="checkbox"
        <?=$licensed ? 'checked="checked"' : '';?>
    >
    <label for="lima-sell-licenses"><?=__('I wish to sell license keys for this product.' ,'lima');?></label>

    <?php if(!$licensed): ?>
        <p><?=__('<b class="text-danger">Important:</b> Even if you have active licenses and a generator, if this is not ticked <b>no licenses will be sold!</b>', 'lima');?></p>
    <?php endif; ?>

    <p><?=sprintf(
        __('You currently have <b class="text-primary">%d</b> available license key(s) ready for sale and <b class="text-dark">%d</b> inactive license key(s)', 'lima'),
        count($license_keys['available']),
        count($license_keys['inactive'])
    ); ?></p>

    <div id="lima-generator-select">
        <p><?=__('Please choose a generator which will be used to create license keys for this product.', 'lima');?></p>
        <select id="lima-generator" name="lima-generator">
            <option value="0"><?=__('Choose a generator...', 'lima');?></option>
            <?php foreach ($generators as $generator): ?>
                <option
                    value="<?=$generator->id;?>"
                    <?=($gen_id == $generator->id) ? 'selected="selected"' : '';?>
                ><?=$generator->name;?></option>
            <?php endforeach; ?>
        </select>
    </div>

<?php else: ?>



<?php endif; ?>
