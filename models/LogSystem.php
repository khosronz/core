<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;
use \yii\behaviors\BlameableBehavior;


/**
 * This is the model class for table "{{%log_system}}".
 *
 * @property integer $id
 * @property string $userid
 * @property string $time
 * @property string $severity
 * @property string $action
 * @property string $message
 *
 * @property Severity $severity0
 * @property User $user
 *
 * @package app\models
 */
class LogSystem extends ActiveRecord
{

    /**
     * @var string
     */
    public $date_from;

    /**
     * @var string
     */
    public $date_to;

    /**
     * Default page size
     * @var int
     */
    public $page_size = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log_system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['time'], 'safe'],
            [['message'], 'required'],
            [['message'], 'string'],
            [['userid'], 'string', 'max' => 128],
            [['severity'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 45],
            [['severity'], 'exist', 'skipOnError' => true, 'targetClass' => Severity::class, 'targetAttribute' => ['severity' => 'name']],
            [['userid'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userid' => 'userid']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'userid'    => Yii::t('app', 'User'),
            'time'      => Yii::t('app', 'Time'),
            'severity'  => Yii::t('log', 'Severity'),
            'action'    => Yii::t('log', 'Action'),
            'message'   => Yii::t('app', 'Message'),
            'date_from' => Yii::t('log', 'Date/time from'),
            'date_to'   => Yii::t('log', 'Date/time to'),
            'page_size' => Yii::t('app', 'Page size'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'userid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeverity0()
    {
        return $this->hasOne(Severity::class, ['name' => 'severity']);
    }

    /**
     * Behaviors
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'userid',
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * Write custom log to DB
     *
     * @param array  $params
     * @param string $level
     */
    public function writeLog($params, $level)
    {
        $this->userid      = Yii::$app->user->id;
        $this->severity    = $level;
        $this->message     = $params[0];
        $this->action      = $params[1];
        $this->save(false);
    }

}
