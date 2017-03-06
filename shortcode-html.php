<?php
$class = ['socialsharing', 'socialsharing--'.$method];

if ($show_icons) {
    $class[] = 'socialsharing--icos';
}
else {
    $class[] = 'socialsharing--noicos';
}
if ($show_icons && $icon_size) {
    $class[] = 'socialsharing--icosize-'.$icon_size;
}
if ($show_labels) {
    $class[] = 'socialsharing--labels';
}
else {
    $class[] = 'socialsharing--nolabels';
}
if ($show_labels && $label_position) {
    $class[] = 'socialsharing--labelposition-'.$label_position;
}
if ($show_sharing_count && $sharing_count) {
    $class[] = 'socialsharing--sharingcount';
}
if ($title) {
    $class[] = 'socialsharing--hastitle';
}
// Definēts platums. Visas pogas aizņems pieejamo platumu
if ($width) {
    if ($width != 'auto') {
        $class[] = 'socialsharing--fullwidth';
    }
}

// Share pogu skaits
$share_items_count = count($icons);

$share_item_class = [
    'socialsharing__share-w',
    'socialsharing__share-w--width-'.round(100 / $share_items_count)
];


$link_attributes = [];
foreach ($icons as $icon) {
    $link_attributes[$icon] = '';
}

$link_labels = [
    'facebook' => 'Facebook',
    'draugiem' => 'Draugiem',
    'twitter' => 'Twitter',
    'email' => 'E-pasts',
    'whatsapp' => 'WhatsApp'
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
    
    <div class="socialsharing__count" data-count="<?php echo $sharing_count ?>">
        <span class="socialsharing__value"><?php echo $sharing_count ?></span>
    </div>

    <div class="socialsharing__shares">
        
        <?php foreach ($icons as $icon): ?>
        <div class="<?php echo implode(' ', $share_item_class) ?>">
            <a 
                class="socialsharing__share socialsharing__<?php echo $icon ?>" 
                <?php echo $link_attributes[$icon] ?>
                data-type="<?php echo $icon ?>"
                >
                <span class="socialsharing__ico">
                    <?php SocialIcons::{$icon}([
                        'class' => "socialsharing__ico-img"
                    ]) ?>
                </span><span class="socialsharing__label">
                    <?php echo $link_labels[$icon] ?>
                </span>
            </a>
        </div>
        <?php endforeach ?>
    
    </div>

</div>