<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 27.01.21 19:53:40
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\file\File;
use dicr\helper\Html;
use dicr\validate\ValidateException;
use RuntimeException;
use Yii;
use yii\base\Model;
use yii\mail\MessageInterface;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

use function gettype;

/**
 * Базовая абстрактная форма.
 *
 * @property-read array|string|null $fromEmail адрес отправителя
 *
 * @property-read array|string|null $managerEmail
 * @property-read ?string $managerSubject
 * @property-read ?string[] $managerData
 * @property-read ?string $managerText
 * @property-read File[]|null $managerFiles
 * @property-read ?MessageInterface $managerMessage
 *
 * @property-read array|string|null $userEmail
 * @property-read ?string $userSubject
 * @property-read ?string[] $userData
 * @property-read ?string $userText
 * @property-read File[]|null $userFiles
 * @property-read ?MessageInterface $userMessage
 */
abstract class AbstractForm extends Model
{
    /**
     * Поле от кого.
     *
     * @return array|string|null
     */
    protected function getFromEmail()
    {
        return Yii::$app->params['email']['from'] ?? null;
    }

    /**
     * E-Mail менеджера.
     *
     * @return array|string|null
     */
    protected function getManagerEmail()
    {
        return Yii::$app->params['email']['manager'] ?? null;
    }

    /**
     * Тема сообщения менеджеру.
     *
     * @return ?string
     */
    protected function getManagerSubject() : ?string
    {
        return null;
    }

    /**
     * Данные сообщения менеджеру.
     *
     * @return ?string[]
     */
    protected function getManagerData() : ?array
    {
        $data = [];

        foreach ($this->attributes as $attribute => $value) {
            $data[Html::esc($this->getAttributeLabel($attribute))] = Html::esc($value);
        }

        return $data;
    }

    /**
     * Возвращает текст сообщения менеджеру
     *
     * @return ?string
     */
    protected function getManagerText() : ?string
    {
        $data = $this->getManagerData();
        if (empty($data)) {
            return null;
        }

        $text = Yii::$app->view->render('@app/mail/table', [
            'data' => $data
        ]);

        return Yii::$app->view->render('@app/mail/manager', [
            'content' => $text
        ]);
    }

    /**
     * Файлы в сообщение менеджеру.
     *
     * @return File[]|null
     */
    protected function getManagerFiles() : ?array
    {
        return null;
    }

    /**
     * Сообщение менеджеру.
     *
     * @return ?MessageInterface
     */
    protected function getManagerMessage() : ?MessageInterface
    {
        $to = $this->getManagerEmail();
        if (empty($to)) {
            return null;
        }

        $subject = $this->getManagerSubject();
        if (empty($subject)) {
            return null;
        }

        $text = $this->getManagerText();
        $files = $this->getManagerFiles();
        if (empty($text) && empty($files)) {
            return null;
        }

        $message = Yii::$app->mailer->compose()
            ->setTo($to)
            ->setSubject($subject)
            ->setCharset(Yii::$app->charset);

        $from = $this->getFromEmail();
        if (! empty($from)) {
            $message->setFrom($from);
        }

        if (! empty($text)) {
            $message->setHtmlBody($text);
        }

        if (! empty($files)) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $message->attach($file->tempName, [
                        'fileName' => $file->name
                    ]);
                } elseif ($file instanceof File) {
                    $message->attach($file->absolutePath, [
                        'fileName' => $file->name
                    ]);
                } else {
                    throw new RuntimeException('Неизвестный тип файла: ' . gettype($file));
                }
            }
        }

        return $message;
    }

    /**
     * E-Mail пользователя.
     *
     * @return array|string|null
     */
    protected function getUserEmail()
    {
        return null;
    }

    /**
     * Заголовок сообщения пользователю.
     *
     * @return ?string
     */
    protected function getUserSubject() : ?string
    {
        return null;
    }

    /**
     * Данные для сообщения пользователю.
     *
     * @return ?array
     */
    protected function getUserData() : ?array
    {
        return null;
    }

    /**
     * Текст сообщения пользователю.
     *
     * @return ?string
     */
    protected function getUserText() : ?string
    {
        $data = $this->getUserData();
        if (empty($data)) {
            return null;
        }

        $text = Yii::$app->view->render('@app/mail/table', [
            'data' => $data
        ]);

        return Yii::$app->view->render('@app/mail/user', [
            'content' => $text
        ]);
    }

    /**
     * Файлы для сообщения пользователю.
     *
     * @return File[]|null
     */
    protected function getUserFiles() : ?array
    {
        return null;
    }

    /**
     * Сообщение пользователю.
     *
     * @return ?MessageInterface
     */
    protected function getUserMessage() : ?MessageInterface
    {
        $to = $this->getUserEmail();
        if (empty($to)) {
            return null;
        }

        $subject = $this->getUserSubject();
        if (empty($subject)) {
            return null;
        }

        $text = $this->getUserText();
        $files = $this->getUserFiles();
        if (empty($text) && empty($files)) {
            return null;
        }

        $message = Yii::$app->mailer->compose()
            ->setTo($to)
            ->setSubject($subject)
            ->setCharset(Yii::$app->charset);

        $from = $this->getFromEmail();
        if (! empty($from)) {
            $message->setFrom($from);
        }

        if (! empty($text)) {
            $message->setHtmlBody($text);
        }

        if (! empty($files)) {
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $message->attach($file->tempName, [
                        'fileName' => $file->name
                    ]);
                } elseif ($file instanceof File) {
                    $message->attach($file->absolutePath, [
                        'fileName' => $file->name
                    ]);
                }
            }
        }

        return $message;
    }

    /**
     * Обработка формы.
     *
     * @return mixed
     * @throws ValidateException
     * @throws ServerErrorHttpException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function process()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $managerMessage = $this->getManagerMessage();
        if ($managerMessage !== null && ! $managerMessage->send()) {
            throw new ServerErrorHttpException('Ошибка отправки сообщения менеджеру');
        }

        $userMessage = $this->getUserMessage();
        if ($userMessage !== null && ! $userMessage->send()) {
            throw new ServerErrorHttpException('Ошибка отправки сообщения пользователю');
        }

        return true;
    }
}
