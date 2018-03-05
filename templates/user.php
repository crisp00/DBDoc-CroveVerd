<?php

?>

<div class="user_pane">
    <h1>Welcome <?php echo $user->username ?></h1>
    <h2>Current Category: <?php echo $dbdoc->getRootCategory()->name ?></h2>
    <?php
        $cats = $dbdoc->getCategories($dbdoc->getRootCategory());
        foreach($cats as $cat){
            echo $cat->toString() . "<br>";
        }
    ?>
    <a href="./?logout"><button>Log out</button></a>
</div>