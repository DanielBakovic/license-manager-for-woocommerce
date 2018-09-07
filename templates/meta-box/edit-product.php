<?php if (count($generators) > 0): ?>

    <input
        id="lima-sell-licenses"
        name="lima-sell-licenses"
        type="checkbox"
        <?=$gen_id ? 'checked="checked"' : '';?>
    >
    <label for="lima-sell-licenses"><?=__('I wish to sell license keys for this product.' ,'lima');?></label>

    <div id="lima-generator-select">
        <p><?=__('Please choose a generator which will be used to create license keys for this product.', 'lima');?></p>
        <select id="lima-generator" name="lima-generator">
            <option value=""><?=__('Choose a generator...', 'lima');?></option>
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
