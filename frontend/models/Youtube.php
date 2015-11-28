<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class Youtube extends Model
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVideo($videoArr, $channelId)
    {
        $returnArr['title']             = $videoArr['items'][0]['snippet']['title'];
        $returnArr['channelTitle']      = $videoArr['items'][0]['snippet']['channelTitle'];
        $returnArr['description']       = $videoArr['items'][0]['snippet']['description'];
        $returnArr['url']               = $videoArr['items'][0]['snippet']['thumbnails']['default']['url'];
        $returnArr['viewCount']         = $videoArr['items'][0]['statistics']['viewCount'];
        $returnArr['commentCount']      = $videoArr['items'][0]['statistics']['commentCount'];
        $returnArr['likeCount']         = $videoArr['items'][0]['statistics']['likeCount'];
        $returnArr['dislikeCount']      = $videoArr['items'][0]['statistics']['dislikeCount'];
        $returnArr['subscriberCount']   = $channelId['items'][0]['statistics']['subscriberCount'];
        return $returnArr;
    }

}
