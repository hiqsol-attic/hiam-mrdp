<?php

use yii\helpers\Html;

/** @var \yii\web\View $this */
/** @var string $url */

$this->title = Yii::t('hiam', 'Test save referal params');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-about">

    <h5><?= $this->title ?></h5>
    <br/>

    <ul>
        <li><?= Html::a('Test', '/mrdp/test-save-params') ?></li>
    </ul>

</div>
