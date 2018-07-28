<?php

/* @var $this yii\web\View */

$this->title = 'Scraper Result';
$this->registerCssFile("@web/css/ekko-lightbox.css", [
    'depends' => [\yii\bootstrap\BootstrapAsset::className()],
    'media' => 'print',
], 'css-print-theme');
$this->registerJsFile(
    '@web/js/ekko-lightbox.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJsFile(
    '@web/js/main.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>

<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-12">
                <h2>Matched Images</h2>
                <?php 
                if (count($matchImages) > 0):
                    foreach($matchImages as $matchImage): 
                        foreach($matchImage as $image):
                    ?>
                        <div class="col-lg-4">
                            <a href="<?php echo $image['src']; ?>" data-toggle="lightbox">
                                <!--<img src="<?php //echo $image['src']; ?>" class="img-fluid" alt="<?php //echo $image['alt']; ?>" /> -->
                                <?php echo $image['src']; ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach;  ?>
                <?php else: ?>
                    <p>--No Matched Images--</p>
                <?php endif; ?>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-lg-12">
                <h2>Possible Matches</h2>
                <?php 
                if (count($matchTags) > 0):
                foreach($matchTags as $matchTag): 
                    foreach($matchTag as $image):
                    ?>
                    <div class="col-lg-4">
                        <a href="<?php echo $image['src']; ?>" data-toggle="lightbox">
                            <!--<img src="<?php //echo $image['src']; ?>" class="img-fluid" alt="<?php //echo $image['alt']; ?>" /> -->
                            <?php echo $image['src']; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach;  ?>
                <?php else: ?>
                    <p>--No Possible Matches--</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

