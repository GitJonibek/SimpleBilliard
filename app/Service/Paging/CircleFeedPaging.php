<?php
App::import('Service/Paging', 'FeedPagingTrait');
App::import('Lib/Paging', 'PagingServiceInterface');
App::import('Lib/Paging', 'PagingServiceTrait');
App::uses('Circle', 'Model');
App::uses('CircleMember', 'Model');
App::uses('Post', 'Model');

/**
 * Methods assume that parameters have been validated in Controller layer
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/05/23
 * Time: 11:38
 */
class CircleFeedPaging implements PagingServiceInterface
{
    use PagingServiceTrait;
    use FeedPagingTrait;

    const EXTEND_ALL_FLAG = -1;
    const EXTEND_USER_FLAG = 0;
    const EXTEND_MY_POST_LIKE_FLAG = 1;
    const EXTEND_CIRCLE_FLAG = 2;
    const EXTEND_COMMENT_FLAG = 3;
    const EXTEND_POST_SHARE_CIRCLE_FLAG = 4;
    const EXTEND_POST_SHARE_USER_FLAG = 5;
    const EXTEND_POST_FILE_FLAG = 6;

    /**
     * Get SQL query for IDs of posts visible to the user using default parameters
     *
     * @param array $conditions Any other required conditions
     *
     * @return array
     */
    private function getDefaultSharedPosts($conditions = [])
    {
        $Post = new Post();

        $Post->my_uid = $conditions['current_user_id'];

        /**
         * @var DboSource $db
         */
        $db = $Post->getDataSource();

        $queryCondition['OR'][] = $Post->getConditionGetMyPostList();
        $queryCondition['OR'][] = $this->createDbExpression($db,
            $Post->getSubQueryFilterPostIdShareWithMe($db, $conditions['start'], $conditions['end']));
        $queryCondition['OR'][] = $this->createDbExpression($db,
            $Post->getSubQueryFilterMyCirclePostId($db, $conditions['start'], $conditions['end']));

        return $queryCondition;
    }

    /**
     * Get query condition for posts visible to the user
     *
     * @param array $conditions
     *
     * @return array
     */
    private function getSharedPosts($conditions = [])
    {
        $Post = new Post();

        /**
         * @var DboSource $db
         */
        $db = $Post->getDataSource();

        if (isset($conditions['circle_id'])) {
            $Circle = new Circle();
            $CircleMember = new CircleMember();

            //Check if circle belongs to current team & user has access to the circle
            $CircleMember->my_uid = $conditions['current_user_id'];
            if (!$Circle->isBelongCurrentTeam($conditions['circle_id'], $conditions['team_id'])
                || ($Circle->isSecret($conditions['circle_id']) && !$CircleMember->isBelong($conditions['circle_id']))) {
                throw new RuntimeException(__("The circle dosen't exist or you don't have permission."));
            }

            $queryConditions['OR'][] = $this->createDbExpression($db,
                $Post->getSubQueryFilterMyCirclePostId($db, $conditions['start'],
                    $conditions['end'],
                    $conditions['circle_id'],
                    PostShareCircle::SHARE_TYPE_SHARED));

        } elseif (isset($conditions['user_id'])) {

            $queryConditions['OR'][] = $this->createDbExpression($db,
                $Post->getSubQueryFilterPostIdShareWithMe($db, $conditions['start'], $conditions['end'],
                    ['user_id' => $conditions['user_id']]));

            $queryConditions['OR'][] = $this->createDbExpression($db,
                $Post->getSubQueryFilterAccessibleCirclePostList($db, $conditions['start'], $conditions['end'],
                    ['user_id' => $conditions['user_id']]));

            if ($conditions['OR']['current_user_id'] == $conditions['user_id']) {
                $Post->my_uid = $conditions['user_id'];
                $queryConditions['OR'][] = $Post->getConditionGetMyPostList();
            }

        } //If no parameters were set, use default values
        else {
            $queryConditions = $this->getDefaultSharedPosts($conditions);
        }

        return $queryConditions;
    }

    protected function readData(PagingCursor $pagingCursor, $limit): array
    {
        $options = [
            'conditions' => $this->getSharedPosts($pagingCursor->getConditions()),
            'limit'      => $limit,
            'order'      => $pagingCursor->getOrders()
        ];

        $options['conditions'][] = $pagingCursor->getPointersAsQueryOption();
        $options['conditions']['Post.type'] = Post::TYPE_NORMAL;

        $Post = new Post();
        $result = $Post->find('all', $options);

        $this->setCommentRead($result);

        $this->setCommentUnreadCount($result);

        $this->getPostResource($result);

        return $result;
    }

    protected function countData($conditions): int
    {
        $post = new Post();

        return $post->find('count', $conditions)[0];
    }

    protected function extendPagingResult(&$resultArray, $flags = [])
    {
        foreach ($resultArray as $feed) {
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_USER_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_MY_POST_LIKE_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_CIRCLE_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_COMMENT_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_POST_SHARE_CIRCLE_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_POST_SHARE_USER_FLAG, $flags)) {

            }
            if (in_array(self::EXTEND_ALL_FLAG, $flags) || in_array(self::EXTEND_POST_FILE_FLAG, $flags)) {

            }
        }
    }
}