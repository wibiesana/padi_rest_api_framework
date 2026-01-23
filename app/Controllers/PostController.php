<?php

namespace App\Controllers;

use Core\Query;
use App\Controllers\Base\PostController as BaseController;

class PostController extends BaseController
{
    /**
     * Override methods here to add custom logic.
     */

    public function queryAll(): void
    {
        $title = $this->request->query('title');
        $query = Query::find()->from('posts')
            ->where(['status' => 'published']);

        if ($title) {
            $query->andWhere(['like', 'title', $title]);
        }

        $posts = $query->all();
        $this->success(['data' => $posts]);
    }
}
