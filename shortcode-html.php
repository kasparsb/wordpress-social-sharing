<?php
$class = ['socialsharing', 'socialsharing--'.$method];

if ($show_icons) {
    $class[] = 'socialsharing--icos';
}
if ($show_labels) {
    $class[] = 'socialsharing--labels';
}
if ($show_icons && $show_labels) {
    $class[] = 'socialsharing--icoslabels';
}
if ($show_sharing_count && $sharing_count) {
    $class[] = 'socialsharing--sharingcount';
}
if ($title) {
    $class[] = 'socialsharing--hastitle';
}
if ($size) {
    $class[] = 'socialsharing--size-'.$size;
}

$link_attributes = [
    'facebook' => '',
    'draugiem' => '',
    'twitter' => ''
];

$link_labels = [
    'facebook' => 'Facebook',
    'draugiem' => 'Draugiem',
    'twitter' => 'Twitter'
];

if (isset($links) && is_array($links)) {
    foreach ($links as $k => $values) {
        $attrs = [];

        foreach ($values as $name => $value) {
            if ($value) {
                $attrs[] = sprintf('%s="%s"', $name, $value);
            }
        }
        
        $link_attributes[$k] = implode(' ', $attrs);
    }
}

if (isset($labels) && is_array($labels)) {
    foreach ($labels as $k => $label) {
        $link_labels[$k] = $label;
    }
}
?>
<div class="<?php echo implode(' ', $class) ?>">
    <h4 class="socialsharing__heading"><?php echo $title ?></h4>
    <div class="socialsharing__item socialsharing__count" data-count="<?php echo $sharing_count ?>">
        <span class="socialsharing__value"><?php echo $sharing_count ?></span>
    </div>
    <a 
        class="socialsharing__item socialsharing__sharing socialsharing__facebook" 
        <?php echo $link_attributes['facebook'] ?>
        data-type="facebook"
        >
        <span class="socialsharing__ico">
            <?php SocialIcons::facebook([
                'class' => "socialsharing__ico-img"
            ]) ?>
        </span>
        <span class="socialsharing__label"><?php echo $link_labels['facebook'] ?></span>
    </a>
    <a 
        class="socialsharing__item socialsharing__sharing socialsharing__draugiem" 
        <?php echo $link_attributes['draugiem'] ?>
        data-type="draugiem"
        data-prefix="LA.lv">
        <span class="socialsharing__ico">
            <?php SocialIcons::draugiem([
                'class' => "socialsharing__ico-img"
            ]) ?>
        </span>
        <span class="socialsharing__label"><?php echo $link_labels['draugiem'] ?></span>
    </a>
    <a 
        class="socialsharing__item socialsharing__sharing socialsharing__twitter" 
        <?php echo $link_attributes['twitter'] ?>
        data-user="LA_lv"
        data-type="twitter">
        <span class="socialsharing__ico">
            <?php SocialIcons::twitter([
                'class' => "socialsharing__ico-img"
            ]) ?>
        </span>
        <span class="socialsharing__label"><?php echo $link_labels['twitter'] ?></span>
    </a>
    <?php if ($method == 'share'): ?>
    <a
        class="socialsharing__item socialsharing__sharing socialsharing__email" 
        data-type="email">
        <span class="socialsharing__ico">
            <?php SocialIcons::email([
                'class' => "socialsharing__ico-img"
            ]) ?>
        </span>
        <span class="socialsharing__label">E-mail</span>
    </a>
    <?php endif ?>
</div>