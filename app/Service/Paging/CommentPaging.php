<?php
App::import('Lib/Paging', 'BasePagingService');
App::uses('PagingCursor', 'Lib/Paging');
/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/05/28
 * Time: 13:56
 */

//TODO
class CommentPaging extends BasePagingService
{

    /**
     * @param PagingCursor $pagingCursor
     * @param int          $limit
     *
     * @return array
     */
    protected function readData($pagingCursor, $limit): array
    {
        $Comment = new Comment();

        return $Comment->getPostCommentsByCursor($pagingCursor, $limit);
    }

    protected function countData($conditions): int
    {
        $Comment = new Comment();

        return $Comment->getCount($conditions);
    }

    protected function extendPagingResult(&$resultArray, &$conditions, $options = [])
    {
    }

}