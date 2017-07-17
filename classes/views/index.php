<?php
use classes\base\helpers\Html;

/** @var \classes\controllers\MainController $this */

/** @var array $messagesModels */
$messagesModels = $this->params['messagesModels'];
?>
    <h2>Лента сообщений</h2>
<?php
if (empty($messagesModels)):
    ?>
    <p>В книге нет ни одного сообщения. Добавьте новое сообщение.</p>
<?php endif; ?>
<?php
/** @var \classes\models\Messages $messageModel */
foreach ($messagesModels as $messageModel): ?>
    <div class="message">
        <a name="message<?= Html::encode($messageModel->id); ?>"></a>
        <div class="message__name">
            <?= Html::encode($messageModel->name); ?>
        </div>
        <div class="message__link">
            <a href="/main/editmessage?id=<?= Html::encode($messageModel->id); ?>">Редактировать</a> |
            <a href="/main/deletemessage?id=<?= Html::encode($messageModel->id); ?>">Удалить</a>
        </div>
        <div class="message__text">
            <?= Html::encode($messageModel->text); ?>
        </div>
        <div class="message_date">
            <?= Html::encode(date('j.m.Y H:i', $messageModel->date)); ?>
        </div>
        <div class="comments">
            <h4>Комментарии (<a class="link" href="/main/addcomment/?id=<?= Html::encode($messageModel->id); ?>">добавить
                    комментарий</a>)</h4>
            <?php
            /** @var array $comments Получаем связанные с сообщением комментарии */
            $comments = $messageModel->getCommets();
            if (count($comments) == 0): ?>
                <p>Нет комментариев для отображения</p>
            <?php else: ?>
                <?php
                /** @var \classes\models\Comments $comment */
                foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment__name">
                            <?= Html::encode($comment->name) ?>
                            (<a class="link" href="/main/deletecomment/?id=<?= Html::encode($comment->id); ?>">удалить</a>)
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
<?php endforeach; ?>

<?php if(isset($_GET['delete']) && $_GET['delete'] == 'none'): ?>
    <script>
        alert('Сообщение не может быть удалено, так как содержит комментарии.\nНеобходимо удалить сначала их.');
    </script>
<?php endif; ?>
