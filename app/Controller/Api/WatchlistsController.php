<?php
App::uses('BasePagingController', 'Controller/Api');
App::uses('Group', 'Model');
App::uses('KeyResult', 'Model');
App::import('Service', 'WatchlistService');
App::import('Service', 'KrProgressService');
App::import('Service', 'TermService');
App::import('Controller/Traits/Notification', 'TranslationNotificationTrait');
App::import('Service', 'ImageStorageService');
App::import('Policy', 'WatchlistPolicy');

use Goalous\Exception as GlException;

class WatchlistsController extends BasePagingController
{
    use TranslationNotificationTrait;

    public $components = [
        'NotifyBiz',
    ];

    public function get_list()
    {
        // @var TermService ;
        $TermService = ClassRegistry::init("TermService");
        $term = $TermService->getCurrentTerm($this->getTeamId());
        $data = $this->loadTermWatchlists($term['id']);

        return ApiResponse::ok()->withData($data)->getResponse();
    }

    public function get_detail(string $id)
    {
        try {
            if ($id !== KrProgressService::MY_KR_ID) {
                $watchlist = $this->findWatchlist($id);
                $this->authorize('read', $watchlist);
            }
        } catch (Exception $e) {
            return $this->generateResponseIfException($e);
        }

        $krProgressService = new KrProgressService(
            $this->request, 
            $this->getUserId(), 
            $this->getTeamId(), 
            $id
        );
        $krs = $krProgressService->findKrs();
        $response = $krProgressService->processKeyResults($krs);
        $response = $krProgressService->appendProgressGraph($response);

        $response = [
            'id' => $id,
            'kr_count' => count($krs),
            'kr_with_progress' => $response['data']
        ];

        return ApiResponse::ok()->withData($response)->getResponse();
    }

    private function findWatchlist(int $watchlistId): array
    {
        /** @var Watchlist $Watchlist */
        $Watchlist = ClassRegistry::init("Watchlist");
        $watchlist = $Watchlist->getById($watchlistId);

        if (empty($watchlist)) {
            throw new GlException\GoalousNotFoundException(__("This watchlist doesn't exist."));
        }

        return $watchlist;
    }

    public function authorize(string $method, array $watchlist): void
    {
        $policy = new WatchlistPolicy($this->getUserId(), $this->getTeamId());

        switch ($method) {
            case 'read':
                if (!$policy->read($watchlist)) {
                    throw new GlException\Auth\AuthFailedException(__("You don't have permission to access this watchlist"));
                }
                break;
        }
    }

    public function loadTermWatchlists(int $termId): array
    {
        // @var WatchlistService ;
        $WatchlistService = ClassRegistry::init("WatchlistService");
        // @var Watchlist ;
        $Watchlist = ClassRegistry::init("Watchlist");

        $userId = $this->getUserId();
        $teamId = $this->getTeamId();

        // TODO: Remove once we enter phase 2
        // Creates the Important list for users if they haven't watched any KR before
        $WatchlistService->findOrCreateWatchlist($userId, $teamId, $termId);

        $policy = new WatchlistPolicy($userId, $teamId);
        $scope = $policy->scope();
        $results = $Watchlist->findWithKrCount($scope);
        $watchlists = Hash::extract($results, '{n}.Watchlist');

        $krProgressService = new KrProgressService(
            $this->request, 
            $userId, 
            $teamId,
            KrProgressService::MY_KR_ID
        );
        $myKrsCount = count($krProgressService->findKrs(KrProgressService::MY_KR_ID));

        $myKrsList = [
            'id' => KrProgressService::MY_KR_ID,
            'kr_count' => $myKrsCount,
        ];

        return array_merge([$myKrsList], $watchlists);
    }
}
