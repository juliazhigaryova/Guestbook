<?php
use classes\base\helpers\Html;

/** @var $this \classes\controllers\MainController */

/** @var \classes\models\Comments $model */
$model = $this->params['model'];
?>
<h2><?= ($model->isNewRecord || !isset($_GET['id'])) ? 'Добавить новое сообщение' : 'Редактирование сообщения. №' . Html::encode($model->id) ?></h2>
<form action="/main/<?= ($model->isNewRecord || !isset($_GET['id'])) ? 'addmessage' : 'editmessage?id='.Html::encode($model->id); ?>" method="post" class="form-message">
    <p class="form-message__group">
        <label for="text"
               class="form-message__label<?= !empty($model->getErrors('text')) ? ' error-field_red' : ''; ?>">*Ваше
            сообщение:</label>
        <br>
        <textarea name="Messages[text]" minlength="1" cols="60" rows="5"
                  id="text" class="form-message__field"><?= (!empty($model->text)) ? Html::encode($model->text) : '' ?></textarea></p>
    <?php if (!empty($model->getErrors('text'))): ?>
        <p class="error-field error-field_red"><?= Html::encode($model->getErrors('text')[0]); ?></p>
    <?php endif; ?>

    <p><label for="name" class="form-message__label<?= !empty($model->getErrors('name')) ? ' error-field_red' : ''; ?>">*Ваше
            имя:</label> <br><input type="text" class="form-message__field" name="Messages[name]" id="name" minlength="2" maxlength="30"
                                    value="<?= (!empty($model->name)) ? Html::encode($model->name) : '' ?>"></p>
    <?php if (!empty($model->getErrors('name'))): ?>
        <p class="error-field error-field_red"><?= Html::encode($model->getErrors('name')[0]); ?></p>
    <?php endif; ?>
    <p><input type="submit" value="<?= ($model->isNewRecord) ? 'Добавить сообщение' : 'Сохранить' ?>" class="form-message__btn"></p>
</form>
