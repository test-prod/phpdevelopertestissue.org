<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="body-content">

        <div class="page-header">
            <h2><span class="label label-default">YouTube Data API v.3 example</span></h2>
        </div>
        <div class="panel panel-default">
            <!-- Default panel contents -->

            <table class="table">
                <tr>
                    <th>Title</th>
                    <th>Channel Title</th>
                    <th>Description</th>
                    <th>Thumbnail</th>
                    <th>View Count</th>
                    <th>Comment Count</th>
                    <th>Like Count</th>
                    <th>Dislike Count</th>
                    <th>Subscriber Count</th>
                </tr>
        <?php foreach($videos as $v):?>
                <tr>
                    <td><?=$v['title']?></td>
                    <td><?=$v['channelTitle']?></td>
                    <td><?=$v['description']?></td>
                    <td><img class="media-object" src="<?=$v['url']?>" alt="<?=$v['title']?>"></td>
                    <td><?=$v['viewCount']?></td>
                    <td><?=$v['commentCount']?></td>
                    <td><?=$v['likeCount']?></td>
                    <td><?=$v['dislikeCount']?></td>
                    <td><?=$v['subscriberCount']?></td>
                </tr>
        <?php endforeach;?>
            </table>
        </div>

    </div>
</div>
