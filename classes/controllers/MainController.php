<?php

namespace classes\controllers;

use classes\base\App;
use classes\base\Controller;
use classes\models\Comments;
use classes\models\Messages;

/**
 * Класс контроллера, который содержит методы, которые вызывает компонент Request.
 *
 * Class MainController
 * @package classes\controllers
 */
class MainController extends Controller
{
    /**
     * Действие по умолчанию.
     * Действие выбирает все сообщения с помощью модели Messages и передает их в представление index.
     */
    public function actionIndex()
    {
        //Выбираем все сообщения
        //['id' => 'DESC'] - сортировка по id от новых сообщений к старым
        $messagesModels = Messages::findAll(null, ['id' => 'DESC']);

        //Передаем данные (сообщения) в представление
        echo $this->render('index', [
            'messagesModels' => $messagesModels,
        ]);
    }

    /**
     * Метод добавления нового комментария.
     *
     * @throws \Exception Отсутствует сообщение, к которому добавляется комментарий
     */
    public function actionAddcomment()
    {
        //Id сообщения
        $messageId = (int) $_GET['id'];

        //Проверка, существует ли сообщение, для которого добавляется комментарий
        if(empty($modelMessage = Messages::findOne($messageId))) {
            throw new \Exception('Сообщения, к которому добавляется комментарий, не существует');
        }


        //Создаем модель нового комментария
        $model = new Comments();

        //Если форма комментария отправлена
        if (!empty($_POST['Comments'])) {

            //Получаем очищенные от опасных конструкций данные и присваеваем их модели
            $model->setAttributes(App::$app->request->postData('Comments'));
            //Добавляем id сообщения, к которому добавляется комментарий
            $model->messageid = $messageId;

            //Проверяем данные на корректность
            if ($model->validate()) {
                //Добавляем новый комментарий
                if (Comments::insertOne([
                    'name' => $model->name,
                    'text' => $model->text,
                    'date' => $model->date,
                    'messageid' => $model->messageid,
                ])
                ) {
                    //Если комментарий успешно добавлен, обновляем страницу, чтобы форма была очищена
                    $this->refresh();
                }
            }

        }

        //Передаем данные в представление для вывода
        echo $this->render('add-comment', [
            'model' => $model,
            'modelMessage' => $modelMessage,
        ]);
    }

    /**
     * Метод добавления сообщения.
     */
    public function actionAddmessage()
    {
        $model = new Messages();

        if (!empty($_POST['Messages'])) {
            $model->setAttributes(App::$app->request->postData('Messages'));
            if ($model->validate()) {
                if (Messages::insertOne(['name' => $model->name, 'text' => $model->text, 'date' => $model->date])) {
                    //Если сообщение добавлено, перенаправляем пользователя на главную страницу
                    $this->redirect('/', 302);
                }
            }
        }

        echo $this->render('message', [
            'model' => $model,
        ]);
    }

    /**
     * Метод редактирования сообщения.
     *
     * @throws \Exception Сообщение не найдено в базе данных
     */
    public function actionEditmessage()
    {
        //id редактируемого сообщения
        $id = (int)$_GET['id'];

        /** @var Messages $model */
        $model = Messages::findOne($id);
        //Если редактируемое сообщение не найдено
        if (empty($model)) {
            throw new \Exception('Данное сообщение не найдено');
        }

        if (!empty($_POST['Messages'])) {
            $model->setAttributes(App::$app->request->postData('Messages'));
            if ($model->validate()) {
                if (Messages::updateOne($id, [
                    'name' => $model->name,
                    'text' => $model->text,
                    'date' => $model->date,
                ])
                ) {
                    $this->redirect('/', 302);
                }
            }
        }

        echo $this->render('message', ['model' => $model]);
    }

    /**
     * Метод удаления сообщения.
     *
     * @throws \Exception
     */
    public function actionDeletemessage()
    {
        //id удаляемого сообщения
        $id = (int)$_GET['id'];

        //Поиск с использованием условия ['messageid' => $id]
        if(!empty(Comments::findOneByCondition(['messageid' => $id]))){
            //Если сообщение содержит комментарии, не удаляем его,
            // иначе комментарии останутся в базе данных навсегда
            //Вместо этого перенаправляем пользователя на главную страницу с GET параметром
            //delete=none#message.$id, параметр выводит alert(), а якорь прокручивает страницу до нужного сообщения
            $this->redirect('/main/index?delete=none#message'.$id, 302);

            throw new \Exception('Сообщение содержит комментарии, поэтому не может быть удалено');
        }

        //Удаляем 1 запись
        if(Messages::deleteOne($id)){
            $this->redirect('/', 302);
        } else {
            throw new \Exception('Сообщение не найдено в базе данных');
        }
    }

    /**
     * Метод удаления комментария.
     *
     * @throws \Exception Комментарий не найден в базе данных
     */
    public function actionDeletecomment()
    {
        //id комментария
        $id = (int)$_GET['id'];

        //Удаляем 1 запись
        if(Comments::deleteOne($id)){
            $this->redirect('/', 302);
        } else {
            throw new \Exception('Комментарий не найден в базе данных');
        }
    }

    function actionTest()
    {
        phpinfo();
    }
}