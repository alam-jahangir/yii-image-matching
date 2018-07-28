<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
use yii\widgets\ActiveForm;
?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
<div class="site-index">
    <div class="body-content">
        
        <div class="row">
            <div class="col-lg-12">
                <label>Upload Image</label>
                <input type="file" class="form-control" name="file" />
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-lg-12">
                <label>URLs</label><br />
                <input type="text" class="form-control" name="urls[]" />
                <div id="more_urls"></div>
                <br />
                <button type="button" onclick="addMoreUrl()">Add More URL</button>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-lg-12">
                <label>Search Tags</label><br />
                <input type="text" class="form-control" name="tags[]" />
                <div id="more_tags"></div>
                <br />
                <button type="button" onclick="addSearchTags()">Add More Tag</button>
            </div>
        </div>
        <br />
        <div class="row">
            <button class="btn btn-primary">Submit</button>
        </div>

    </div>
</div>

<script type="text/javascript">

    function addMoreUrl() {
        $('#more_urls').append('<div><input class="form-control" type="text" name="urls[]" /><a href="javascript:void(0)" onclick="$(this).parent().remove()">Remove</a></div>');

    }

    function addSearchTags() {
        $('#more_tags').append('<div><input class="form-control" type="text" name="tags[]" /><a href="javascript:void(0)" onclick="$(this).parent().remove()">Remove</a></div>');
    }

</script>

<?php ActiveForm::end() ?>
