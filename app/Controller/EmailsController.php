<?php
App::uses('AppController', 'Controller');

/**
 * Emails Controller
 *
 * @property Email              $Email
 * @property PaginatorComponent $Paginator
 * @property SessionComponent   $Session
 */
class EmailsController extends AppController
{
    /**
     * delete method
     *
     * @throws NotFoundException
     *
     * @param string $id
     *
     * @return void
     */
    public function delete($id = null)
    {
        $this->Email->id = $id;
        if (!$this->Email->exists()) {
            throw new NotFoundException(__("このメールアドレスは存在しません。"));
        }
        if (!$this->Email->isOwner($this->Auth->user('id'))) {
            throw new NotFoundException(__("このメールアドレスはあなたのものではありません。"));
        }

        $this->request->allowMethod('post', 'delete');
        $this->Email->delete();
        $this->Pnotify->outSuccess(__("To change email address is canceled."));
        /** @noinspection PhpInconsistentReturnPointsInspection */
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return $this->redirect($this->referer());
    }
}
