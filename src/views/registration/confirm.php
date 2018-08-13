<?php

/** @var boolean|string $result */

use yii\helpers\Html;

?>

<div class="error-content">
    <?php if ($result === true) : ?>
        <h3><i class="fa fa-check-circle text-green"></i> <?= Yii::t('hiam', 'Email has been confirmed!') ?></h3>
        <p class="text-center"><?= Yii::t('hiam', 'Thank you, now you can {link}.', ['link' => Html::a(Yii::t('hiam', 'return to panel'), '/site/back')]) ?></p>
    <?php else : ?>
        <h3><i class="fa fa-warning text-yellow"></i> <?= Yii::t('hiam', 'Error. Email has not been confirmed!') ?></h3>
        <p class="text-center"><?= Html::encode($result) ?></p>
    <?php endif; ?>
</div>
