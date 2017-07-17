<?php
use classes\base\helpers\Html;

/** @var $this \classes\controllers\MainController */

/** @var \classes\models\Comments $model */
$model = $this->params['model'];

/** @var \classes\models\Messages $modelMessage */
$modelMessage = $this->params['modelMessage'];
?>

<h2>Сообщение</h2>
<div class="message">
    <a name="message<?= Html::encode($modelMessage->id); ?>"></a>
    <div class="message__name">
        <?= Html::encode($modelMessage->name); ?>
    </div>
    <div class="message__text">
        <?= Html::encode($modelMessage->text); ?>
    </div>
    <div class="message_date">
        <?= Html::encode(date('j.m.Y H:i', $modelMessage->date)); ?>
    </div>
    <div class="comments">
        <h4>Комментарии</h4>
        <?php
        /** @var array $comments Получаем связанные с сообщением комментарии */
        $comments = $modelMessage->getCommets();
        if (count($comments) == 0): ?>
            <p>Нет комментариев для отображения</p>
        <?php else: ?>
            <?php
            /** @var \classes\models\Comments $comment */
            foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment__name">
                        <?= Html::encode($comment->name) ?>
                    </div>
                    <div class="comment__text">
                        <?= Html::encode($comment->text) ?>
                    </div>
                    <div class="comment__date">
                        <?= Html::encode(date('j.m.Y H:i', $comment->date)) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>


<h2>Форма добавления нового комментария</h2>
<form action="/main/addcomment/?id=<?= Html::encode((int) $_GET['id']) ?>" method="post">
    <p>
        <label for="text" class="form-message__label<?= !empty($model->getErrors('text')) ? ' error-field_red' : ''; ?>">*Текст комментария:</label>
        <textarea minlength="1" cols="60" rows="5" class="form-message__field" name="Comments[text]" id="text"><?= (!empty($model->text)) ? Html::encode($model->text) : '' ?></textarea></p>
    <?php if (!empty($model->getErrors('text'))): ?>
        <p class="error-field error-field_red"><?= Html::encode($model->getErrors('text')[0]); ?></p>
    <?php endif; ?>

    <p><label for="name" class="form-message__label<?= !empty($model->getErrors('text')) ? ' error-field_red' : ''; ?>">*Название комментария:</label>
        <input type="text" name="Comments[name]" class="form-message__field" id="name" value="<?= (!empty($model->name)) ? Html::encode($model->name) : '' ?>"></p>

    <?php if (!empty($model->getErrors('name'))): ?>
        <p class="error-field error-field_red"><?= Html::encode($model->getErrors('name')[0]); ?></p>
    <?php endif; ?>
    <p><input type="submit" value="Добавить комментарий" class="form-message__btn"></p>
</form>
