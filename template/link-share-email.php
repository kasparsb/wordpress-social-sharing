<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <p>
            <?php echo $name ? $name : 'Draugs' ?> sūta jums rakstu!<br />
            Sūtītāja e-pasts: <?php echo $email ?>
        </p>

        <?php if ($comment): ?>
        <p>
            Drauga komentārs:<br />
            <?php echo nl2br($comment) ?>
        </p>
        <?php endif ?>
        
        <h4><?php echo $title ?></h4>
        <p><?php echo $excerpt ?></p>

        <p>
            Lasīt rakstu <a href="<?php echo $link ?>"><?php echo $link ?></a>
        </p>
        

        <p>
            <br />
            <br />
            Jautājumu un problēmu gadījumā rakstiet: <a href="mailto:eabonesana@la.lv">eabonesana@la.lv</a><br /><br />
            Ar cieņu,<br />
            LA.lv
        </p>
    </body>
</html>