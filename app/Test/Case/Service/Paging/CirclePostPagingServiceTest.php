<?php
App::uses('GoalousTestCase', 'Test');
App::import('Service/Paging', 'CirclePostPagingService');
App::uses('PagingRequest', 'Lib/Paging');

/**
 * Created by PhpStorm.
 * User: StephenRaharja
 * Date: 2018/06/20
 * Time: 11:24
 */
class CirclePostPagingServiceTest extends GoalousTestCase
{
    public $fixtures = [
        'app.team',
        'app.team_member',
        'app.user',
        'app.circle',
        'app.circle_member',
        'app.circle_pin',
        'app.post',
        'app.post_share_circle',
        'app.post_share_user',
        'app.comment',
        'app.local_name',
        'app.experiment',
        'app.post_like',
        'app.saved_post',
    ];

    public function test_getCirclePost_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();

        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 3);
        $cursor->addResource('current_team_id', 1);

        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1);

        $this->assertCount(1, $result['data']);
        $this->assertNotEmpty($result['paging']);
        $this->assertNotEmpty($result['count']);
    }

    public function test_getCirclePostWithCursor_success()
    {
        /** @var CirclePostPagingService $CircleFeedPaging */
        $CircleFeedPaging = new CirclePostPagingService();

        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 3);
        $cursor->addResource('current_team_id', 1);

        $result = $CircleFeedPaging->getDataWithPaging($cursor, 1);
        $this->assertNotEmpty($result['paging']['next']);
        $this->assertNotEmpty($result['count']);

        $pagingRequest = PagingRequest::decodeCursorToObject($result['paging']);
        $pagingRequest->addResource('res_id', 1);
        $pagingRequest->addResource('current_team_id', 1);

        $secondResult = $CircleFeedPaging->getDataWithPaging($pagingRequest, 2);

        $this->assertCount(2, $secondResult['data']);
        $this->assertNotEmpty($secondResult['paging']);
        $this->assertNotEmpty($secondResult['count']);
    }

    public function test_getCirclePostWithUserExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 1);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1, CirclePostPagingService::EXTEND_USER);

        $this->assertCount(1, $result['data']);

        $postData = $result['data'][0];

        $this->assertNotEmpty($postData['user']);
    }

    public function test_getCirclePostWithCircleExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 3);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 10, CirclePostPagingService::EXTEND_CIRCLE);

        $this->assertCount(2, $result['data']);

        //Loop since not all post has circle_id
        foreach ($result['data'] as $post) {
            if (!empty($post['circle_id'])) {
                $this->assertNotEmpty($post['circle']);
            }
        }
    }

    public function test_getCirclePostWithCommentsExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 1);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1, CirclePostPagingService::EXTEND_COMMENTS);

        $this->assertCount(1, $result['data']);

        $postData = $result['data'][0];

        $this->assertNotEmpty($postData['comments']);
    }

    public function test_getCirclePostWithPostLikeExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 1);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1, CirclePostPagingService::EXTEND_LIKE);

        $this->assertCount(1, $result['data']);

        $postData = $result['data'][0];
        $this->assertInternalType('bool', $postData['is_liked']);
    }

    public function test_getCirclePostWithPostSavedExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 1);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1, CirclePostPagingService::EXTEND_SAVED);

        $this->assertCount(1, $result['data']);

        $postData = $result['data'][0];
        $this->assertInternalType('bool', $postData['is_saved']);
    }

    public function test_getCirclePostWithPostFileExtension_success()
    {
        /** @var CirclePostPagingService $CirclePostPagingService */
        $CirclePostPagingService = new CirclePostPagingService();
        $cursor = new PagingRequest();
        $cursor->addResource('res_id', 1);
        $cursor->addResource('current_user_id', 1);
        $cursor->addResource('current_team_id', 1);
        $result = $CirclePostPagingService->getDataWithPaging($cursor, 1, CirclePostPagingService::EXTEND_POST_FILE);

        $this->assertCount(1, $result['data']);

        $postData = $result['data'][0];
        $this->assertArrayHasKey('attached_files', $postData);
    }
}
