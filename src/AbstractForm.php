<?php
/*
 * @copyright 2019-2022 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 05.01.22 22:47:11
 */

declare(strict_types = 1);
namespace dicr\site;

use dicr\file\File;
use dicr\helper\Html;
use dicr\validate\ValidateException;
use RuntimeException;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\mail\MessageInterface;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

use function gettype;

/**
 * Базовая абстрактная форма.
 *
 * Используемые шаблоны сообщений.
 * - layout @app/mail/layouts/html
 * - manager @app/mail/manager
 * - user @app/mail/user
 * - table @app/mail/table
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
     */
    protected function getFromEmail(): ?string
    {
        return null;
    }

    /**
     * E-Mail менеджера.
     */
    protected function getManagerEmail(): array|string|null
    {
        return null;
    }

    /**
     * Тема сообщения менеджеру.
     */
    protected function getManagerSubject(): ?string
    {
        return null;
    }

    /**
     * Данные сообщения менеджеру.
     *
     * @return string[]|null
     */
    protected function getManagerData(): ?array
    {
        $data = [];
        foreach ($this->attributes as $attribute => $value) {
            $data[Html::esc($this->getAttributeLabel($attribute))] = Html::esc($value);
        }

        return $data ?: null;
    }

    /**
     * Возвращает текст сообщения менеджеру
     */
    protected function getManagerText(): ?string
    {
        $data = $this->getManagerData();

        return empty($data) ? null : Yii::$app->view->render('@app/mail/table', [
            'data' => $data
        ]);
    }

    /**
     * Файлы в сообщение менеджеру.
     *
     * @return File[]|UploadedFile[]|null
     * @noinspection PhpDocSignatureInspection
     */
    protected function getManagerFiles(): ?array
    {
        return null;
    }

    /**
     * Сообщение менеджеру.
     * Для отправки необходимо наличие subject и (text или files).
     * По-умолчанию адрес назначения можно настроить в Mailer::messageConfig
     */
    protected function getManagerMessage(): ?MessageInterface
    {
        $subject = $this->getManagerSubject();
        if (empty($subject)) {
            return null;
        }

        $text = $this->getManagerText();
        $files = $this->getManagerFiles();
        if (empty($text) && empty($files)) {
            return null;
        }

        $message = Yii::$app->mailer
            ->compose('manager', ['content' => $text ?? ''])
            ->setSubject($subject);

        $from = $this->getFromEmail();
        if (! empty($from)) {
            $message->setFrom($from);
        }

        $to = $this->getManagerEmail();
        if (! empty($to)) {
            $message->setTo($to);
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
     */
    protected function getUserEmail(): array|string|null
    {
        return null;
    }

    /**
     * Заголовок сообщения пользователю.
     */
    protected function getUserSubject(): ?string
    {
        return null;
    }

    /**
     * Данные для сообщения пользователю.
     */
    protected function getUserData(): ?array
    {
        return null;
    }

    /**
     * Текст сообщения пользователю.
     */
    protected function getUserText(): ?string
    {
        $data = $this->getUserData();

        return empty($data) ? null : Yii::$app->view->render('@app/mail/table', [
            'data' => $data
        ]);
    }

    /**
     * Файлы для сообщения пользователю.
     *
     * @return File[]|UploadedFile[]|null
     * @noinspection PhpDocSignatureInspection
     */
    protected function getUserFiles(): ?array
    {
        return null;
    }

    /**
     * Сообщение пользователю.
     * Для отправки необходимо to, subject и (text или files)
     */
    protected function getUserMessage(): ?MessageInterface
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

        $message = Yii::$app->mailer
            ->compose('user', ['content' => $text ?? ''])
            ->setTo($to)
            ->setSubject($subject);

        $from = $this->getFromEmail();
        if (! empty($from)) {
            $message->setFrom($from);
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
     * @throws Exception
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function process(): mixed
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
